<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <!-- Header -->
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Route Preview & Live Bus Tracking</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Road-following navigation path, stop markers, and real-time telemetry</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Telemetry Pill -->
            <div id="routeTelemetryPill" class="hidden sm:flex items-center gap-2 rounded-xl bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                <span class="inline-block h-2 w-2 rounded-full bg-gray-400"></span>
                <span>Speed: <strong id="routeBusSpeed" class="text-gray-900 dark:text-white font-mono">—</strong></span>
                <span class="text-gray-300 dark:text-gray-600">•</span>
                <span>Next: <strong id="routeNextStop" class="text-brand-600 dark:text-brand-400">Loading...</strong></span>
            </div>

            <span id="routeStatusBadge" class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                Waiting for GPS
            </span>
        </div>
    </div>

    <!-- Map Canvas Container with Floating Action Overlay -->
    <div class="relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-900" style="min-height: 420px;">

        <!-- Map Element -->
        <div id="routeMapCanvas" class="h-[440px] w-full z-0"></div>

        <!-- Floating Map Action Controls -->
        <div class="absolute top-4 right-4 z-10 flex flex-col gap-2">
            <!-- Recenter on Bus Button -->
            <button
                type="button"
                id="routeRecenterBtn"
                onclick="recenterCameraOnBus()"
                title="Recenter Camera on Bus"
                class="hidden items-center gap-2 rounded-xl bg-white/95 px-3 py-2 text-xs font-semibold text-gray-800 shadow-md backdrop-blur-md transition hover:bg-brand-500 hover:text-white dark:bg-gray-900/95 dark:text-gray-200 dark:hover:bg-brand-500 dark:hover:text-white border border-gray-200 dark:border-gray-700"
            >
                <svg class="h-4 w-4 text-brand-500 dark:text-brand-400 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <span>Recenter on Bus</span>
            </button>

            <!-- Fit Full Route Button -->
            <button
                type="button"
                onclick="fitFullRouteBounds()"
                title="Fit Route to Screen"
                class="flex items-center gap-2 rounded-xl bg-white/95 px-3 py-2 text-xs font-semibold text-gray-800 shadow-md backdrop-blur-md transition hover:bg-gray-100 dark:bg-gray-900/95 dark:text-gray-200 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-700"
            >
                <svg class="h-4 w-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
                <span>Fit Full Route</span>
            </button>
        </div>

        <!-- Info Banner Overlay -->
        <div id="routeMapBanner" class="hidden absolute top-4 left-4 right-4 z-20 sm:left-auto sm:max-w-md">
            <div id="routeMapBannerInner" class="flex items-center gap-2 rounded-xl bg-gray-900/90 px-4 py-3 text-xs font-medium text-gray-200 shadow-lg backdrop-blur-md border border-gray-700/60">
                <span class="h-2 w-2 rounded-full bg-gray-400 shrink-0"></span>
                <span id="routeMapBannerText">Banner text</span>
            </div>
        </div>

        <!-- Floating Route Details Legend -->
        <div class="absolute bottom-4 left-4 z-10 hidden sm:flex items-center gap-3 rounded-xl bg-white/90 p-3 text-xs shadow-lg backdrop-blur-md dark:bg-gray-900/90 border border-gray-200/80 dark:border-gray-800">
            <div class="flex items-center gap-1.5 font-medium text-gray-700 dark:text-gray-300">
                <span class="h-3 w-3 rounded-full bg-emerald-500 inline-block"></span>
                Start: <strong class="text-gray-900 dark:text-white">{{ $route->start_location }}</strong>
            </div>
            <div class="h-3 w-px bg-gray-300 dark:bg-gray-700"></div>
            <div class="flex items-center gap-1.5 font-medium text-gray-700 dark:text-gray-300">
                <span class="h-3 w-3 rounded-full bg-brand-500 inline-block"></span>
                Stops: <strong id="routeLegendStops" class="text-gray-900 dark:text-white">0</strong>
            </div>
            <div class="h-3 w-px bg-gray-300 dark:bg-gray-700"></div>
            <div class="flex items-center gap-1.5 font-medium text-gray-700 dark:text-gray-300">
                <span class="h-3 w-3 rounded-full bg-rose-500 inline-block"></span>
                End: <strong class="text-gray-900 dark:text-white">{{ $route->end_location }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    /* Custom Bus Marker Container */
    .bus-marker-container {
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s ease-out;
    }

    .bus-marker-svg {
        width: 36px;
        height: 36px;
        filter: drop-shadow(0px 4px 6px rgba(0, 0, 0, 0.4));
    }

    /* Stop Marker Pin Styling */
    .stop-marker-pin {
        background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%);
        color: white;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 11px;
        border: 2px solid #FFFFFF;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
    }
</style>

<script>
    let routeMap = null;
    let routePolyline = null;
    let routePolylineBounds = null;
    let busLocationMarker = null;
    let busMarkerIcon = null;
    let busPollTimer = null;

    const routeStopsData = @json($route->stops);

    const routeBusData = @json($activeBus ? ['id' => $activeBus->id, 'bus_number' => $activeBus->bus_number, 'name' => $activeBus->bus_number] : null);
    const routeDriverName = @json($activeDriver?->full_name ?? null);
    const gpsEndpoint = @json($activeBus ? route('bus_location.latest', ['bus_id' => $activeBus->id]) : null);

    let hasActiveTrip = @json($activeBus ? true : false);
    let busLat = null;
    let busLng = null;
    let speedVal = 0;
    let currentStatus = null;
    let currentStatusLabel = 'Offline';
    let currentStatusColor = '#6b7280';
    let hasLiveFix = false;
    let autoFollowCamera = true;
    let isPollingBusGps = false;

    function busMarkerHtml() {
        return `
            <div id="routeBusMarkerInner" class="bus-marker-container">
                <svg class="bus-marker-svg" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="32" r="30" fill="#4F46E5" fill-opacity="0.25" />
                    <circle cx="32" cy="32" r="24" fill="#312E81" stroke="#FFFFFF" stroke-width="3" />
                    <rect x="18" y="20" width="28" height="24" rx="4" fill="#F59E0B" />
                    <rect x="21" y="23" width="22" height="8" rx="2" fill="#1E293B" />
                    <circle cx="23" cy="40" r="3" fill="#000000" />
                    <circle cx="41" cy="40" r="3" fill="#000000" />
                    <polygon points="32,6 38,18 26,18" fill="#10B981" />
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

    function formatGpsTime(value) {
        if (!value) return 'No telemetry recorded';
        const d = new Date(value);
        if (isNaN(d.getTime())) return value;
        return d.toLocaleString('en-US', { hour12: true });
    }

    function showBanner(text, isWarning) {
        const banner = document.getElementById('routeMapBanner');
        const inner = document.getElementById('routeMapBannerInner');
        if (!banner || !inner) return;
        inner.className = 'flex items-center gap-2 rounded-xl px-4 py-3 text-xs font-medium shadow-lg backdrop-blur-md border ' +
            (isWarning
                ? 'bg-amber-900/90 text-amber-200 border-amber-700/60'
                : 'bg-gray-900/90 text-gray-200 border-gray-700/60');
        document.getElementById('routeMapBannerText').innerText = text;
        banner.classList.remove('hidden');
    }

    function hideBanner() {
        const banner = document.getElementById('routeMapBanner');
        if (banner) banner.classList.add('hidden');
    }

    function updateStatusBadge(status, label, color) {
        const badge = document.getElementById('routeStatusBadge');
        if (!badge) return;

        const isOnline = status && status !== 'offline';
        currentStatus = status;
        currentStatusLabel = label || 'Offline';
        currentStatusColor = color || (isOnline ? '#22c55e' : '#6b7280');

        badge.innerHTML = '<span class="h-2 w-2 rounded-full' + (isOnline ? ' animate-pulse' : '') + '" style="background-color:' + currentStatusColor + '"></span>' + currentStatusLabel;
        badge.className = 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold border ' +
            (isOnline
                ? 'border-emerald-200/50 dark:border-emerald-800/40'
                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border-gray-200 dark:border-gray-700');
        badge.style.backgroundColor = isOnline ? currentStatusColor + '1a' : '';
        badge.style.color = isOnline ? currentStatusColor : '';
    }

    function ensureBusMarker() {
        if (busLocationMarker) return;

        busLocationMarker = L.marker([busLat, busLng], {
            icon: getBusMarkerIcon(),
            zIndexOffset: 1000
        }).addTo(routeMap);

        const busNumber = routeBusData ? routeBusData.bus_number : '';
        const html = `
            <div style="font-family:inherit;min-width:180px;padding:2px;">
                <div style="font-weight:700;font-size:14px;color:#111827;">🚍 Bus #${busNumber}</div>
                <div style="font-size:12px;color:#4B5563;margin-top:3px;">Route: <strong>{{ $route->name }}</strong></div>
                <div style="font-size:12px;color:#4B5563;">Driver: <strong>${routeDriverName || '—'}</strong></div>
                <div style="font-size:12px;color:#4B5563;">Speed: <strong>${Math.round(speedVal)} km/h</strong></div>
                <div style="font-size:11px;color:#6B7280;margin-top:4px;">Recorded: ${formatGpsTime(lastSignalTextValue())}</div>
            </div>
        `;

        busLocationMarker.bindTooltip(html, {
            direction: 'top',
            offset: [0, -10],
            opacity: 1,
        });

        document.getElementById('routeRecenterBtn').classList.remove('hidden');
    }

    function lastSignalTextValue() {
        return null;
    }

    function recenterCameraOnBus() {
        if (busLocationMarker && routeMap) {
            autoFollowCamera = true;
            routeMap.panTo(busLocationMarker.getLatLng(), { animate: true, duration: 0.3 });
        }
    }

    function fitFullRouteBounds() {
        if (!routeMap) return;

        if (routePolylineBounds) {
            routeMap.fitBounds(routePolylineBounds, { padding: [50, 50], animate: true, duration: 0.8 });
        } else if (stopBounds) {
            routeMap.fitBounds(stopBounds, { padding: [50, 50], animate: true, duration: 0.8 });
        }
    }

    let stopBounds = null;

    function applyGpsPayload(data) {
        if (!data || data.latitude == null || data.longitude == null) return false;

        busLat = parseFloat(data.latitude);
        busLng = parseFloat(data.longitude);
        speedVal = parseFloat(data.speed_kmh) || 0;

        const heading = data.course != null ? parseFloat(data.course) : parseFloat((data.marker || {}).heading || 0);
        const course = isNaN(heading) ? 0 : heading;

        if (busLocationMarker) {
            busLocationMarker.setLatLng([busLat, busLng]);
            const inner = document.getElementById('routeBusMarkerInner');
            if (inner) inner.style.transform = 'rotate(' + course + 'deg)';
            if (autoFollowCamera && routeMap) {
                routeMap.panTo([busLat, busLng], { animate: true, duration: 0.3 });
            }
        } else {
            ensureBusMarker();
        }

        hasLiveFix = true;
        hideBanner();

        updateStatusBadge(data.status, data.status_label, data.status_color);

        const speedEl = document.getElementById('routeBusSpeed');
        if (speedEl) speedEl.innerText = Math.round(speedVal) + ' km/h';

        const nextEl = document.getElementById('routeNextStop');
        if (nextEl && data.next_stop && data.next_stop.name) nextEl.innerText = data.next_stop.name;

        const busNumber = routeBusData ? routeBusData.bus_number : '';
        const popupHtml = `
            <div style="font-family:inherit;min-width:180px;padding:2px;">
                <div style="font-weight:700;font-size:14px;color:#111827;">🚍 Bus #${busNumber}</div>
                <div style="font-size:12px;color:#4B5563;margin-top:3px;">Route: <strong>{{ $route->name }}</strong></div>
                <div style="font-size:12px;color:#4B5563;">Driver: <strong>${routeDriverName || '—'}</strong></div>
                <div style="font-size:12px;color:#4B5563;">Speed: <strong>${Math.round(speedVal)} km/h</strong></div>
                <div style="font-size:11px;color:#6B7280;margin-top:4px;">Recorded: ${formatGpsTime(data.gps_time || data.last_updated_at)}</div>
            </div>
        `;
        if (busLocationMarker) busLocationMarker.setTooltipContent(popupHtml);

        return true;
    }

    async function pollRouteBusGps() {
        if (!gpsEndpoint || isPollingBusGps) return;
        isPollingBusGps = true;

        try {
            const response = await fetch(gpsEndpoint, {
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            });
            if (!response.ok) return;
            const data = await response.json();
            applyGpsPayload(data);
        } catch (err) {
            console.warn('Live GPS poll failed:', err);
        } finally {
            isPollingBusGps = false;
        }
    }

    function startBusPolling() {
        if (!gpsEndpoint || busPollTimer) return;
        pollRouteBusGps();
        busPollTimer = setInterval(pollRouteBusGps, 5000);
    }

    function stopBusPolling() {
        if (busPollTimer) {
            clearInterval(busPollTimer);
            busPollTimer = null;
        }
    }

    async function drawRoutePolyline(stopPoints) {
        const waypoints = stopPoints.map(s => [Number(s.latitude), Number(s.longitude)]);
        let latlngs = null;

        try {
            const coordStr = waypoints.map(ll => `${ll[1]},${ll[0]}`).join(';');
            const url = `https://router.project-osrm.org/route/v1/driving/${coordStr}?overview=full&geometries=geojson`;
            const response = await fetch(url);
            const data = await response.json();

            if (data.code === 'Ok' && data.routes && data.routes[0] && data.routes[0].geometry) {
                latlngs = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
            }
        } catch (err) {
            console.warn('OSRM routing request failed, falling back to direct waypoints:', err);
        }

        if (!latlngs) {
            latlngs = [];
            for (let i = 0; i < waypoints.length - 1; i++) {
                const p1 = waypoints[i];
                const p2 = waypoints[i + 1];
                const steps = 15;
                for (let s = 0; s < steps; s++) {
                    const r = s / steps;
                    latlngs.push([p1[0] + (p2[0] - p1[0]) * r, p1[1] + (p2[1] - p1[1]) * r]);
                }
            }
            latlngs.push(waypoints[waypoints.length - 1]);
        }

        if (latlngs.length === 0) return;

        L.polyline(latlngs, {
            color: '#6366F1',
            weight: 8,
            opacity: 0.35,
            lineCap: 'round'
        }).addTo(routeMap);

        routePolyline = L.polyline(latlngs, {
            color: '#4F46E5',
            weight: 4,
            opacity: 0.9,
            lineCap: 'round'
        }).addTo(routeMap);

        routePolylineBounds = routePolyline.getBounds();
        routeMap.fitBounds(routePolylineBounds, { padding: [50, 50], maxZoom: 16 });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!document.getElementById('routeMapCanvas')) return;

        routeMap = L.map('routeMapCanvas', {
            preferCanvas: true,
            updateWhenIdle: true
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(routeMap);

        const stopPoints = routeStopsData.filter(
            s => s.latitude && s.longitude && Number(s.latitude) !== 0 && Number(s.longitude) !== 0
        );

        document.getElementById('routeLegendStops').innerText = stopPoints.length;

        if (stopPoints.length === 1) {
            routeMap.setView([Number(stopPoints[0].latitude), Number(stopPoints[0].longitude)], 15);
        } else if (stopPoints.length >= 2) {
            stopBounds = L.latLngBounds(stopPoints.map(s => [Number(s.latitude), Number(s.longitude)]));
            routeMap.fitBounds(stopBounds, { padding: [50, 50], maxZoom: 16 });
        }

        if (stopPoints.length === 0) {
            document.getElementById('routeLegendStops').innerText = '0';
            showBanner('No stops with GPS coordinates configured yet.', false);
        }

        stopPoints.forEach(stop => {
            const icon = L.divIcon({
                className: 'custom-stop-pin',
                html: `<div class="stop-marker-pin">${stop.stop_order}</div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });

            L.marker([Number(stop.latitude), Number(stop.longitude)], { icon })
                .addTo(routeMap)
                .bindPopup(`<b>Stop #${stop.stop_order}: ${stop.name}</b>`);
        });

        if (stopPoints.length >= 2) {
            drawRoutePolyline(stopPoints);
        }

        if (!hasActiveTrip) {
            updateStatusBadge(null, 'No Active Trip', '#6b7280');
            showBanner('No active trip — no bus is running on this route right now.', false);
            return;
        }

        const initial = @json($latestLocation ?? null);
        if (initial && initial.latitude != null && initial.longitude != null) {
            applyGpsPayload(initial);
        } else {
            updateStatusBadge(null, 'Waiting for GPS', '#6b7280');
            showBanner('Waiting for a live GPS signal from the bus device...', false);
        }

        startBusPolling();

        routeMap.on('movestart dragstart zoomstart touchstart', function (e) {
            if (e.originalEvent) autoFollowCamera = false;
        });

        window.addEventListener('beforeunload', stopBusPolling);
    });
</script>