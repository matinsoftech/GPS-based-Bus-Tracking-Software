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
    ];

    private const INACTIVE_MESSAGE = 'Your school\'s subscription is not active.';

    public function __construct(private readonly SchoolContextService $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($this->context->userHasAccess($user)) {
            return $next($request);
        }

        if ($request->route()?->getName() !== null
            && in_array($request->route()->getName(), self::BYPASS_ROUTES, true)) {
            return $next($request);
        }

        if ($request->is('api/*')) {
            abort(403, self::INACTIVE_MESSAGE);
        }

        if ($user && $user->hasAnyRole(['School Admin', 'Principal'])) {
            return redirect()->route('principal.subscription')
                ->with('warning', self::INACTIVE_MESSAGE);
        }

        return redirect()->route('subscription.inactive');
    }
}
