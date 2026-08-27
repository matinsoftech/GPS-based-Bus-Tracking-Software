<x-app-layout page="vehicle-tracking">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6 space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Vehicle Tracking</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Live location of all NazarTrack vehicles. Matched with system buses where available.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span id="vehicleLastUpdate" class="rounded-full bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                    Loading...
                </span>
                <button onclick="refreshData()" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M20 8A8 8 0 005.6 5.6L4 8m16 8l-1.6 2.4A8 8 0 014 16"/></svg>
                    Refresh
                </button>
            </div>
        </div>

        {{-- Status Summary Cards --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4" id="statusSummary">
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Moving</span>
                </div>
                <p id="countMoving" class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">0</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-yellow-400"></span>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Idle</span>
                </div>
                <p id="countIdle" class="mt-2 text-2xl font-bold text-yellow-500 dark:text-yellow-400">0</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Stopped</span>
                </div>
                <p id="countStopped" class="mt-2 text-2xl font-bold text-amber-500 dark:text-amber-400">0</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-gray-400"></span>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Inactive</span>
                </div>
                <p id="countInactive" class="mt-2 text-2xl font-bold text-gray-500 dark:text-gray-400">0</p>
            </div>
        </div>

        {{-- Main Content: Vehicle List (Left) + Map (Right) --}}
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

            {{-- Left Column: Vehicle List --}}
            <div class="xl:col-span-4">
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
                    {{-- Search --}}
                    <div class="border-b border-gray-200 dark:border-gray-800 p-4">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" id="vehicleSearch" placeholder="Search vehicle name or bus number..."
                                class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-4 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    {{-- Vehicle List --}}
                    <div id="vehicleList" class="max-h-[520px] overflow-y-auto">
                        {{-- Populated by JS --}}
                    </div>

                    <div id="vehicleListEmpty" class="hidden p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        No vehicles found.
                    </div>
                </div>
            </div>

            {{-- Right Column: Map --}}
            <div class="xl:col-span-8">
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 px-5 py-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Fleet Map</h2>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Live GPS positions of all tracked vehicles</p>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="flex items-center gap-1.5 font-medium text-gray-700 dark:text-gray-300">
                                <span class="inline-block h-3 w-3 rounded-full bg-emerald-500"></span> Moving
                            </span>
                            <span class="flex items-center gap-1.5 font-medium text-gray-700 dark:text-gray-300">
                                <span class="inline-block h-3 w-3 rounded-full bg-amber-500"></span> Stopped
                            </span>
                            <span class="flex items-center gap-1.5 font-medium text-gray-700 dark:text-gray-300">
                                <span class="inline-block h-3 w-3 rounded-full bg-yellow-400"></span> Idle
                            </span>
                            <span class="flex items-center gap-1.5 font-medium text-gray-700 dark:text-gray-300">
                                <span class="inline-block h-3 w-3 rounded-full bg-gray-400"></span> Inactive
                            </span>
                        </div>
                    </div>
                    <div id="vehicleMap" class="w-full" style="height: 560px;"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .vehicle-row {
            transition: background-color 0.15s ease;
        }
        .vehicle-row:hover {
            background-color: rgba(99, 102, 241, 0.04);
        }
        .vehicle-row.active {
            background-color: rgba(99, 102, 241, 0.08);
            border-left: 3px solid #6366f1;
        }
        .vehicle-popup .leaflet-popup-content-wrapper {
            border-radius: 12px;
            padding: 0;
        }
        .vehicle-popup .leaflet-popup-content {
            margin: 0;
            min-width: 220px;
        }
    </style>

    <script>
        const initialVehicles = @json($vehicles);
        const dataUrl = @json(route('vehicle-tracking.data'));
        const REFRESH_INTERVAL = 30000;

        let allVehicles = initialVehicles;
        let map, markerLayer = {};
        let refreshTimer = null;

        const STATUS_COLORS = {
            moving: '#22c55e',
            stopped: '#f59e0b',
            idle: '#eab308',
            inactive: '#6b7280',
            offline: '#6b7280',
        };

        document.addEventListener('DOMContentLoaded', function () {
            initMap();
            renderVehicleList(allVehicles);
            updateStatusCounts(allVehicles);
            startAutoRefresh();

            document.getElementById('vehicleSearch').addEventListener('input', function (e) {
                const query = e.target.value.toLowerCase();
                const filtered = allVehicles.filter(v =>
                    (v.asset_name || '').toLowerCase().includes(query) ||
                    (v.bus_number || '').toLowerCase().includes(query) ||
                    (v.plate_number || '').toLowerCase().includes(query)
                );
                renderVehicleList(filtered);
            });
        });

        function initMap() {
            map = L.map('vehicleMap', { zoomControl: false }).setView([28.6139, 77.2090], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            L.control.zoom({ position: 'bottomright' }).addTo(map);

            addMarkersToMap(allVehicles);

            if (allVehicles.length > 0) {
                const withCoords = allVehicles.filter(v => v.latitude && v.longitude);
                if (withCoords.length > 0) {
                    const bounds = L.latLngBounds(withCoords.map(v => [v.latitude, v.longitude]));
                    map.fitBounds(bounds, { padding: [40, 40] });
                }
            }
        }

        function createMarkerIcon(color) {
            return L.divIcon({
                className: '',
                html: `<div style="
                    width: 14px; height: 14px;
                    background: ${color};
                    border-radius: 50%;
                    border: 2.5px solid white;
                    box-shadow: 0 1px 4px rgba(0,0,0,0.3);
                "></div>`,
                iconSize: [14, 14],
                iconAnchor: [7, 7],
            });
        }

        function addMarkersToMap(vehicles) {
            Object.values(markerLayer).forEach(m => map.removeLayer(m));
            markerLayer = {};

            vehicles.forEach(v => {
                if (!v.latitude || !v.longitude) return;

                const color = STATUS_COLORS[v.status] || '#6b7280';
                const icon = createMarkerIcon(color);
                const marker = L.marker([v.latitude, v.longitude], { icon }).addTo(map);

                marker.bindPopup(buildPopupHtml(v), { className: 'vehicle-popup', maxWidth: 280 });

                markerLayer[v.asset_id] = marker;
            });
        }

        function buildPopupHtml(v) {
            const busInfo = v.bus_number
                ? `<div style="font-size:12px;color:#6b7280;">Bus: <strong style="color:#111827;">${v.bus_number}</strong>${v.bus_registration ? ' (' + v.bus_registration + ')' : ''}</div>`
                : `<div style="font-size:12px;color:#6b7280;">Bus: <em>No assigned bus</em></div>`;

            const schoolInfo = v.school_name
                ? `<div style="font-size:12px;color:#6b7280;">School: ${v.school_name}</div>`
                : '';

            const driverInfo = v.matched_driver_name
                ? `<div style="font-size:12px;color:#6b7280;">Driver: ${v.matched_driver_name}</div>`
                : (v.driver_name
                    ? `<div style="font-size:12px;color:#6b7280;">Driver: ${v.driver_name}${v.driver_phone ? ' (' + v.driver_phone + ')' : ''}</div>`
                    : '');

            return `
                <div style="padding:12px 14px;">
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:${STATUS_COLORS[v.status] || '#6b7280'};display:inline-block;"></span>
                        <strong style="font-size:14px;color:#111827;">${v.asset_name}</strong>
                        ${v.plate_number ? '<span style="font-size:11px;color:#6b7280;margin-left:auto;">' + v.plate_number + '</span>' : ''}
                    </div>
                    ${busInfo}
                    ${schoolInfo}
                    ${driverInfo}
                    <div style="margin-top:6px;padding-top:6px;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;font-size:11px;color:#6b7280;">
                        <span>${Math.round(v.speed_kmh)} km/h</span>
                        <span style="font-weight:600;color:${STATUS_COLORS[v.status] || '#6b7280'};">${v.status_label}</span>
                    </div>
                    ${v.last_updated_ago ? '<div style="font-size:10px;color:#9ca3af;margin-top:4px;">Updated: ' + v.last_updated_ago + '</div>' : ''}
                </div>
            `;
        }

        function renderVehicleList(vehicles) {
            const list = document.getElementById('vehicleList');
            const empty = document.getElementById('vehicleListEmpty');

            if (vehicles.length === 0) {
                list.innerHTML = '';
                empty.classList.remove('hidden');
                return;
            }

            empty.classList.add('hidden');

            list.innerHTML = vehicles.map(v => {
                const color = STATUS_COLORS[v.status] || '#6b7280';
                const busLabel = v.bus_number
                    ? `Bus #${v.bus_number}`
                    : '<span style="color:#9ca3af;font-style:italic;">No assigned bus</span>';

                return `
                    <div class="vehicle-row cursor-pointer border-b border-gray-100 dark:border-gray-800/60 px-4 py-3"
                         data-asset-id="${v.asset_id}"
                         onclick="focusVehicle(${v.asset_id})">
                        <div class="flex items-start gap-3">
                            <span style="width:10px;height:10px;border-radius:50%;background:${color};margin-top:4px;flex-shrink:0;"></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white truncate">${v.asset_name}</span>
                                    <span class="text-xs font-medium ml-2 flex-shrink-0" style="color:${color};">${v.status_label}</span>
                                </div>
                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">${busLabel}</div>
                                <div class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">${Math.round(v.speed_kmh)} km/h${v.last_updated_ago ? ' · ' + v.last_updated_ago : ''}</div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function updateStatusCounts(vehicles) {
            const moving = vehicles.filter(v => v.status === 'moving').length;
            const idle = vehicles.filter(v => v.status === 'idle').length;
            const stopped = vehicles.filter(v => v.status === 'stopped').length;
            const inactive = vehicles.filter(v => ['inactive', 'offline'].includes(v.status)).length;

            document.getElementById('countMoving').textContent = moving;
            document.getElementById('countIdle').textContent = idle;
            document.getElementById('countStopped').textContent = stopped;
            document.getElementById('countInactive').textContent = inactive;
        }

        function focusVehicle(assetId) {
            const vehicle = allVehicles.find(v => v.asset_id === assetId);
            if (!vehicle || !vehicle.latitude || !vehicle.longitude) return;

            map.setView([vehicle.latitude, vehicle.longitude], 15);

            const marker = markerLayer[assetId];
            if (marker) {
                marker.openPopup();
            }

            document.querySelectorAll('.vehicle-row').forEach(el => el.classList.remove('active'));
            const row = document.querySelector(`.vehicle-row[data-asset-id="${assetId}"]`);
            if (row) {
                row.classList.add('active');
                row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        async function refreshData() {
            try {
                const response = await fetch(dataUrl, {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store',
                });
                if (!response.ok) return;
                const json = await response.json();

                allVehicles = json.vehicles || [];

                const searchQuery = document.getElementById('vehicleSearch').value.toLowerCase();
                const filtered = searchQuery
                    ? allVehicles.filter(v =>
                        (v.asset_name || '').toLowerCase().includes(searchQuery) ||
                        (v.bus_number || '').toLowerCase().includes(searchQuery) ||
                        (v.plate_number || '').toLowerCase().includes(searchQuery)
                    )
                    : allVehicles;

                renderVehicleList(filtered);
                updateStatusCounts(allVehicles);
                addMarkersToMap(allVehicles);

                document.getElementById('vehicleLastUpdate').textContent =
                    'Updated: ' + new Date().toLocaleTimeString();
            } catch (err) {
                console.warn('Vehicle tracking refresh failed:', err);
            }
        }

        function startAutoRefresh() {
            if (refreshTimer) clearInterval(refreshTimer);
            refreshTimer = setInterval(refreshData, REFRESH_INTERVAL);
        }
    </script>
</x-app-layout>
