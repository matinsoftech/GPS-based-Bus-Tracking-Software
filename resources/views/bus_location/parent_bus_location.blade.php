<x-app-layout page="bus-location">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6 space-y-6">

        <!-- Page Header & Child Selector -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-5 dark:border-gray-800">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Bus Location & Live Tracking</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Real-time tracking, live telemetry, and stop schedule for your child's school bus.
                        </p>
                    </div>
                </div>
            </div>

            @if ($children->count() > 1)
            <!-- Multiple Children Selector Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 custom-scrollbar">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 shrink-0">Select Child:</span>
                @foreach ($children as $child)
                @php
                $isSelected = $selectedChild && $selectedChild->id === $child->id;
                @endphp
                <a
                    href="{{ route('bus_location', ['child_id' => $child->id]) }}"
                    class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-semibold transition shrink-0 border {{ $isSelected ? 'bg-brand-500 text-white border-brand-500 shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-750' }}">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold {{ $isSelected ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                        {{ strtoupper(substr($child->first_name ?? $child->full_name, 0, 1)) }}
                    </span>
                    <span>{{ $child->full_name }}</span>
                    @if ($child->bus)
                    <span class="text-[10px] opacity-80 font-mono">({{ $child->bus->bus_number }})</span>
                    @endif
                </a>
                @endforeach
            </div>
            @endif
        </div>

        @if (!$selectedChild || !$bus || $routes->isEmpty())
        <!-- Empty / Unassigned State Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-8 md:p-12 text-center shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 mb-4">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">No Bus Route Assigned</h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                @if ($selectedChild)
                <strong>{{ $selectedChild->full_name }}</strong> is currently not assigned to a school bus route.
                @else
                No children linked to your parent account are assigned to a bus route.
                @endif
                Please contact your school administration to configure bus transport.
            </p>
            <div class="mt-6">
                <a href="{{ route('parent.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-600">
                    Back to Dashboard
                </a>
            </div>
        </div>
        @else

        @php $route = $routes->first(); @endphp

        <!-- "Where is My Bus?" Telemetry & Information Cards Grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">

            <!-- Card 1: Bus Status & Speed -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03] flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Bus Status</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center gap-2">
                        <span id="liveBusStatusBadge" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Moving
                        </span>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white font-mono">
                        <span id="liveBusSpeed">32</span> <span class="text-xs font-normal text-gray-500 dark:text-gray-400">km/h</span>
                    </p>
                    <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">
                        Last updated: <span id="liveLastUpdateText">Just now</span>
                    </p>
                </div>
            </div>

            <!-- Card 2: Next Stop & ETA -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03] flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Next Stop & ETA</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <p id="liveNextStopCardText" class="text-sm font-bold text-brand-600 dark:text-brand-400 truncate">
                        {{ $route->stops->skip(1)->first()->name ?? $route->end_location }}
                    </p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white font-mono">
                        <span id="liveEtaCardText">Awaiting signal</span>
                    </p>
                    <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500 flex items-center justify-between">
                        <span>Distance:</span>
                        <span id="liveDistCardText" class="font-mono font-semibold text-gray-700 dark:text-gray-300">—</span>
                    </p>
                </div>
            </div>

            <!-- Card 3: Route Details -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03] flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Assigned Route</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                        {{ $route->name }}
                    </p>
                    <p class="mt-1 text-xs font-mono font-semibold text-purple-600 dark:text-purple-400">
                        Code: {{ $route->route_code }}
                    </p>
                    <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 truncate">
                        Start: {{ $route->start_location }} → End: {{ $route->end_location }}
                    </p>
                </div>
            </div>

            <!-- Card 4: Bus & Driver Details -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03] flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Bus & Driver</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Bus #{{ $bus->bus_number }}</span>
                        @if ($bus->registration_number)
                        <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-mono text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            {{ $bus->registration_number }}
                        </span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                        Driver: {{ $bus->driver?->full_name ?? 'Assigned Driver' }}
                    </p>
                    @if ($bus->driver?->phone)
                    <a
                        href="tel:{{ $bus->driver->phone }}"
                        class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        Call {{ $bus->driver->phone }}
                    </a>
                    @endif
                </div>
            </div>

            <!-- Card 5: Child Transport Info -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03] flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Child Info</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                        {{ $selectedChild->full_name }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ trim($selectedChild->grade.' '.$selectedChild->section) }} • Roll #{{ $selectedChild->roll_no ?? '—' }}
                    </p>
                    <p class="mt-2 text-[11px] text-gray-600 dark:text-gray-300 truncate">
                        Stop: <strong>{{ $selectedChild->pickup_location ?? ($route->stops->first()->name ?? 'Assigned Stop') }}</strong>
                    </p>
                </div>
            </div>

        </div>

        <!-- Main Content: Route Map (Left) & Bus Stops Timeline (Right) -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

            <!-- Left Column: Live Fleet Map (shared map component, same filters as every role) -->
            <div class="xl:col-span-7 min-w-0">
                @include('partials.fleet-map', [
                    'fleetMap' => $fleetMap,
                    'fleetMapRefreshUrl' => route('bus_location.latest'),
                    'fleetMapTitle' => 'My Bus Location',
                    'fleetMapSubtitle' => 'Live GPS position of your children\'s buses, route paths, and stops.',
                    'fleetMapCompact' => true,
                ])
            </div>

            <!-- Right Column: Bus Stops Vertical Timeline ("Where Is My Bus") -->
            <div class="xl:col-span-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">

                <!-- Timeline Section Header -->
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Bus Stops Timeline</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Real-time stop completion & ETAs</p>
                        </div>
                    </div>

                    <span id="journeyStatusPill" class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                        <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                        Awaiting Signal
                    </span>
                </div>

                <!-- Journey Progress Bar Card -->
                <div class="mb-5 rounded-xl bg-gray-50/80 p-4 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60">
                    <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                        <span class="text-gray-600 dark:text-gray-400">Overall Route Progress</span>
                        <span id="progressBarLabel" class="text-brand-600 dark:text-brand-400 font-mono">0% Completed</span>
                    </div>
                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        <div
                            id="journeyProgressBar"
                            class="h-full rounded-full bg-gradient-to-r from-brand-500 to-emerald-500 transition-all duration-500 ease-out"
                            style="width: 0%;"></div>
                    </div>
                </div>

                <!-- Vertical Journey Timeline List -->
                <div class="relative pl-2 pr-1 py-1 max-h-[440px] overflow-y-auto custom-scrollbar">
                    <div class="space-y-5" id="journeyTimelineContainer">
                        @forelse ($route->stops as $index => $stop)
                        @php
                        $isLast = $index === ($route->stops->count() - 1);
                        @endphp

                        <div
                            id="journeyStopItem-{{ $index }}"
                            class="journey-stop-row relative flex items-start gap-3.5 transition-all duration-300"
                            data-stop-index="{{ $index }}">
                            <!-- Node Marker & Connector Line -->
                            <div class="relative flex flex-col items-center">
                                <!-- Node Icon -->
                                <div
                                    id="stopNodeIcon-{{ $index }}"
                                    class="node-icon flex h-8 w-8 items-center justify-center rounded-full border-2 transition-all duration-300 z-10 bg-white border-gray-300 text-gray-400 dark:bg-gray-900 dark:border-gray-700">
                                    <span class="text-xs font-mono font-bold">{{ $stop->stop_order }}</span>
                                </div>

                                <!-- Vertical Connector Line -->
                                @if (!$isLast)
                                <div
                                    id="stopConnectorLine-{{ $index }}"
                                    class="connector-line w-0.5 h-12 bg-gray-200 dark:bg-gray-800 transition-colors duration-500 my-1"></div>
                                @endif
                            </div>

                            <!-- Stop Info Box -->
                            <div
                                id="stopCardBox-{{ $index }}"
                                class="stop-card-box flex-1 rounded-xl p-3 border transition-all duration-300 bg-gray-50/50 border-gray-100 dark:bg-gray-800/30 dark:border-gray-800/60">
                                <div class="flex flex-wrap items-center justify-between gap-1.5">
                                    <div class="flex items-center gap-2">
                                        <h3 id="stopTitleText-{{ $index }}" class="text-xs font-semibold text-gray-900 dark:text-white">
                                            {{ $stop->name }}
                                        </h3>

                                        <span
                                            id="stopBadge-{{ $index }}"
                                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold hidden"></span>
                                    </div>

                                    <div class="text-right">
                                        <div class="text-[11px] font-mono font-semibold text-gray-700 dark:text-gray-300">
                                            Sched: {{ $stop->pickup_time ? date('h:i A', strtotime($stop->pickup_time)) : ($stop->drop_time ? date('h:i A', strtotime($stop->drop_time)) : '07:00 AM') }}
                                        </div>
                                        <div id="stopEtaText-{{ $index }}" class="text-[11px] font-mono font-medium text-gray-600 dark:text-gray-400">—</div>
                                    </div>
                                </div>

                                <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 flex items-center justify-between">
                                    <span>Stop #{{ $stop->stop_order }}</span>
                                    <span id="stopDistanceText-{{ $index }}" class="font-mono">—</span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-6">
                            No route stops configured for this bus route.
                        </p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        @endif

    </div>
</x-app-layout>

<!-- Leaflet CSS & JS -->
@if ($routes->isNotEmpty())
<script>
    let gpsPollTimer = null;

    const liveJourneyStops = @json($route->stops);

    const latestLocation = @json($latestLocation);

    const gpsEndpoint = '{{ route('bus_location.latest') }}' + '?child_id=' + @json($selectedChild?->id);

    let liveBusGps = null;

    // Journey progression: index of the furthest stop the bus has arrived at.
    // null = no stop reached yet; only ever increases (the bus never goes backwards).
    let journeyReachedIdx = null;

    // Distance (km) within which a stop is treated as "reached".
    const ARRIVED_RADIUS_KM = 0.2;

    // Speed (km/h) at or below which the bus is treated as stationary.
    const STOPPED_SPEED_KPH = 3;

    // A bus further than this from every configured stop is considered off-route.
    const ON_ROUTE_THRESHOLD_KM = 1.5;

    document.addEventListener('DOMContentLoaded', function() {
        // Reset the journey timeline to a neutral state so it never shows a fake
        // "current stop" before the first live GPS fix arrives.
        renderJourneyNeutral(liveJourneyStops.length);
        setJourneyPill('Awaiting Signal', '#6b7280');

        // Initial render + start real-time polling (every 5 seconds, no reload).
        updateLiveGps(latestLocation);
        startLivePolling();
    });

    /**
     * Single entry point that applies a live GPS JSON payload to every UI
     * component: speed, status, last updated and the journey timeline
     * (nearest stop, real ETA, real distance). The map itself is driven by
     * the shared fleet-map partial, not by this script.
     */
    function updateLiveGps(data) {
        if (!data || data.latitude == null || data.longitude == null) {
            // No usable fix (offline / no data yet): show offline telemetry and a
            // neutral journey timeline instead of stale "first stop" defaults.
            updateTelemetryCards({
                speed_kmh: 0,
                status: 'offline',
                status_label: 'No Signal',
                status_color: '#6b7280',
                last_updated_ago: null,
            });
            updateLiveJourneyState(null);
            return;
        }

        const lat = parseFloat(data.latitude);
        const lng = parseFloat(data.longitude);
        const speed = parseFloat(data.speed_kmh) || 0;
        const heading = data.course != null ? parseFloat(data.course) : parseFloat((data.marker || {}).heading);
        const course = isNaN(heading) ? 0 : heading;

        liveBusGps = {
            latitude: lat,
            longitude: lng,
            speed_kmh: speed,
            course: course,
            status: data.status,
            status_label: data.status_label || 'Offline',
            status_color: data.status_color || '#6b7280',
            gps_time: data.gps_time,
            last_updated_at: data.last_updated_at,
            last_updated_ago: data.last_updated_ago,
            asset_name: data.asset_name,
            imei: data.imei
        };

        // 1. Telemetry cards: speed, status, last updated
        updateTelemetryCards(liveBusGps);

        // 2. Journey timeline, ETA and distance
        updateLiveJourneyState(liveBusGps);
    }

    function updateTelemetryCards(gps) {
        const speedEl = document.getElementById('liveBusSpeed');
        if (speedEl) speedEl.innerText = Math.round(gps.speed_kmh);

        const badge = document.getElementById('liveBusStatusBadge');
        if (badge) {
            badge.innerHTML = '<span class="h-1.5 w-1.5 rounded-full animate-pulse" style="background-color:' + gps.status_color + '"></span>' + gps.status_label;
            badge.style.backgroundColor = gps.status_color + '1a';
            badge.style.borderColor = gps.status_color + '40';
            badge.style.color = gps.status_color;
        }

        const statusPill = document.getElementById('journeyStatusPill');
        if (statusPill) {
            statusPill.innerHTML = '<span class="h-2 w-2 rounded-full animate-pulse" style="background-color:' + gps.status_color + '"></span>' + gps.status_label;
            statusPill.style.backgroundColor = gps.status_color + '1a';
            statusPill.style.borderColor = gps.status_color + '40';
            statusPill.style.color = gps.status_color;
        }

        const timeEl = document.getElementById('liveLastUpdateText');
        if (timeEl) timeEl.innerText = gps.last_updated_ago || gps.last_updated_at || '—';
    }

    function liveGpsDistanceKm(lat1, lon1, lat2, lon2) {
        const toRad = (deg) => (deg * Math.PI) / 180;
        const earthRadiusKm = 6371;
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a = Math.sin(dLat / 2) ** 2 +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
        return earthRadiusKm * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    // Nearest stop is found purely by Haversine distance from the live GPS fix.
    function liveNearestStop(gps) {
        let idx = -1;
        let distance = Infinity;

        for (let i = 0; i < liveJourneyStops.length; i++) {
            const stop = liveJourneyStops[i];
            if (stop && stop.latitude && stop.longitude && parseFloat(stop.latitude) !== 0 && parseFloat(stop.longitude) !== 0) {
                const dist = liveGpsDistanceKm(gps.latitude, gps.longitude, parseFloat(stop.latitude), parseFloat(stop.longitude));
                if (dist < distance) {
                    distance = dist;
                    idx = i;
                }
            }
        }

        return { idx, distance };
    }

    function liveStopEtaMinutes(gps, stop) {
        if (!stop || !stop.latitude || !stop.longitude) return null;
        const distKm = liveGpsDistanceKm(gps.latitude, gps.longitude, parseFloat(stop.latitude), parseFloat(stop.longitude));
        if (gps.speed_kmh <= 0) return null;
        return (distKm / gps.speed_kmh) * 60;
    }

    function setJourneyPill(label, color) {
        const pill = document.getElementById('journeyStatusPill');
        if (!pill) return;
        pill.innerHTML = '<span class="h-2 w-2 rounded-full animate-pulse" style="background-color:' + color + '"></span>' + label;
        pill.style.backgroundColor = color + '1a';
        pill.style.borderColor = color + '40';
        pill.style.color = color;
    }

    function liveStopDistanceKm(gps, stop) {
        if (!stop || !stop.latitude || !stop.longitude) return null;
        return liveGpsDistanceKm(gps.latitude, gps.longitude, parseFloat(stop.latitude), parseFloat(stop.longitude));
    }

    /**
     * Reset the journey UI to a neutral "waiting for signal" state. Used on page
     * load and whenever there is no usable GPS fix, so the route never fakes a
     * "current stop" or progress before real telemetry arrives.
     */
    function renderJourneyNeutral(totalStops) {
        const progressBar = document.getElementById('journeyProgressBar');
        const progressLabel = document.getElementById('progressBarLabel');
        if (progressBar) progressBar.style.width = '0%';
        if (progressLabel) progressLabel.innerText = '0% Completed';

        const cardNextStop = document.getElementById('liveNextStopCardText');
        const cardEta = document.getElementById('liveEtaCardText');
        const cardDist = document.getElementById('liveDistCardText');
        if (cardNextStop) cardNextStop.innerText = '—';
        if (cardEta) cardEta.innerText = 'Awaiting signal';
        if (cardDist) cardDist.innerText = '—';

        for (let i = 0; i < totalStops; i++) {
            const iconNode = document.getElementById('stopNodeIcon-' + i);
            const connector = document.getElementById('stopConnectorLine-' + i);
            const cardBox = document.getElementById('stopCardBox-' + i);
            const titleText = document.getElementById('stopTitleText-' + i);
            const badge = document.getElementById('stopBadge-' + i);
            const etaText = document.getElementById('stopEtaText-' + i);
            const distText = document.getElementById('stopDistanceText-' + i);

            if (iconNode) {
                iconNode.className = 'node-icon flex h-8 w-8 items-center justify-center rounded-full bg-white border-2 border-gray-300 text-gray-400 dark:bg-gray-900 dark:border-gray-700 z-10';
                iconNode.innerHTML = '<span class="text-xs font-mono font-bold">' + (i + 1) + '</span>';
            }
            if (connector) connector.className = 'connector-line w-0.5 h-12 bg-gray-200 dark:bg-gray-800 transition-colors duration-500 my-1';
            if (cardBox) cardBox.className = 'stop-card-box flex-1 rounded-xl p-3 border bg-gray-50/40 border-gray-100 dark:bg-gray-800/20 dark:border-gray-800/50';
            if (titleText) titleText.className = 'text-xs font-semibold text-gray-900 dark:text-white';
            if (badge) badge.className = 'hidden';
            if (etaText) {
                etaText.className = 'text-[11px] font-mono font-medium text-gray-600 dark:text-gray-400';
                etaText.innerText = '—';
            }
            if (distText) distText.innerText = '—';
        }
    }

    /**
     * Recompute and render the route progress bar using the real GPS position:
     * completed stops + the fraction of the segment already travelled toward the
     * next stop. Uses live distance and speed, never hard-coded percentages.
     */
    function renderJourneyProgress(gps, reachedIdx, totalStops) {
        const progressBar = document.getElementById('journeyProgressBar');
        const progressLabel = document.getElementById('progressBarLabel');
        const denominator = totalStops - 1;
        let percent = 0;

        if (reachedIdx >= totalStops - 1) {
            percent = 100;
        } else if (reachedIdx >= 0) {
            const anchor = liveJourneyStops[reachedIdx];
            const next = liveJourneyStops[reachedIdx + 1];
            let frac = 1;
            if (anchor && next && anchor.latitude && anchor.longitude && next.latitude && next.longitude) {
                const distToNext = liveGpsDistanceKm(gps.latitude, gps.longitude, parseFloat(next.latitude), parseFloat(next.longitude));
                const anchorToNext = liveGpsDistanceKm(parseFloat(anchor.latitude), parseFloat(anchor.longitude), parseFloat(next.latitude), parseFloat(next.longitude));
                frac = anchorToNext > 0 ? Math.max(0, Math.min(1, 1 - distToNext / anchorToNext)) : 1;
            }
            percent = ((reachedIdx + frac) / denominator) * 100;
        } else if (denominator > 0) {
            // Heading toward the first stop: reflect how close the bus is.
            const nearest = liveNearestStop(gps);
            const approachKm = 2;
            const frac = Math.max(0, Math.min(1, 1 - nearest.distance / approachKm));
            percent = (frac / denominator) * 100;
        }

        percent = Math.round(Math.max(0, Math.min(100, percent)));

        if (progressBar) progressBar.style.width = percent + '%';
        if (progressLabel) progressLabel.innerText = percent + '% Completed';
    }

    /**
     * Update the "Next Stop & ETA" card with the real target stop, distance and
     * ETA computed from the live speed.
     */
    function renderNextStopCard(gps, reachedIdx, totalStops, offRoute) {
        const cardNextStop = document.getElementById('liveNextStopCardText');
        const cardEta = document.getElementById('liveEtaCardText');
        const cardDist = document.getElementById('liveDistCardText');

        if (reachedIdx >= totalStops - 1) {
            if (cardNextStop) cardNextStop.innerText = 'Destination';
            if (cardEta) cardEta.innerText = 'Arrived';
            if (cardDist) cardDist.innerText = 'Journey complete';
            return;
        }

        if (offRoute && reachedIdx < 0) {
            if (cardNextStop) cardNextStop.innerText = '—';
            if (cardEta) cardEta.innerText = 'Off Route';
            if (cardDist) cardDist.innerText = 'No signal near route';
            return;
        }

        const targetIdx = reachedIdx + 1;
        const target = liveJourneyStops[targetIdx];
        if (!target) return;

        const dist = liveStopDistanceKm(gps, target);
        const eta = liveStopEtaMinutes(gps, target);

        if (cardNextStop) cardNextStop.innerText = target.name;

        if (cardEta) {
            if (offRoute) {
                cardEta.innerText = '—';
            } else if (eta == null) {
                cardEta.innerText = gps.speed_kmh <= 0 ? 'Stopped' : '—';
            } else if (eta <= 1) {
                cardEta.innerText = 'In under a minute';
            } else {
                cardEta.innerText = 'In ' + Math.round(eta) + ' mins';
            }
        }

        if (cardDist) cardDist.innerText = dist == null ? '—' : dist.toFixed(1) + ' km away';
    }

    /**
     * Render the vertical stop timeline. Each stop has a lifecycle:
     *   Passed   -> reached earlier and left behind (greyed out with a check)
     *   Arrived  -> the bus has reached this stop (green, "Arrived" badge)
     *   Arriving -> the next stop the bus is heading toward (highlighted)
     *   Upcoming -> later stops with a live ETA and distance
     */
    function renderJourneyTimeline(gps, reachedIdx, totalStops, offRoute) {
        for (let i = 0; i < totalStops; i++) {
            const stop = liveJourneyStops[i];
            const iconNode = document.getElementById('stopNodeIcon-' + i);
            const connector = document.getElementById('stopConnectorLine-' + i);
            const cardBox = document.getElementById('stopCardBox-' + i);
            const titleText = document.getElementById('stopTitleText-' + i);
            const badge = document.getElementById('stopBadge-' + i);
            const etaText = document.getElementById('stopEtaText-' + i);
            const distText = document.getElementById('stopDistanceText-' + i);

            if (i < reachedIdx) {
                // Passed
                if (iconNode) {
                    iconNode.className = 'node-icon flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 border-emerald-500 text-white z-10 shadow-xs';
                    iconNode.innerHTML = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
                }
                if (connector) connector.className = 'connector-line w-0.5 h-12 bg-emerald-500 transition-colors duration-500 my-1';
                if (cardBox) cardBox.className = 'stop-card-box flex-1 rounded-xl p-3 border bg-emerald-50/30 border-emerald-100 dark:bg-emerald-500/5 dark:border-emerald-800/30 opacity-75';
                if (titleText) titleText.className = 'text-xs font-semibold text-gray-500 dark:text-gray-400 line-through';
                if (badge) badge.className = 'hidden';
                if (etaText) {
                    etaText.className = 'text-[11px] font-mono font-semibold text-emerald-600 dark:text-emerald-400';
                    etaText.innerText = 'Passed';
                }
                if (distText) distText.innerText = 'Completed';

            } else if (i === reachedIdx) {
                // Arrived
                const dist = liveStopDistanceKm(gps, stop);
                if (iconNode) {
                    iconNode.className = 'node-icon flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 border-2 border-white text-white z-10 ring-4 ring-emerald-500/30 shadow-md';
                    iconNode.innerHTML = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
                }
                if (connector) connector.className = 'connector-line w-0.5 h-12 bg-emerald-500 transition-colors duration-500 my-1';
                if (cardBox) cardBox.className = 'stop-card-box flex-1 rounded-xl p-3 border bg-emerald-50 border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-800/40 shadow-sm ring-1 ring-emerald-500/20';
                if (titleText) titleText.className = 'text-xs font-bold text-emerald-700 dark:text-emerald-300';
                if (badge) {
                    badge.className = 'inline-flex items-center gap-1 rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-semibold text-white';
                    badge.innerHTML = '<span>Arrived</span>';
                }
                if (etaText) {
                    etaText.className = 'text-[11px] font-mono font-bold text-emerald-600 dark:text-emerald-400';
                    etaText.innerText = 'Arrived';
                }
                if (distText) distText.innerText = dist == null ? 'At stop' : 'At stop (' + dist.toFixed(2) + ' km)';

            } else if (i === reachedIdx + 1 && !(offRoute && reachedIdx < 0)) {
                // Next stop the bus is heading toward
                const dist = liveStopDistanceKm(gps, stop);
                const eta = liveStopEtaMinutes(gps, stop);

                if (iconNode) {
                    iconNode.className = 'node-icon flex h-8 w-8 items-center justify-center rounded-full bg-brand-500 border-2 border-white text-white z-10 ring-4 ring-brand-500/30 animate-bounce shadow-md';
                    iconNode.innerHTML = '<span class="text-xs">🚌</span>';
                }
                if (connector) connector.className = 'connector-line w-0.5 h-12 bg-brand-300 dark:bg-brand-800 transition-colors duration-500 my-1';
                if (cardBox) cardBox.className = 'stop-card-box flex-1 rounded-xl p-3 border bg-brand-50/80 border-brand-300 dark:bg-brand-500/15 dark:border-brand-500/40 shadow-sm ring-1 ring-brand-500/20';
                if (titleText) titleText.className = 'text-xs font-bold text-brand-700 dark:text-brand-300';
                if (badge) {
                    badge.className = 'inline-flex items-center gap-1 rounded-full bg-brand-600 px-2 py-0.5 text-[10px] font-semibold text-white animate-pulse';
                    badge.innerHTML = offRoute ? '<span>Off Route</span>' : '<span>Arriving</span>';
                }
                if (etaText) {
                    etaText.className = 'text-[11px] font-mono font-bold text-brand-600 dark:text-brand-400';
                    etaText.innerText = offRoute ? 'Off Route' : 'Arriving';
                }
                if (distText) distText.innerText = dist == null ? '—' : dist.toFixed(1) + ' km away';

            } else {
                // Upcoming stops with live ETA / distance
                const eta = liveStopEtaMinutes(gps, stop);
                const dist = liveStopDistanceKm(gps, stop);

                if (iconNode) {
                    iconNode.className = 'node-icon flex h-8 w-8 items-center justify-center rounded-full bg-white border-2 border-gray-300 text-gray-400 dark:bg-gray-900 dark:border-gray-700 z-10';
                    iconNode.innerHTML = '<span class="text-xs font-mono font-bold">' + (i + 1) + '</span>';
                }
                if (connector) connector.className = 'connector-line w-0.5 h-12 bg-gray-200 dark:bg-gray-800 transition-colors duration-500 my-1';
                if (cardBox) cardBox.className = 'stop-card-box flex-1 rounded-xl p-3 border bg-gray-50/40 border-gray-100 dark:bg-gray-800/20 dark:border-gray-800/50';
                if (titleText) titleText.className = 'text-xs font-semibold text-gray-900 dark:text-white';
                if (badge) badge.className = 'hidden';
                if (etaText) {
                    etaText.className = 'text-[11px] font-mono font-medium text-gray-600 dark:text-gray-400';
                    etaText.innerText = eta == null ? 'ETA: —' : 'ETA: ' + Math.round(eta) + ' mins';
                }
                if (distText) distText.innerText = dist == null ? '—' : dist.toFixed(1) + ' km away';
            }
        }
    }

    /**
     * Core journey update. Tracks how many stops the bus has genuinely arrived at
     * (monotonic) and drives the progress bar, next-stop card and timeline.
     * Offline / off-route positions never fake stop progression.
     */
    function updateLiveJourneyState(gps) {
        const totalStops = liveJourneyStops.length;
        if (totalStops === 0) return;

        // No usable fix: never fake a stop or progress. If we already rendered a
        // real timeline, keep showing that last-known state; otherwise the
        // neutral markup stays untouched.
        if (!gps || gps.latitude == null || gps.longitude == null) {
            if (journeyReachedIdx === null) {
                renderJourneyNeutral(totalStops);
            }
            setJourneyPill('No Signal', '#6b7280');
            return;
        }

        const speed = parseFloat(gps.speed_kmh) || 0;
        const isOnline = gps.status !== 'offline';
        const isStopped = speed <= STOPPED_SPEED_KPH;

        const { idx: nearestIdx, distance: nearestDist } = liveNearestStop(gps);

        if (nearestIdx === -1) {
            if (journeyReachedIdx === null) {
                renderJourneyNeutral(totalStops);
            }
            setJourneyPill('No Signal', '#6b7280');
            return;
        }

        const offRoute = nearestDist > ON_ROUTE_THRESHOLD_KM;
        const atNearestStop = nearestDist <= ARRIVED_RADIUS_KM;

        // First usable fix: guess how far along the route the bus already is.
        // Never trust an off-route position as evidence of stop progression.
        if (journeyReachedIdx === null) {
            if (!isOnline || offRoute) {
                journeyReachedIdx = -1;
            } else if (atNearestStop && isStopped) {
                journeyReachedIdx = nearestIdx;
            } else if (nearestIdx > 0) {
                journeyReachedIdx = nearestIdx - 1;
            } else {
                journeyReachedIdx = -1;
            }
        } else if (!offRoute && nearestIdx > journeyReachedIdx + 1) {
            // Bus skipped ahead (e.g. it came online mid-route): catch up, but
            // only when it is actually near the route.
            journeyReachedIdx = nearestIdx - 1;
        }

        // Arrival at the target stop: the bus must be near the stop AND stationary.
        const targetIdx = journeyReachedIdx + 1;
        if (targetIdx < totalStops && !offRoute) {
            const target = liveJourneyStops[targetIdx];
            if (target && target.latitude && target.longitude) {
                const distToTarget = liveGpsDistanceKm(gps.latitude, gps.longitude, parseFloat(target.latitude), parseFloat(target.longitude));
                if (isOnline && isStopped && distToTarget <= ARRIVED_RADIUS_KM) {
                    journeyReachedIdx = targetIdx;
                }
            }
        }

        if (journeyReachedIdx >= totalStops - 1) {
            journeyReachedIdx = totalStops - 1; // destination reached
        }
        if (journeyReachedIdx < -1) journeyReachedIdx = -1;

        renderJourneyProgress(gps, journeyReachedIdx, totalStops);
        renderNextStopCard(gps, journeyReachedIdx, totalStops, offRoute);
        renderJourneyTimeline(gps, journeyReachedIdx, totalStops, offRoute);

        if (offRoute && journeyReachedIdx < totalStops - 1) {
            setJourneyPill('Off Route', '#f59e0b');
        }
    }

    async function pollLiveGps() {
        try {
            const response = await fetch(gpsEndpoint, {
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            });
            if (!response.ok) return;
            const data = await response.json();
            updateLiveGps(data);
        } catch (err) {
            console.warn('Live GPS poll failed:', err);
        }
    }

    function startLivePolling() {
        stopLivePolling();
        pollLiveGps();
        gpsPollTimer = setInterval(pollLiveGps, 5000);
    }

    function stopLivePolling() {
        if (gpsPollTimer) {
            clearInterval(gpsPollTimer);
            gpsPollTimer = null;
        }
    }
</script>
@endif