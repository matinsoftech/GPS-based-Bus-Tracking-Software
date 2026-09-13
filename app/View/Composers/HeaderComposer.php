<?php

namespace App\View\Composers;

use App\Services\SchoolContextService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Injects the current user's latest notifications and unread count into the
 * header partial so the bell dropdown and badge always render real data.
 *
 * Also resolves the current user's school (logo + name) so the header and
 * sidebar can display their own school branding. Super Admin manages all
 * schools and gets no school context. The `subscriptionOk` flag lets the
 * header and sidebar hide product modules when the school's subscription
 * is not usable.
 */
class HeaderComposer
{
    public function __construct(private readonly SchoolContextService $context) {}

    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $user = Auth::user();

        if (! $user) {
            $view->with('headerNotifications', collect())
                ->with('headerUnreadCount', 0)
                ->with('headerSchool', null)
                ->with('subscriptionOk', true);

            return;
        }

        $view->with('headerNotifications', $user->notifications()->latest()->take(8)->get())
            ->with('headerUnreadCount', $user->unreadNotifications()->count())
            ->with('headerSchool', $this->context->resolveSchool($user))
            ->with('subscriptionOk', $this->context->userHasAccess($user));
    }
}
