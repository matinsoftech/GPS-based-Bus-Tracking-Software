<?php

namespace App\View\Composers;

use App\Models\School;
use App\Models\SchoolAdmin;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Injects the current user's latest notifications and unread count into the
 * header partial so the bell dropdown and badge always render real data.
 *
 * Also resolves the current user's school (logo + name) so the header and
 * sidebar can display their own school branding for Principal, Driver, and
 * Parent roles. Super Admin manages all schools and gets no school context.
 */
class HeaderComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $user = Auth::user();

        if (! $user) {
            $view->with('headerNotifications', collect())
                ->with('headerUnreadCount', 0)
                ->with('headerSchool', null);

            return;
        }

        $view->with('headerNotifications', $user->notifications()->latest()->take(8)->get())
            ->with('headerUnreadCount', $user->unreadNotifications()->count())
            ->with('headerSchool', $this->resolveSchool($user));
    }

    /**
     * Resolve the school context for the current user, or null when there is
     * no single school to attribute (e.g. a Super Admin managing all schools).
     */
    private function resolveSchool($user): ?School
    {
        if ($user->school_id) {
            return School::find($user->school_id);
        }

        $roleNames = array_map('strtolower', $user->getRoleNames()->all());

        if (in_array('principal', $roleNames, true) || in_array('school admin', $roleNames, true)) {
            $schoolId = SchoolAdmin::where('user_id', $user->id)->value('school_id');

            if (! $schoolId) {
                $schoolId = School::where('principal_name', $user->name)
                    ->orWhere('email', $user->email)
                    ->value('id');
            }

            return $schoolId ? School::find($schoolId) : null;
        }

        if (in_array('driver', $roleNames, true) && $user->driver) {
            return $user->driver->school;
        }

        if (in_array('parent', $roleNames, true) && $user->parent) {
            return $user->parent->school;
        }

        return null;
    }
}
