<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrincipalSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $schoolId = $this->resolveSchoolId($user);

        $school = $schoolId ? School::find($schoolId) : null;

        $subscription = $school
            ? $school->subscriptions()->with('plan')->latest()->first()
            : null;

        $plan = $subscription?->plan;

        return view('principal.subscriptions.index', compact('school', 'subscription', 'plan'));
    }

    private function resolveSchoolId($user): ?int
    {
        $schoolId = $user->school_id;

        if (! $schoolId) {
            $schoolId = SchoolAdmin::where('user_id', $user->id)->value('school_id');
        }

        if (! $schoolId) {
            $schoolId = School::where('principal_name', $user->name)
                ->orWhere('email', $user->email)
                ->value('id');
        }

        return $schoolId;
    }
}