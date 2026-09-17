<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-3 dark:border-gray-800">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Route Information</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">General details, assigned driver, buses and schedule configuration</p>
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

    <dl class="grid grid-cols-2 gap-x-4 gap-y-5 text-sm">
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Route Name</dt>
            <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $route->name }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Route Code</dt>
            <dd class="truncate font-mono font-semibold text-brand-600 dark:text-brand-400">{{ $route->route_code }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Route Type</dt>
            <dd>
                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $route->route_type_color_classes }}">
                    {{ $route->route_type_label }}
                </span>
            </dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Distance</dt>
            <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $route->estimated_distance ? $route->estimated_distance . ' km' : '—' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Duration</dt>
            <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $route->estimated_duration ? $route->estimated_duration . ' mins' : '—' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Start Location</dt>
            <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $route->start_location }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">End Location</dt>
            <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $route->end_location }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">School</dt>
            <dd class="truncate">
                @if ($route->school)
                    <a href="{{ route('schools.show', $route->school) }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">{{ $route->school->name }}</a>
                @else
                    <span class="font-medium text-gray-900 dark:text-white">—</span>
                @endif
            </dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Driver</dt>
            @if ($route->activeTrip?->bus?->drivers?->first())
                <dd>
                    <a href="{{ route('drivers.show', $route->activeTrip->bus->drivers->first()) }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">{{ $route->activeTrip->bus->drivers->first()->full_name }}</a>
                </dd>
            @else
                <dd class="truncate font-medium text-gray-900 dark:text-white">—</dd>
            @endif
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Assigned Buses</dt>
            <dd>
                @if ($route->activeTrip?->bus)
                    <a href="{{ route('buses.show', $route->activeTrip->bus) }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">{{ $route->activeTrip->bus->bus_number }}</a>
                @else
                    <span class="font-medium text-gray-900 dark:text-white">—</span>
                @endif
            </dd>
        </div>
        <div class="col-span-2">
            <dt class="text-gray-500 dark:text-gray-400">Record Created</dt>
            <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $route->created_at ? $route->created_at->format('M d, Y h:i A') : '—' }}</dd>
        </div>
        <div class="col-span-2">
            <dt class="text-gray-500 dark:text-gray-400">Last Updated</dt>
            <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $route->updated_at ? $route->updated_at->format('M d, Y h:i A') : '—' }}</dd>
        </div>
    </dl>
</div>