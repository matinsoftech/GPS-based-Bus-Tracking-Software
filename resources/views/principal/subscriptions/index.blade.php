<x-app-layout page="subscription">
    @php
        $featureLabels = [
            'live_tracking' => 'Live Tracking',
            'parent_app' => 'Parent App',
            'notifications' => 'Notifications',
            'attendance' => 'Attendance',
            'reports' => 'Reports',
            'analytics' => 'Analytics',
        ];

        $limitLabels = [
            'max_buses' => 'Buses',
            'max_students' => 'Students',
            'max_parents' => 'Parents',
            'max_drivers' => 'Drivers',
            'max_routes' => 'Routes',
            'max_devices' => 'GPS Devices',
        ];

        $statusColors = [
            'trialing' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-500',
            'active' => 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500',
            'past_due' => 'bg-yellow-50 text-yellow-600 dark:bg-yellow-500/15 dark:text-yellow-500',
            'cancelled' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-500',
            'expired' => 'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
        ];

        $daysLeft = $subscription && $subscription->ends_at
            ? now()->startOfDay()->diffInDays($subscription->ends_at->copy()->startOfDay(), false)
            : null;

        $daysLeftLabel = null;

        if ($daysLeft !== null) {
            $daysLeftLabel = $daysLeft === 0
                ? 'Expires today'
                : $daysLeft.' '.(abs($daysLeft) === 1 ? 'day' : 'days').($daysLeft > 0 ? ' left' : ' ago');
        }
    @endphp

    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Subscription &amp; Plan</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                View your school's current plan and subscription details.
            </p>
        </div>

        @if ($school && $subscription && $plan)
            <div class="max-w-4xl space-y-6">
                {{-- Subscription card --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Current Subscription</h2>
                        <span class="rounded-full px-3 py-1 text-xs font-medium {{ $statusColors[$subscription->status] ?? $statusColors['expired'] }}">
                            {{ ucfirst(str_replace('_', ' ', $subscription->status)) }}
                        </span>
                    </div>

                    @if ($daysLeft !== null)
                        @if ($daysLeft >= 0)
                            <div class="mt-4 inline-flex items-center gap-2 rounded-xl bg-green-50 px-4 py-2.5 text-sm font-medium text-green-700 dark:bg-green-500/10 dark:text-green-400">
                                <x-heroicon-o-clock class="h-5 w-5 shrink-0" />
                                {{ $daysLeftLabel }}
                                <span class="text-xs font-normal text-green-600/70 dark:text-green-400/70">
                                    &middot; ends {{ $subscription->ends_at->format('M d, Y') }}
                                </span>
                            </div>
                        @else
                            <div class="mt-4 inline-flex items-center gap-2 rounded-xl bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">
                                <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0" />
                                {{ $daysLeftLabel }}
                                <span class="text-xs font-normal text-red-600/70 dark:text-red-400/70">
                                    &middot; ended {{ $subscription->ends_at->format('M d, Y') }}
                                </span>
                            </div>
                        @endif
                    @endif

                    <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-4 md:grid-cols-4">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Plan</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Billing</dt>
                            <dd class="mt-1 text-sm capitalize text-gray-900 dark:text-white">
                                {{ $subscription->billing_cycle }} &middot; ₹{{ number_format((float) $subscription->amount, 2) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Started</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $subscription->starts_at ? $subscription->starts_at->format('M d, Y') : '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Expires</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $subscription->ends_at ? $subscription->ends_at->format('M d, Y') : '—' }}
                            </dd>
                        </div>
                    </dl>

                    @if ($subscription->trial_ends_at)
                        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                            Trial ends {{ $subscription->trial_ends_at->format('M d, Y') }}.
                        </p>
                    @endif
                </div>

                {{-- Plan card --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Plan Details</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $plan->name }} plan</p>

                    <div class="mt-5">
                        <h3 class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Features</h3>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($featureLabels as $key => $label)
                                @if (isset($plan->features[$key]) && $plan->features[$key])
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-500/10 dark:text-green-400">
                                        <x-heroicon-o-check-circle class="h-4 w-4 shrink-0" />
                                        {{ $label }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500 dark:bg-gray-500/10 dark:text-gray-400">
                                        <x-heroicon-o-x-circle class="h-4 w-4 shrink-0" />
                                        {{ $label }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Limits</h3>
                        <dl class="mt-3 grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                            @foreach ($limitLabels as $key => $label)
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ $label }}</dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $plan->{$key} !== null ? $plan->{$key} : 'Unlimited' }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/[0.03]">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Monthly Price</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">₹{{ number_format((float) $plan->monthly_price, 2) }}</dd>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/[0.03]">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Yearly Price</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">₹{{ number_format((float) $plan->yearly_price, 2) }}</dd>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="max-w-4xl rounded-2xl border border-gray-200 bg-white p-6 text-center dark:border-gray-800 dark:bg-white/[0.03]">
                <x-heroicon-o-receipt-percent class="mx-auto h-12 w-12 text-gray-400" />
                <h2 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">No Subscription Yet</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Your school doesn't have a subscription yet. Contact your administrator to get started.
                </p>
            </div>
        @endif
    </div>
</x-app-layout>