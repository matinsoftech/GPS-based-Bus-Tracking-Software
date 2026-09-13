<?php

namespace App\Http\Controllers;

use App\Services\SchoolContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrincipalSubscriptionController extends Controller
{
    public function __construct(private readonly SchoolContextService $context) {}

    public function index(Request $request)
    {
        $school = $this->context->resolveSchool(Auth::user());

        $subscription = $school
            ? $school->subscriptions()->with('plan')->latest()->first()
            : null;

        $plan = $subscription?->plan;

        return view('principal.subscriptions.index', compact('school', 'subscription', 'plan'));
    }
}
