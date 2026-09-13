<?php

namespace App\Services;

use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\User;
use Illuminate\Support\Collection;

class SchoolContextService
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}

    /**
     * Resolve the single school context for a user, or null when there is no
     * attributable school (e.g. a Super Admin managing all schools).
     */
    public function resolveSchool(?User $user): ?School
    {
        if (! $user) {
            return null;
        }

        if ($user->school_id) {
            return School::find($user->school_id);
        }

        // Mobile users may carry a driver/parent profile without an assigned
        // role, so resolve from the profile relation whenever it exists.
        if ($user->driver) {
            return $user->driver->school;
        }

        if ($user->parent) {
            return $user->parent->school;
        }

        $roleNames = $this->roleNames($user);

        if ($roleNames->contains('principal') || $roleNames->contains('school admin')) {
            $schoolId = SchoolAdmin::where('user_id', $user->id)->value('school_id');

            if (! $schoolId) {
                $schoolId = School::where('principal_name', $user->name)
                    ->orWhere('email', $user->email)
                    ->value('id');
            }

            return $schoolId ? School::find($schoolId) : null;
        }

        return null;
    }

    /**
     * Whether a school currently has a usable subscription that grants access.
     */
    public function schoolAccessible(?School $school): bool
    {
        if (! $school) {
            return false;
        }

        $subscription = $school->activeSubscription()->with('plan')->first();

        return $subscription !== null && $this->subscriptions->isActive($subscription);
    }

    /**
     * Whether the user may use the product: Super Admin is always allowed,
     * users with no attributable school are passed through to their own
     * controllers, and everyone else only when their school's subscription is
     * usable.
     */
    public function userHasAccess(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        $school = $this->resolveSchool($user);

        if ($school === null) {
            return true;
        }

        return $this->schoolAccessible($school);
    }

    private function roleNames(User $user): Collection
    {
        return collect($user->getRoleNames()->all())
            ->map(fn (string $role) => strtolower($role));
    }
}
