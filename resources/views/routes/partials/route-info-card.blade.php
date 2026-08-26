<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Route Information</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">General details, assigned driver, buses and schedule configuration</p>
            </div>
        </div>
        <div>
            @if ($route->is_active)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20">
                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                    Active
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-500/20 dark:bg-gray-800 dark:text-gray-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                    Inactive
                </span>
            @endif
        </div>
    </div>

    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <!-- Route Name -->
        <div class="rounded-xl bg-gray-50/60 p-4 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60 flex flex-col justify-between">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Route Name</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $route->name }}</dd>
        </div>

        <!-- Route Code -->
        <div class="rounded-xl bg-gray-50/60 p-4 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60 flex flex-col justify-between">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Route Code</dt>
            <dd class="mt-1 text-sm font-semibold text-brand-600 dark:text-brand-400 font-mono">{{ $route->route_code }}</dd>
        </div>

        <!-- Start Location -->
        <div class="rounded-xl bg-gray-50/60 p-4 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60 flex flex-col justify-between">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Start Location</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $route->start_location }}</dd>
        </div>

        <!-- End Location -->
        <div class="rounded-xl bg-gray-50/60 p-4 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60 flex flex-col justify-between">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">End Location</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $route->end_location }}</dd>
        </div>

        <!-- Estimated Distance -->
        <div class="rounded-xl bg-gray-50/60 p-4 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60 flex flex-col justify-between">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Distance</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $route->estimated_distance ? $route->estimated_distance . ' km' : '—' }}</dd>
        </div>

        <!-- Estimated Duration -->
        <div class="rounded-xl bg-gray-50/60 p-4 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60 flex flex-col justify-between">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Duration</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $route->estimated_duration ? $route->estimated_duration . ' mins' : '—' }}</dd>
        </div>

        <!-- School -->
        <div class="rounded-xl bg-gray-50/60 p-4 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60 flex flex-col justify-between">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">School</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $route->school->name ?? '—' }}</dd>
        </div>

        <!-- Driver -->
        <div class="rounded-xl bg-gray-50/60 p-4 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60 flex flex-col justify-between">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Driver</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $route->buses->first()?->drivers->first()?->full_name ?? '—' }}</dd>
        </div>

        <!-- Buses -->
        <div class="rounded-xl bg-gray-50/60 p-4 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60 flex flex-col justify-between sm:col-span-2 lg:col-span-1 xl:col-span-2">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Assigned Buses</dt>
            <dd class="mt-1.5 flex flex-wrap gap-1.5 items-center">
                @forelse ($route->buses ?? [] as $bus)
                    <span class="inline-flex items-center rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-400 border border-brand-200/60 dark:border-brand-500/20">
                        🚍{{ $bus->bus_number }}
                    </span>
                @empty
                    <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">—</span>
                @endforelse
            </dd>
        </div>

        <!-- Created At -->
        <div class="rounded-xl bg-gray-50/60 p-4 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60 flex flex-col justify-between">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Created</dt>
            <dd class="mt-1 text-xs font-medium text-gray-700 dark:text-gray-300">{{ $route->created_at ? $route->created_at->format('M d, Y H:i') : '—' }}</dd>
        </div>

        <!-- Updated At -->
        <div class="rounded-xl bg-gray-50/60 p-4 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60 flex flex-col justify-between">
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Updated</dt>
            <dd class="mt-1 text-xs font-medium text-gray-700 dark:text-gray-300">{{ $route->updated_at ? $route->updated_at->format('M d, Y H:i') : '—' }}</dd>
        </div>
    </dl>
</div>
