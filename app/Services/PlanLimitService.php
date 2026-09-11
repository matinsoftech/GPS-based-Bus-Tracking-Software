<?php

namespace App\Services;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\Route;
use App\Models\School;
use App\Models\Student;

class PlanLimitService
{
    private const ENTITY_MODELS = [
        'buses' => [Bus::class, 'max_buses'],
        'students' => [Student::class, 'max_students'],
        'parents' => [ParentProfile::class, 'max_parents'],
        'drivers' => [Driver::class, 'max_drivers'],
        'routes' => [Route::class, 'max_routes'],
    ];

    /**
     * Return an error message when the school's plan cannot accept another
     * record of $entity, or null when the creation is allowed (unlimited).
     */
    public function assertCreatable(string $entity, int $schoolId): ?string
    {
        $plural = $entity;

        $limit = $this->planLimit($entity, $schoolId);

        if ($limit === null || $limit < 0) {
            return null;
        }

        [$modelClass, $column] = self::ENTITY_MODELS[$entity];

        $current = $modelClass::where('school_id', $schoolId)->count();

        if ($current >= $limit) {
            return "Plan limit reached: your current plan allows up to {$limit} {$plural}. Please upgrade your plan to add more.";
        }

        return null;
    }

    /**
     * Resolve the plan limit for an entity, or null when unlimited.
     */
    private function planLimit(string $entity, int $schoolId): ?int
    {
        if (! isset(self::ENTITY_MODELS[$entity])) {
            return null;
        }

        $school = School::with('activeSubscription.plan')->find($schoolId);

        if (! $school) {
            return null;
        }

        $subscription = $school->activeSubscription;

        if (! $subscription || ! $subscription->plan) {
            return null;
        }

        [, $column] = self::ENTITY_MODELS[$entity];

        return $subscription->plan->{$column};
    }
}