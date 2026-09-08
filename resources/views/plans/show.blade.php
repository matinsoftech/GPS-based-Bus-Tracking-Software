<x-app-layout page="plans">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Plan details and features.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('plans.edit', $plan) }}"
                    class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    Edit
                </a>
                <a href="{{ route('plans.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    Back
                </a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-sm text-gray-500 dark:text-gray-400">Monthly Price</span>
                <h4 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">₹{{ number_format($plan->monthly_price, 2) }}</h4>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-sm text-gray-500 dark:text-gray-400">Yearly Price</span>
                <h4 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">₹{{ number_format($plan->yearly_price, 2) }}</h4>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                <h4 class="mt-1">
                    @if ($plan->is_active)
                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600 dark:bg-green-500/15 dark:text-green-500">
                            Active
                        </span>
                    @else
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-500/15 dark:text-gray-400">
                            Inactive
                        </span>
                    @endif
                </h4>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-sm text-gray-500 dark:text-gray-400">Features Enabled</span>
                <h4 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ collect($plan->features ?? [])->filter()->count() }}
                </h4>
            </div>
        </div>

        {{-- Limits --}}
        <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Limits</h2>
            <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $plan->max_buses ?? '∞' }}</div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Buses</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $plan->max_students ?? '∞' }}</div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Students</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $plan->max_parents ?? '∞' }}</div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Parents</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $plan->max_drivers ?? '∞' }}</div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Drivers</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $plan->max_routes ?? '∞' }}</div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Routes</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $plan->max_devices ?? '∞' }}</div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Devices</div>
                </div>
            </div>
        </div>

        {{-- Features --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Features</h2>
            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                @php
                    $featureLabels = [
                        'live_tracking' => 'Live Tracking',
                        'parent_app' => 'Parent App',
                        'notifications' => 'Notifications',
                        'attendance' => 'Attendance',
                        'reports' => 'Reports',
                        'analytics' => 'Analytics',
                    ];
                @endphp
                @foreach ($featureLabels as $key => $label)
                    @php $enabled = ($plan->features[$key] ?? false); @endphp
                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        @if ($enabled)
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-500/15">
                                <x-heroicon-s-check class="h-4 w-4 text-green-600 dark:text-green-400" />
                            </span>
                        @else
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-500/15">
                                <x-heroicon-s-x-mark class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                            </span>
                        @endif
                        <span class="text-sm font-medium {{ $enabled ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}">
                            {{ $label }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
