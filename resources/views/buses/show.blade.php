<x-app-layout page="buses">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6 space-y-6">

        <!-- Header Bar -->
        <div class="flex items-center justify-between border-b border-gray-200 pb-5 dark:border-gray-800">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $bus->bus_number }}</h1>
                    @if ($bus->registration_number)
                        <span
                            class="rounded-md bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 font-mono">
                            {{ $bus->registration_number }}
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Vehicle details, driver assignment, and live GPS map position.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('buses.index') }}"
                    class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    Back to Buses
                </a>
                <a href="{{ route('buses.trips', $bus) }}"
                    class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    Trip History
                </a>
                <a href="{{ route('buses.edit', $bus) }}"
                    class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-600">
                    Edit Bus
                </a>
            </div>
        </div>

        @if (session('success'))
            <div
                class="rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400 border border-green-200 dark:border-green-800/40">
                {{ session('success') }}
            </div>
        @endif

        <!-- Vehicle Details & Assignment Cards Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Card 1: Vehicle Specs -->
            <div
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800 mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Vehicle Details</h2>
                    @if ($bus->status === 'Active')
                        <span
                            class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400">Active</span>
                    @elseif ($bus->status === 'Maintenance')
                        <span
                            class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400">Maintenance</span>
                    @else
                        <span
                            class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-500/20 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                    @endif
                </div>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Bus Number</dt>
                        <dd class="font-semibold text-gray-900 dark:text-white">{{ $bus->bus_number }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Registration Plate</dt>
                        <dd class="font-mono font-semibold text-brand-600 dark:text-brand-400">
                            {{ $bus->registration_number }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Make & Model</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            {{ trim(($bus->make ?? '') . ' ' . ($bus->model ?? '')) ?: '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Model Year</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $bus->year ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Seating Capacity</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $bus->capacity }} Passengers</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Fuel Type</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $bus->fuel_type ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">GPS Device ID</dt>
                        <dd class="font-mono text-xs font-semibold text-gray-900 dark:text-white">
                            {{ $bus->gps_device_id ?? 'Not linked' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Insurance Number</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $bus->insurance_number ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Insurance Expiry Date</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            {{ $bus->insurance_expiry_date?->format('M d, Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Last Service Date</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            {{ $bus->last_service_date?->format('M d, Y') ?? '—' }}</dd>
                    </div>
                    @if ($bus->notes)
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Notes</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $bus->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Card 2: Route & Driver Assignment -->
            <div class="space-y-6">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <h2
                        class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                        Route & Driver Assignment</h2>

                    <dl class="space-y-4 text-sm">
                        <div class="flex justify-between items-start gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Assigned
                                Driver{{ count($bus->drivers) > 1 ? 's' : '' }}</dt>
                            <dd class="font-semibold text-gray-900 dark:text-white text-right">
                                @if ($bus->drivers->isNotEmpty())
                                    <div class="flex flex-col items-start gap-1.5">
                                        @foreach ($bus->drivers as $driver)
                                            <a href="{{ route('drivers.show', $driver) }}"
                                                class="inline-flex items-center gap-1.5 text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                {{ $driver->full_name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400">— No driver assigned —</span>
                                @endif
                            </dd>
                        </div>

                        <div class="flex justify-between items-start gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Route{{ $tripRoutes->count() > 1 ? 's' : '' }}
                                (Trip)</dt>
                            <dd class="font-semibold text-gray-900 dark:text-white text-right">
                                @if ($tripRoutes->isNotEmpty())
                                    <div class="flex flex-col items-end gap-1.5">
                                        @foreach ($tripRoutes as $route)
                                            <a href="{{ route('routes.show', $route) }}"
                                                class="inline-flex items-center gap-1.5 text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <circle cx="9" cy="6" r="2.5"
                                                        stroke-width="2" />
                                                    <circle cx="15" cy="18" r="2.5"
                                                        stroke-width="2" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 8.5V15a2.5 2.5 0 003.5 2.3L16 15.5" />
                                                </svg>
                                                {{ $route->name }}
                                                @if ($route->route_code)
                                                    <span
                                                        class="rounded bg-gray-100 px-1.5 py-0.5 text-[11px] font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">{{ $route->route_code }}</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400">— No trip started yet —</span>
                                @endif
                            </dd>
                        </div>

                        <div class="flex justify-between items-center gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">School</dt>
                            <dd class="font-semibold text-gray-900 dark:text-white">
                                {{ $bus->school->name ?? '—' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Live Bus GPS Location Map Card (Single Point API Location) -->
        <div
            class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
            @php
                $lat = $latestLocation['latitude'] ?? 27.7172;
                $lng = $latestLocation['longitude'] ?? 85.324;
                $hasGps = !empty($latestLocation['latitude']) && !empty($latestLocation['longitude']);
                $isOnline = $hasGps && ($latestLocation['status'] ?? 'offline') !== 'offline';
                $speed = (float) ($latestLocation['speed_kmh'] ?? 0);
                $statusLabel = $latestLocation['status_label'] ?? ($hasGps ? 'Online' : 'No GPS Data Yet');
                $lastSignalText = !empty($latestLocation['gps_time'])
                    ? date('M d, Y H:i:s', strtotime($latestLocation['gps_time']))
                    : (!empty($latestLocation['last_updated_at'])
                        ? date('M d, Y H:i:s', strtotime($latestLocation['last_updated_at']))
                        : 'No telemetry recorded');
            @endphp

            <div
                class="mb-5 flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Live GPS Location</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Real-time GPS coordinate telemetry from
                            device API</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Telemetry Stats -->
                    <div
                        class="flex items-center gap-2 rounded-xl bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                        <span>Speed: <strong id="liveSpeedText"
                                class="text-gray-900 dark:text-white font-mono">{{ $speed }}
                                km/h</strong></span>
                        <span class="text-gray-300 dark:text-gray-600">•</span>
                        <span>Coords: <strong id="liveCoordsText"
                                class="text-brand-600 dark:text-brand-400 font-mono">{{ $hasGps ? number_format($lat, 4) . ', ' . number_format($lng, 4) : '—' }}</strong></span>
                    </div>

                    @if ($isOnline)
                        <span id="liveStatusBadge"
                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/40">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            {{ $statusLabel }}
                        </span>
                    @else
                        <span id="liveStatusBadge"
                            class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                            <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                            {{ $hasGps ? 'Offline / Parked' : 'No GPS Data Yet' }}
                        </span>
                    @endif
                </div>
            </div>

            @php
                $busMapRoutes = $busRoutes->filter(
                    fn ($route) => collect($route['stops'])
                        ->filter(fn ($stop) => $stop['latitude'] && $stop['longitude'])
                        ->count() >= 2
                )->values();
                $busPalette = ['#4F46E5', '#0EA5E9', '#D946EF', '#F97316', '#10B981', '#EF4444'];
            @endphp

            @if($busMapRoutes->isNotEmpty())

                <div class="mb-4 flex flex-wrap items-center gap-2">

                    @foreach($busMapRoutes as $index => $route)

                        <button
                            type="button"
                            data-bus-route-id="{{ $route['id'] }}"
                            class="bus-route-chip inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-xs ring-2 ring-offset-2 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:ring-offset-gray-900"
                        >
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $busPalette[$index % count($busPalette)] }}"></span>
                            {{ $route['name'] }}
                        </button>

                    @endforeach

                    <button
                        type="button"
                        id="fitBusRoutesBtn"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <svg class="h-4 w-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                        Fit All Routes
                    </button>

                </div>

            @endif

            <!-- Leaflet Map Container -->
            <div class="relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-900"
                style="min-height: 420px;">
                <div id="busSingleLocationMap" class="h-[420px] w-full z-0"></div>

                <!-- Recenter Button -->
                <div class="absolute top-4 right-4 z-10">
                    <button type="button" onclick="recenterMapOnBus()" title="Recenter Camera on Bus"
                        class="flex items-center gap-2 rounded-xl bg-white/95 px-3 py-2 text-xs font-semibold text-gray-800 shadow-md backdrop-blur-md transition hover:bg-brand-500 hover:text-white dark:bg-gray-900/95 dark:text-gray-200 dark:hover:bg-brand-500 dark:hover:text-white border border-gray-200 dark:border-gray-700">
                        <svg class="h-4 w-4 text-brand-500 dark:text-brand-400 hover:text-white" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <span>Recenter on Bus</span>
                    </button>
                </div>

                <!-- Telemetry Legend Footer Overlay -->
                <div
                    class="absolute bottom-4 left-4 z-10 flex items-center gap-3 rounded-xl bg-white/90 p-3 text-xs shadow-lg backdrop-blur-md dark:bg-gray-900/90 border border-gray-200/80 dark:border-gray-800">
                    <div class="flex items-center gap-1.5 font-medium text-gray-700 dark:text-gray-300">
                        <span
                            class="h-3 w-3 rounded-full {{ $isOnline ? 'bg-emerald-500' : 'bg-amber-500' }} inline-block"></span>
                        Bus #{{ $bus->bus_number }}
                    </div>
                    <div class="h-3 w-px bg-gray-300 dark:bg-gray-700"></div>
                    <div class="text-gray-600 dark:text-gray-400">
                        Last Signal: <strong id="lastSignalText">{{ $lastSignalText }}</strong>
                    </div>
                </div>

                <!-- No GPS Data Overlay -->
                <div id="busNoGpsOverlay"
                    class="absolute inset-0 z-[500] flex flex-col items-center justify-center gap-2 bg-gray-900/95 text-center px-6"
                    style="{{ $hasGps ? 'display:none;' : '' }}">
                    <svg class="h-10 w-10 text-gray-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <p class="text-sm font-semibold text-white">Waiting for GPS data…</p>
                    <p class="text-xs text-gray-400">Location will appear here once the bus reports its position.</p>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let busMap = null;
    let busLocationMarker = null;
    let busPollTimer = null;

    let busLat = {{ $lat }};
    let busLng = {{ $lng }};
    let speedVal = {{ $speed }};
    let lastSignalText = @json($lastSignalText);

    const busNumber = @json($bus->bus_number);
    const driverName = @json($tripDrivers->first()?->full_name ?? '—');
    const routeName = 'Dynamic (via active trip)';
    const gpsEndpoint = @json(route('bus_location.latest', ['bus_id' => $bus->id]));

    let hasLiveBusMarker = {{ $hasGps ? 'true' : 'false' }};
    let busMarkerIcon = null;

    const BUS_ROUTE_PALETTE = @json($busPalette);
    const busRoutesData = @json($busMapRoutes);
    let busRouteLayers = {};

    function busMarkerHtml() {
        return `
            <div id="busLocationMarkerInner" style="display:flex;flex-direction:column;align-items:center;transition:transform 0.3s ease-out;">
                <svg width="44" height="44" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter:drop-shadow(0px 4px 6px rgba(0,0,0,0.4));">
                    <circle cx="32" cy="32" r="30" fill="#4F46E5" fill-opacity="0.25"/>
                    <circle cx="32" cy="32" r="24" fill="#312E81" stroke="#FFFFFF" stroke-width="3"/>
                    <rect x="18" y="20" width="28" height="24" rx="4" fill="#F59E0B"/>
                    <rect x="21" y="23" width="22" height="8" rx="2" fill="#1E293B"/>
                    <circle cx="23" cy="40" r="3" fill="#000000"/>
                    <circle cx="41" cy="40" r="3" fill="#000000"/>
                    <polygon points="32,6 38,18 26,18" fill="#10B981"/>
                </svg>
            </div>
        `;
    }

    function getBusMarkerIcon() {
        if (!busMarkerIcon) {
            busMarkerIcon = L.divIcon({
                className: '',
                html: busMarkerHtml(),
                iconSize: [44, 44],
                iconAnchor: [22, 22],
            });
        }
        return busMarkerIcon;
    }

    function hideNoGpsOverlay() {
        const overlay = document.getElementById('busNoGpsOverlay');
        if (overlay) overlay.style.display = 'none';
    }

    function ensureBusMarker() {
        if (busLocationMarker) return;

        busLocationMarker = L.marker([busLat, busLng], {
            icon: getBusMarkerIcon(),
            zIndexOffset: 1000
        }).addTo(busMap);

        const html = `
            <div style="font-family:inherit;min-width:180px;padding:2px;">
                <div style="font-weight:700;font-size:14px;color:#111827;">🚍 Bus #${busNumber}</div>
                <div style="font-size:12px;color:#4B5563;margin-top:3px;">Route: <strong>${routeName}</strong></div>
                <div style="font-size:12px;color:#4B5563;">Driver: <strong>${driverName}</strong></div>
                <div style="font-size:12px;color:#4B5563;">Speed: <strong>${Math.round(speedVal)} km/h</strong></div>
                <div style="font-size:11px;color:#6B7280;margin-top:4px;">Recorded: ${lastSignalText}</div>
            </div>
        `;

        busLocationMarker.bindTooltip(html, {
            direction: 'top',
            offset: [0, -10],
            opacity: 1,
        });

        hideNoGpsOverlay();
    }

    function formatGpsTime(value) {
        if (!value) return 'No telemetry recorded';
        const d = new Date(value);
        if (isNaN(d.getTime())) return value;
        return d.toLocaleString('en-US', {
            hour12: true
        });
    }

    function updateBusGps(data) {
        if (!data || data.latitude == null || data.longitude == null) return;

        busLat = parseFloat(data.latitude);
        busLng = parseFloat(data.longitude);
        speedVal = parseFloat(data.speed_kmh) || 0;

        const heading = data.course != null ? parseFloat(data.course) : parseFloat((data.marker || {}).heading);
        const course = isNaN(heading) ? 0 : heading;
        const isOnline = data.status !== 'offline';
        const statusLabel = data.status_label || 'Offline';
        const statusColor = data.status_color || '#6b7280';

        if (busLocationMarker) {
            busLocationMarker.setLatLng([busLat, busLng]);
            const inner = document.getElementById('busLocationMarkerInner');
            if (inner) inner.style.transform = 'rotate(' + course + 'deg)';
        } else {
            ensureBusMarker();
        }
        hasLiveBusMarker = true;

        const speedEl = document.getElementById('liveSpeedText');
        if (speedEl) speedEl.innerText = Math.round(speedVal);

        const coordsEl = document.getElementById('liveCoordsText');
        if (coordsEl) coordsEl.innerText = busLat.toFixed(4) + ', ' + busLng.toFixed(4);

        const badge = document.getElementById('liveStatusBadge');
        if (badge) {
            if (isOnline) {
                badge.innerHTML = '<span class="h-2 w-2 rounded-full animate-pulse" style="background-color:' +
                    statusColor + '"></span>' + statusLabel;
                badge.className =
                    'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold border border-emerald-200/50 dark:border-emerald-800/40';
                badge.style.backgroundColor = statusColor + '1a';
                badge.style.color = statusColor;
            } else {
                badge.innerHTML = '<span class="h-2 w-2 rounded-full bg-gray-400"></span>Offline';
                badge.className =
                    'inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700';
                badge.style.backgroundColor = '';
                badge.style.color = '';
            }
        }

        const timeEl = document.getElementById('lastSignalText');
        if (timeEl) {
            lastSignalText = formatGpsTime(data.gps_time || data.last_updated_at);
            timeEl.innerText = lastSignalText;
        }

        if (busLocationMarker) {
            const popupHtml = `
                <div style="font-family:inherit;min-width:180px;padding:2px;">
                    <div style="font-weight:700;font-size:14px;color:#111827;">🚍 Bus #${busNumber}</div>
<div style="font-size:12px;color:#4B5563;margin-top:3px;">Route: <strong>Assigned via active trip</strong></div>
                    <div style="font-size:12px;color:#4B5563;">Driver: <strong>${driverName}</strong></div>
                    <div style="font-size:12px;color:#4B5563;">Speed: <strong>${Math.round(speedVal)} km/h</strong></div>
                    <div style="font-size:11px;color:#6B7280;margin-top:4px;">Recorded: ${lastSignalText}</div>
                </div>
            `;
            busLocationMarker.setTooltipContent(popupHtml);
        }
    }

    async function pollBusGps() {
        try {
            const response = await fetch(gpsEndpoint, {
                headers: {
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            });
            if (!response.ok) return;
            const data = await response.json();
            updateBusGps(data);
        } catch (err) {
            console.warn('Live GPS poll failed:', err);
        }
    }

    function startLivePolling() {
        stopLivePolling();
        pollBusGps();
        busPollTimer = setInterval(pollBusGps, 5000);
    }

    function stopLivePolling() {
        if (busPollTimer) {
            clearInterval(busPollTimer);
            busPollTimer = null;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        busMap = L.map('busSingleLocationMap', {
            preferCanvas: true,
            updateWhenIdle: true
        }).setView([busLat, busLng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(busMap);

        if (hasLiveBusMarker) {
            ensureBusMarker();
        }

        startLivePolling();
        initBusRouteLayers();
    });

    async function initBusRouteLayers() {
        if (busRoutesData.length === 0 || typeof busMap === 'undefined') return;

        let rendered = 0;
        const drawPromises = [];

        busRoutesData.forEach((route, index) => {
            const stops = (route.stops || []).filter(
                s => s.latitude && s.longitude && Number(s.latitude) !== 0 && Number(s.longitude) !== 0
            );

            if (stops.length < 2) return;

            const color = BUS_ROUTE_PALETTE[index % BUS_ROUTE_PALETTE.length];
            const group = L.featureGroup().addTo(busMap);

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

            drawPromises.push(drawBusRoutePath(stops, color, group));

            busRouteLayers[route.id] = { group, color, visible: true };
            rendered++;
        });

        await Promise.all(drawPromises);

        if (rendered > 0) {
            bindBusRouteChips();

            if (busLocationMarker) {
                recenterMapOnBus();
            } else {
                fitBusVisibleRoutes();
            }
        }
    }

    async function drawBusRoutePath(stops, color, group) {
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

    function fitBusVisibleRoutes() {
        if (!busMap) return;

        const bounds = L.latLngBounds([]);

        Object.values(busRouteLayers).forEach(layer => {
            if (layer.visible && layer.group.getLayers().length > 0) {
                bounds.extend(layer.group.getBounds());
            }
        });

        if (bounds.isValid()) {
            busMap.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });
        }
    }

    function focusBusRoute(routeId, btn) {
        const layer = busRouteLayers[routeId];
        if (!layer) return;

        if (!layer.visible) {
            layer.group.addTo(busMap);
            layer.visible = true;
            btn.classList.add('ring-2', 'ring-offset-2');
        }

        if (layer.group.getLayers().length > 0) {
            busMap.flyToBounds(layer.group.getBounds(), {
                padding: [50, 50],
                maxZoom: 16,
                duration: 0.8,
            });
        }
    }

    function bindBusRouteChips() {
        document.querySelectorAll('.bus-route-chip').forEach(btn => {
            btn.addEventListener('click', () => focusBusRoute(btn.dataset.busRouteId, btn));
        });

        const fitAllBtn = document.getElementById('fitBusRoutesBtn');
        if (fitAllBtn) fitAllBtn.addEventListener('click', fitBusVisibleRoutes);
    }

    function recenterMapOnBus() {
        if (busMap && busLocationMarker) {
            busMap.flyTo([busLat, busLng], 16, {
                duration: 0.8
            });
        }
    }
</script>
