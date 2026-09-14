<?php

namespace App\Http\Middleware;

use App\Services\SchoolContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks product modules when the acting school's subscription is not
 * usable. Super Admin is always allowed, and pages that must stay reachable
 * (subscription page, profile, notifications) are whitelisted by name.
 */
class EnsureActiveSubscription
{
    /**
     * Route names that remain reachable while a school's subscription is
     * not active, so users can manage their plan, profile, and alerts.
     */
    private const BYPASS_ROUTES = [
        'principal.subscription',
        'subscription.inactive',
        'profile.edit',
        'profile.update',
        'profile.destroy',
        'notifications.index',
        'notifications.unread-count',
        'notifications.read',
        'notifications.read-all',
        'invoices.index',
        'invoices.show',
        'invoices.print',
    ];

    /**
     * API endpoint patterns that stay reachable while the school's
     * subscription is not active, so users can manage their account and
     * alerts. Login/forgot-password/reset-password are public and already
     * outside the gated group.
     */
    private const API_BYPASS_PATTERNS = [
        'api/v1/auth/*',
        'api/v1/notifications*',
    ];

    private const INACTIVE_MESSAGE = 'Your school\'s subscription is not active.';

    public function __construct(private readonly SchoolContextService $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($request->is(self::API_BYPASS_PATTERNS)) {
            return $next($request);
        }

        if ($this->context->userHasAccess($user)) {
            return $next($request);
        }

        if ($request->route()?->getName() !== null
            && in_array($request->route()->getName(), self::BYPASS_ROUTES, true)) {
            return $next($request);
        }

        if ($request->is('api/*')) {
            return response()->json([
                'message' => self::INACTIVE_MESSAGE,
                'subscription_required' => true,
                'status' => 'inactive',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($user && $user->hasAnyRole(['School Admin', 'Principal'])) {
            return redirect()->route('principal.subscription')
                ->with('warning', self::INACTIVE_MESSAGE);
        }

        return redirect()->route('subscription.inactive');
    }
}
