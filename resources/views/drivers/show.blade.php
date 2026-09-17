<x-app-layout page="drivers">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">

    {{-- PAGE HEADER --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                Driver Details
            </h1>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                View complete information about {{ $driver->full_name }}
            </p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">

            <a
                href="{{ route('drivers.edit', $driver) }}"
                class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto"
            >
                Edit Driver
            </a>

            <a
                href="{{ route('drivers.index') }}"
                class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            >
                Back
            </a>

        </div>

    </div>


    @php
        $driverTabs = [
            ['key' => 'overview', 'label' => 'Overview', 'url' => route('drivers.show', $driver), 'icon' => 'user-circle'],
            ['key' => 'assigned', 'label' => 'Assigned', 'url' => route('drivers.show', [$driver, 'tab' => 'assigned']), 'icon' => 'truck', 'count' => $driver->buses->count()],
            ['key' => 'routes', 'label' => 'Route', 'url' => route('drivers.show', [$driver, 'tab' => 'routes']), 'icon' => 'map', 'count' => $driver->routes->count()],
            ['key' => 'trips', 'label' => 'Trip History', 'url' => route('drivers.show', [$driver, 'tab' => 'trips']), 'icon' => 'clock', 'count' => $tripCount],
        ];
    @endphp

    <x-pill-tabs :tabs="$driverTabs" :active="$tab" />


    {{-- TAB: OVERVIEW --}}
    @if ($tab === 'overview')
    <div class="mt-6 flex flex-col space-y-6">

        {{-- PROFILE & CONTACT --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="text-center">
                <div class="mb-4 flex justify-center">
                    @if ($driver->profile_photo)
                        <img
                            src="{{ asset('storage/' . $driver->profile_photo) }}"
                            alt="{{ $driver->full_name }}"
                            class="h-28 w-28 rounded-full object-cover ring-4 ring-gray-100 dark:ring-gray-800"
                        >
                    @else
                        <div
                            class="flex h-28 w-28 items-center justify-center rounded-full bg-brand-100 text-4xl font-semibold text-brand-600 ring-4 ring-gray-100 dark:bg-brand-500/10 dark:text-brand-400 dark:ring-gray-800"
                        >
                            {{ strtoupper(substr($driver->first_name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ $driver->full_name }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $driver->employee_id }}
                </p>
                <div class="mt-3">
                    @if ($driver->status === 'Active')
                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                    @elseif ($driver->status === 'Suspended')
                        <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400">Suspended</span>
                    @else
                        <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">Inactive</span>
                    @endif
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-x-4 gap-y-5 border-t border-gray-100 pt-5 text-sm dark:border-gray-800 sm:grid-cols-4">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">School</dt>
                    <dd class="mt-0.5 truncate">
                        @if ($driver->school)
                            <a href="{{ route('schools.show', $driver->school) }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">{{ $driver->school->name }}</a>
                        @else
                            <span class="font-medium text-gray-900 dark:text-white">—</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Phone</dt>
                    <dd class="mt-0.5 truncate font-medium text-gray-900 dark:text-white">{{ $driver->phone }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                    <dd class="mt-0.5 truncate font-medium text-gray-900 dark:text-white">{{ $driver->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Joining Date</dt>
                    <dd class="mt-0.5 truncate font-medium text-gray-900 dark:text-white">{{ $driver->joining_date?->format('M d, Y') ?? '—' }}</dd>
                </div>
            </div>
        </div>


        {{-- PERSONAL INFORMATION --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 border-b border-gray-100 pb-3 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Personal Information</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Basic information about the driver</p>
            </div>

            <dl class="grid grid-cols-2 gap-x-4 gap-y-5 text-sm">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">First Name</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->first_name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Last Name</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->last_name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Gender</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->gender }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Date of Birth</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->date_of_birth?->format('M d, Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Phone Number</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->phone }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Email Address</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->email ?? '—' }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-gray-500 dark:text-gray-400">Address</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ $driver->address }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">City</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->city ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">State</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->state ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Country</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->country ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Postal Code</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->postal_code ?? '—' }}</dd>
                </div>
            </dl>
        </div>


        {{-- LICENSE INFORMATION --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 border-b border-gray-100 pb-3 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">License Information</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Driver's driving license and experience details</p>
            </div>

            <dl class="grid grid-cols-2 gap-x-4 gap-y-5 text-sm lg:grid-cols-3">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">License Number</dt>
                    <dd class="truncate font-mono font-semibold text-gray-900 dark:text-white">{{ $driver->license_number }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">License Type</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->license_type }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Experience</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->experience_years ?? 0 }} years</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Issue Date</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->license_issue_date?->format('M d, Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Expiry Date</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->license_expiry_date?->format('M d, Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">License Status</dt>
                    <dd>
                        @if ($driver->license_expiry_date && $driver->license_expiry_date->isPast())
                            <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400">Expired</span>
                        @elseif ($driver->license_expiry_date && $driver->license_expiry_date->diffInDays(now()) <= 30)
                            <span class="inline-flex rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">Expiring Soon</span>
                        @else
                            <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Valid</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>


        {{-- EMPLOYMENT INFORMATION --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 border-b border-gray-100 pb-3 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Employment Information</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">School and employment details</p>
            </div>

            <dl class="grid grid-cols-2 gap-x-4 gap-y-5 text-sm">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">School</dt>
                    <dd class="truncate font-semibold text-gray-900 dark:text-white">{{ $driver->school->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Employee ID</dt>
                    <dd class="truncate font-semibold text-gray-900 dark:text-white">{{ $driver->employee_id }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Joining Date</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->joining_date?->format('M d, Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Employment Status</dt>
                    <dd>
                        @if ($driver->status === 'Active')
                            <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                        @elseif ($driver->status === 'Suspended')
                            <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400">Suspended</span>
                        @else
                            <span class="inline-flex rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">Inactive</span>
                        @endif
                    </dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-gray-500 dark:text-gray-400">Remarks</dt>
                    <dd class="text-sm leading-6 text-gray-700 dark:text-gray-300">{{ $driver->remarks ?? 'No remarks available.' }}</dd>
                </div>
            </dl>

            <div class="mt-5 border-t border-gray-100 pt-4 dark:border-gray-800">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Record Information</p>
                <div class="mt-3 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-3">
                    <div>
                        <p class="text-xs text-gray-400">Created By</p>
                        <p class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">{{ $driver->creator->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Created On</p>
                        <p class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">{{ $driver->created_at?->format('M d, Y h:i A') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Last Updated</p>
                        <p class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">{{ $driver->updated_at?->format('M d, Y h:i A') ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>


        {{-- EMERGENCY CONTACT --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 border-b border-gray-100 pb-3 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Emergency Contact</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Emergency contact information</p>
            </div>

            <dl class="grid grid-cols-2 gap-x-4 gap-y-5 text-sm">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Contact Name</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->emergency_contact_name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Contact Number</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $driver->emergency_contact_phone ?? '—' }}</dd>
                </div>
            </dl>
        </div>

    </div>


    {{-- TAB: ASSIGNED --}}
    @elseif ($tab === 'assigned')
    <div class="mt-6 flex flex-col space-y-6">

        {{-- ASSIGNED BUSES --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Assigned Buses</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Buses assigned to this driver</p>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($driver->buses as $bus)
                    <div class="flex items-center justify-between gap-3 px-6 py-4">
                        <div class="min-w-0 flex-1">
                            <a
                                href="{{ route('buses.show', $bus) }}"
                                class="truncate text-sm font-semibold text-brand-600 hover:underline dark:text-brand-400"
                            >
                                {{ $bus->bus_number }}
                            </a>
                            <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ $bus->registration_number }}</p>
                        </div>
                        @if ($bus->status === 'Active')
                            <span class="inline-flex shrink-0 rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                        @elseif ($bus->status === 'Maintenance')
                            <span class="inline-flex shrink-0 rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">Maintenance</span>
                        @else
                            <span class="inline-flex shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $bus->status }}</span>
                        @endif
                    </div>
                @empty
                    <p class="px-6 py-5 text-sm text-gray-500 dark:text-gray-400">No buses assigned.</p>
                @endforelse
            </div>
        </div>


        {{-- ASSIGNED ROUTES --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Assigned Routes</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Routes assigned to this driver</p>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($driver->routes as $route)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <a
                                href="{{ route('routes.show', $route) }}"
                                class="min-w-0 truncate text-sm font-semibold text-brand-600 hover:underline dark:text-brand-400"
                            >
                                {{ $route->name }}
                            </a>
                            <span class="{{ $route->route_type_color_classes }} inline-flex shrink-0 rounded-full px-2.5 py-1 text-xs font-medium">{{ $route->route_type_label }}</span>
                        </div>
                        <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $route->start_location ?: '—' }}
                            @if ($route->start_location && $route->end_location)
                                →
                            @endif
                            {{ $route->end_location ?: '' }}
                        </p>
                    </div>
                @empty
                    <p class="px-6 py-5 text-sm text-gray-500 dark:text-gray-400">No routes assigned.</p>
                @endforelse
            </div>
        </div>

    </div>


    {{-- TAB: ROUTE STOPS MAP --}}
    @elseif ($tab === 'routes')
    @php
        $mapRoutes = $assignedRoutes->filter(
            fn ($route) => collect($route['stops'])
                ->filter(fn ($stop) => $stop['latitude'] && $stop['longitude'])
                ->count() >= 2
        )->values();
        $palette = ['#4F46E5', '#0EA5E9', '#D946EF', '#F97316', '#10B981', '#EF4444'];
    @endphp

    <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Route Stops Map</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Stops for every route assigned to {{ $driver->first_name }}</p>
                    </div>
                </div>
                @if ($mapRoutes->isNotEmpty())
                    <button
                        type="button"
                        id="fitAllRoutesBtn"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <svg class="h-4 w-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                        Fit All Routes
                    </button>
                @endif
            </div>
        </div>
        <div class="p-6">
            @if ($mapRoutes->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No route stops with coordinates are available for this driver's assigned routes.</p>
            @else
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    @foreach ($mapRoutes as $index => $route)
                        <button
                            type="button"
                            data-route-id="{{ $route['id'] }}"
                            class="driver-route-chip inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-xs ring-2 ring-offset-2 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:ring-offset-gray-900"
                        >
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $palette[$index % count($palette)] }}"></span>
                            {{ $route['name'] }}
                        </button>
                    @endforeach
                </div>
                <div class="relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800" style="min-height: 440px;">
                    <div id="driverRouteStopsMap" class="h-[440px] w-full z-0"></div>
                </div>
            @endif
        </div>
    </div>


    {{-- TAB: TRIP HISTORY --}}
    @elseif ($tab === 'trips')
    <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Trip History</h2>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $trips->total() }} trip{{ $trips->total() === 1 ? '' : 's' }}</span>
                <a href="{{ route('drivers.trips', $driver) }}"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    View Full History
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 dark:border-gray-800">
                    <tr class="text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-3 font-medium">Date</th>
                        <th class="px-5 py-3 font-medium">Bus</th>
                        <th class="px-5 py-3 font-medium">Route</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Duration</th>
                        <th class="px-5 py-3 font-medium">Started</th>
                        <th class="px-5 py-3 font-medium">Ended</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($trips as $trip)
                        <tr class="text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-3">{{ $trip->started_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @if ($trip->bus)
                                    <a href="{{ route('buses.show', $trip->bus) }}" class="text-brand-600 hover:underline dark:text-brand-400">{{ $trip->bus->bus_number }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    @if ($trip->route)
                                        <a href="{{ route('routes.show', $trip->route) }}"
                                            class="text-brand-600 hover:underline dark:text-brand-400">{{ $trip->route->name }}</a>
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs font-medium {{ $trip->route->route_type_color_classes }}">
                                            {{ $trip->route->route_type_label }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                @if ($trip->status === 'in_progress')
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-500/10 dark:text-green-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse mr-1"></span>
                                        In Progress
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        Completed
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3">{{ $trip->durationInMinutes() ?? '—' }} min</td>
                            <td class="px-5 py-3">{{ $trip->started_at?->format('H:i:s') ?? '—' }}</td>
                            <td class="px-5 py-3">{{ $trip->ended_at?->format('H:i:s') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No trips recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($trips->hasPages())
                <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-800">
                    {{ $trips->links() }}
                </div>
            @endif
        </div>
    </div>
    @endif


    </div>
</x-app-layout>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@if ($tab === 'routes' && $mapRoutes->isNotEmpty())
    <script>
        const DRIVER_ROUTE_PALETTE = @json($palette);

        document.addEventListener('DOMContentLoaded', function () {
            initDriverRouteStopsMap();
        });

        async function initDriverRouteStopsMap() {
            const routesData = @json($mapRoutes);
            const mapEl = document.getElementById('driverRouteStopsMap');

            if (!mapEl || typeof L === 'undefined') return;

            window.driverRouteMap = L.map(mapEl, {
                preferCanvas: true,
                updateWhenIdle: true,
                zoomAnimation: true,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(window.driverRouteMap);

            window.driverRouteLayers = {};
            let rendered = 0;
            const drawPromises = [];

            routesData.forEach((route, index) => {
                const stops = (route.stops || []).filter(
                    s => s.latitude && s.longitude && Number(s.latitude) !== 0 && Number(s.longitude) !== 0
                );

                if (stops.length < 2) return;

                const color = DRIVER_ROUTE_PALETTE[index % DRIVER_ROUTE_PALETTE.length];
                const group = L.featureGroup().addTo(window.driverRouteMap);

                stops.forEach(stop => {
                    const latlng = [Number(stop.latitude), Number(stop.longitude)];
                    const icon = L.divIcon({
                        className: '',
                        html: `<div style="background:${color};color:#fff;border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:10px;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3);">${stop.stop_order}</div>`,
                        iconSize: [22, 22],
                        iconAnchor: [11, 11],
                    });

                    L.marker(latlng, { icon })
                        .addTo(group)
                        .bindTooltip(`<b>Stop ${stop.stop_order}:</b> ${stop.name}`);
                });

                drawPromises.push(drawDriverRoutePath(stops, color, group));

                window.driverRouteLayers[route.id] = { group, color, visible: true };
                rendered++;
            });

            await Promise.all(drawPromises);

            if (rendered > 0) {
                fitDriverRouteBounds();
                bindDriverRouteControls();
            } else {
                window.driverRouteMap.setView([27.7172, 85.324], 12);
            }
        }

        async function drawDriverRoutePath(stops, color, group) {
            const waypoints = stops.map(s => [Number(s.latitude), Number(s.longitude)]);
            let latlngs = null;

            try {
                const coordStr = waypoints.map(latlng => `${latlng[1]},${latlng[0]}`).join(';');
                const url = `https://router.project-osrm.org/route/v1/driving/${coordStr}?overview=full&geometries=geojson`;
                const response = await fetch(url);
                const data = await response.json();

                if (data.code === 'Ok' && data.routes && data.routes[0] && data.routes[0].geometry) {
                    latlngs = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                }
            } catch (err) {
                console.warn('OSRM routing request failed, falling back to straight line:', err);
            }

            if (!latlngs) latlngs = waypoints;

            L.polyline(latlngs, { color, weight: 7, opacity: 0.18, lineCap: 'round' }).addTo(group);
            L.polyline(latlngs, { color, weight: 3, opacity: 0.8, lineCap: 'round' }).addTo(group);
        }

        function fitDriverRouteBounds() {
            if (!window.driverRouteMap) return;

            const bounds = L.latLngBounds([]);

            Object.values(window.driverRouteLayers).forEach(layer => {
                if (layer.visible && layer.group.getLayers().length > 0) {
                    bounds.extend(layer.group.getBounds());
                }
            });

            if (bounds.isValid()) {
                window.driverRouteMap.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });
            }
        }

        function bindDriverRouteControls() {
            document.querySelectorAll('.driver-route-chip').forEach(btn => {
                btn.addEventListener('click', () => {
                    const layer = window.driverRouteLayers[btn.dataset.routeId];
                    if (!layer) return;

                    if (layer.visible) {
                        window.driverRouteMap.removeLayer(layer.group);
                        layer.visible = false;
                        btn.classList.remove('ring-2', 'ring-offset-2');
                    } else {
                        layer.group.addTo(window.driverRouteMap);
                        layer.visible = true;
                        btn.classList.add('ring-2', 'ring-offset-2');
                    }

                    fitDriverRouteBounds();
                });
            });

            const fitAllBtn = document.getElementById('fitAllRoutesBtn');
            if (fitAllBtn) {
                fitAllBtn.addEventListener('click', fitDriverRouteBounds);
            }
        }
    </script>
@endif