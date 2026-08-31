<x-app-layout page="vehicle-tracking">
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

        {{-- Main Content: Vehicle List (Left) + Map (Right) --}}
        <div class="flex flex-col lg:flex-row gap-6 items-start">

            {{-- Left Column: Vehicle List --}}
            <div class="w-full lg:w-1/5">
                <div class="flex flex-col rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden" style="height: 645px;">
                    {{-- Search --}}
                    <div class="border-b border-gray-200 dark:border-gray-800 p-3">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" id="vehicleSearch" placeholder="Search vehicle name or bus..."
                                class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-4 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    {{-- Status Filter Pills --}}
                    <div class="flex flex-wrap gap-1.5 border-b border-gray-200 dark:border-gray-800 px-3 py-2.5">
                        <button type="button" onclick="setFilter('all')" data-filter="all"
                            class="filter-pill active-all inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-semibold transition-colors"
                            style="border-color:#6366f1; background:rgba(99,102,241,0.08); color:#6366f1;">
                            All <span id="pillCountAll" class="ml-0.5 opacity-70">0</span>
                        </button>
                        <button type="button" onclick="setFilter('moving')" data-filter="moving"
                            class="filter-pill inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-semibold transition-colors"
                            style="border-color:#d1fae5; background:transparent; color:#059669;">
                            <span class="inline-block h-1.5 w-1.5 rounded-full" style="background:#059669;"></span>
                            Moving <span id="pillCountMoving" class="ml-0.5 opacity-70">0</span>
                        </button>
                        <button type="button" onclick="setFilter('stopped')" data-filter="stopped"
                            class="filter-pill inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-semibold transition-colors"
                            style="border-color:#fde68a; background:transparent; color:#d97706;">
                            <span class="inline-block h-1.5 w-1.5 rounded-full" style="background:#d97706;"></span>
                            Stopped <span id="pillCountStopped" class="ml-0.5 opacity-70">0</span>
                        </button>
                        <button type="button" onclick="setFilter('idle')" data-filter="idle"
                            class="filter-pill inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-semibold transition-colors"
                            style="border-color:#fef08a; background:transparent; color:#ca8a04;">
                            <span class="inline-block h-1.5 w-1.5 rounded-full" style="background:#ca8a04;"></span>
                            Idle <span id="pillCountIdle" class="ml-0.5 opacity-70">0</span>
                        </button>
                        <button type="button" onclick="setFilter('inactive')" data-filter="inactive"
                            class="filter-pill inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-semibold transition-colors"
                            style="border-color:#e5e7eb; background:transparent; color:#6b7280;">
                            <span class="inline-block h-1.5 w-1.5 rounded-full" style="background:#6b7280;"></span>
                            Inactive <span id="pillCountInactive" class="ml-0.5 opacity-70">0</span>
                        </button>
                    </div>

                    {{-- Vehicle List --}}
                    <div id="vehicleList" class="flex-1 overflow-y-auto no-scrollbar">
                        {{-- Populated by JS --}}
                    </div>

                    <div id="vehicleListEmpty" class="hidden flex-1 p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        No vehicles found.
                    </div>
                </div>
            </div>

            {{-- Right Column: Map --}}
            <div class="w-full lg:w-4/5 relative">
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

                {{-- Vehicle Detail Panel (Overlay on map) --}}
                <div id="detailPanel" class="hidden absolute bottom-4 right-4 z-[1000] w-[340px] max-h-[520px] overflow-y-auto rounded-2xl border border-gray-200 bg-white/95 shadow-2xl backdrop-blur-md dark:border-gray-700 dark:bg-gray-900/95">
                    <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 dark:border-gray-700 bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm px-5 py-3 rounded-t-2xl">
                        <h3 id="detailTitle" class="text-sm font-bold text-gray-900 dark:text-white truncate">Vehicle Details</h3>
                        <button onclick="closeDetailPanel()" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div id="detailContent" class="p-5 space-y-4">
                        {{-- Populated by JS --}}
                    </div>
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
        .filter-pill {
            cursor: pointer;
        }
        .filter-pill:hover {
            opacity: 0.85;
        }
        .filter-pill.pill-active {
            opacity: 1;
            box-shadow: 0 0 0 1.5px currentColor;
        }
        .detail-field {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .dark .detail-field {
            border-bottom-color: #374151;
        }
        .detail-field:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }
        .detail-value {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            text-align: right;
        }
        .dark .detail-value {
            color: #f3f4f6;
        }
    </style>

    {{-- Leaflet CSS & JS (loaded before inline script, matching fleet-map pattern) --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const initialVehicles = @json($vehicles);
        const dataUrl = @json(route('vehicle-tracking.data'));
        const REFRESH_INTERVAL = 30000;

        let allVehicles = initialVehicles;
        let map, markerLayer = {};
        let refreshTimer = null;
        let activeFilter = 'all';
        let activeDetailId = null;

        const STATUS_COLORS = {
            moving: '#059669',
            stopped: '#d97706',
            idle: '#ca8a04',
            inactive: '#6b7280',
            offline: '#6b7280',
        };

        const STATUS_BG = {
            moving: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            stopped: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            idle: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
            inactive: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
            offline: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
        };

        document.addEventListener('DOMContentLoaded', function () {
            initMap();
            updateFilterPillCounts();
            applyFilters();
            startAutoRefresh();

            document.getElementById('vehicleSearch').addEventListener('input', function () {
                applyFilters();
            });
        });

        function initMap() {
            if (typeof L === 'undefined') {
                console.error('Leaflet not loaded');
                return;
            }

            map = L.map('vehicleMap', { zoomControl: false }).setView([28.6139, 77.2090], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            L.control.zoom({ position: 'bottomright' }).addTo(map);

            map.invalidateSize();

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
            if (!map) return;

            Object.values(markerLayer).forEach(m => map.removeLayer(m));
            markerLayer = {};

            vehicles.forEach(v => {
                if (!v.latitude || !v.longitude) return;

                const color = STATUS_COLORS[v.status] || '#6b7280';
                const icon = createMarkerIcon(color);
                const marker = L.marker([v.latitude, v.longitude], { icon }).addTo(map);

                marker.bindPopup(buildPopupHtml(v), { className: 'vehicle-popup', maxWidth: 280 });

                if (v.asset_id != null) {
                    markerLayer[v.asset_id] = marker;
                }
            });
        }

        function buildPopupHtml(v) {
            const color = STATUS_COLORS[v.status] || '#6b7280';

            return `
                <div style="padding:6px 10px;font-family:system-ui,-apple-system,sans-serif;">
                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:${color};margin-right:6px;vertical-align:middle;"></span>
                    <strong style="font-size:13px;color:#111827;white-space:nowrap;">${v.asset_name}</strong>
                </div>
            `;
        }

        function setFilter(status) {
            activeFilter = status;
            updateFilterPillStyles();
            applyFilters();
        }

        function updateFilterPillStyles() {
            const pills = document.querySelectorAll('.filter-pill');
            pills.forEach(pill => {
                const filter = pill.dataset.filter;
                const isActive = filter === activeFilter;
                pill.classList.toggle('pill-active', isActive);

                if (isActive) {
                    const colors = {
                        all: { border: '#6366f1', bg: 'rgba(99,102,241,0.08)', text: '#6366f1' },
                        moving: { border: '#059669', bg: 'rgba(5,150,105,0.08)', text: '#059669' },
                        stopped: { border: '#d97706', bg: 'rgba(217,119,6,0.08)', text: '#d97706' },
                        idle: { border: '#ca8a04', bg: 'rgba(202,138,4,0.08)', text: '#ca8a04' },
                        inactive: { border: '#6b7280', bg: 'rgba(107,114,128,0.08)', text: '#6b7280' },
                    };
                    const c = colors[filter] || colors.all;
                    pill.style.borderColor = c.border;
                    pill.style.background = c.bg;
                    pill.style.color = c.text;
                } else {
                    pill.style.borderColor = '#e5e7eb';
                    pill.style.background = 'transparent';
                    pill.style.color = '#6b7280';
                }
            });
        }

        function applyFilters() {
            const query = (document.getElementById('vehicleSearch').value || '').toLowerCase();

            const filtered = allVehicles.filter(v => {
                const matchesStatus = activeFilter === 'all' || v.status === activeFilter;
                const matchesSearch = !query ||
                    (v.asset_name || '').toLowerCase().includes(query) ||
                    (v.bus_number || '').toLowerCase().includes(query) ||
                    (v.plate_number || '').toLowerCase().includes(query);
                return matchesStatus && matchesSearch;
            });

            renderVehicleList(filtered);
        }

        function updateFilterPillCounts() {
            const counts = { all: allVehicles.length, moving: 0, stopped: 0, idle: 0, inactive: 0 };
            allVehicles.forEach(v => {
                const s = v.status;
                if (counts[s] !== undefined) counts[s]++;
                else counts.inactive++;
            });

            document.getElementById('pillCountAll').textContent = counts.all;
            document.getElementById('pillCountMoving').textContent = counts.moving;
            document.getElementById('pillCountStopped').textContent = counts.stopped;
            document.getElementById('pillCountIdle').textContent = counts.idle;
            document.getElementById('pillCountInactive').textContent = counts.inactive;
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
                const bgCls = STATUS_BG[v.status] || STATUS_BG.inactive;
                const busLabel = v.bus_number
                    ? `Bus #${v.bus_number}`
                    : '<span style="color:#9ca3af;font-style:italic;">No assigned bus</span>';

                return `
                    <div class="vehicle-row cursor-pointer border-b border-gray-100 dark:border-gray-800/60 px-3 py-2.5"
                         data-asset-id="${v.asset_id}"
                         onclick="selectVehicle(${v.asset_id})">
                        <div class="flex items-start gap-2.5">
                            <span style="width:9px;height:9px;border-radius:50%;background:${color};margin-top:5px;flex-shrink:0;"></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[13px] font-semibold text-gray-900 dark:text-white truncate">${v.asset_name}</span>
                                    <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-bold flex-shrink-0 ${bgCls}">${v.status_label}</span>
                                </div>
                                <div class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400 truncate">${busLabel}</div>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">
                                    <span>${Math.round(v.speed_kmh)} km/h</span>
                                    ${v.last_updated_ago ? '<span class="opacity-60">&middot;</span><span>' + v.last_updated_ago + '</span>' : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function selectVehicle(assetId) {
            const vehicle = allVehicles.find(v => v.asset_id === assetId);
            if (!vehicle) return;

            activeDetailId = assetId;

            document.querySelectorAll('.vehicle-row').forEach(el => el.classList.remove('active'));
            const row = document.querySelector(`.vehicle-row[data-asset-id="${assetId}"]`);
            if (row) {
                row.classList.add('active');
                row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            animatedZoomToVehicle(assetId, 16, () => {
                const marker = markerLayer[assetId];
                if (marker) marker.openPopup();
            });

            openDetailPanel(vehicle);
        }

        let zoomAnimating = false;

        function animatedZoomToVehicle(assetId, targetZoom, onDone) {
            if (zoomAnimating) return;

            const vehicle = allVehicles.find(v => v.asset_id === assetId);
            if (!vehicle || !vehicle.latitude || !vehicle.longitude || !map) return;

            zoomAnimating = true;
            const lat = vehicle.latitude;
            const lng = vehicle.longitude;
            const minZoom = 8;

            map.flyTo([lat, lng], minZoom, {
                duration: 0.45,
                easeLinearity: 0.2,
            });

            setTimeout(() => {
                map.flyTo([lat, lng], targetZoom == null ? 16 : targetZoom, {
                    duration: 0.9,
                    easeLinearity: 0.25,
                });
                zoomAnimating = false;
                if (onDone) setTimeout(onDone, 550);
            }, 500);
        }

        function openDetailPanel(v) {
            const panel = document.getElementById('detailPanel');
            const title = document.getElementById('detailTitle');
            const content = document.getElementById('detailContent');

            const color = STATUS_COLORS[v.status] || '#6b7280';
            const bgCls = STATUS_BG[v.status] || STATUS_BG.inactive;

            title.textContent = v.asset_name;

            const fields = [
                { label: 'Status', value: `<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-bold ${bgCls}">${v.status_label}</span>` },
                { label: 'Speed', value: Math.round(v.speed_kmh) + ' km/h' },
                v.plate_number ? { label: 'Plate No.', value: v.plate_number } : null,
                v.bus_number ? { label: 'Bus Number', value: '#' + v.bus_number } : null,
                v.bus_registration ? { label: 'Bus Reg.', value: v.bus_registration } : null,
                v.school_name ? { label: 'School', value: v.school_name } : null,
                v.matched_driver_name ? { label: 'Driver', value: v.matched_driver_name } : null,
                (!v.matched_driver_name && v.driver_name) ? { label: 'Driver (API)', value: v.driver_name } : null,
                v.driver_phone ? { label: 'Driver Phone', value: v.driver_phone } : null,
                v.imei ? { label: 'IMEI', value: v.imei } : null,
                v.latitude && v.longitude ? { label: 'Coordinates', value: Number(v.latitude).toFixed(5) + ', ' + Number(v.longitude).toFixed(5) } : null,
                v.gps_time ? { label: 'GPS Time', value: v.gps_time } : null,
                v.last_updated_ago ? { label: 'Last Updated', value: v.last_updated_ago } : null,
            ].filter(Boolean);

            content.innerHTML = `
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4" style="border-left:3px solid ${color};">
                    ${fields.map(f => `
                        <div class="detail-field">
                            <span class="detail-label">${f.label}</span>
                            <span class="detail-value">${f.value}</span>
                        </div>
                    `).join('')}
                </div>
                ${v.latitude && v.longitude ? `
                    <button onclick="centerOnVehicle(${v.asset_id})" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        Center on Map
                    </button>
                ` : ''}
            `;

            panel.classList.remove('hidden');
        }

        function closeDetailPanel() {
            document.getElementById('detailPanel').classList.add('hidden');
            activeDetailId = null;
        }

        function centerOnVehicle(assetId) {
            const vehicle = allVehicles.find(v => v.asset_id === assetId);
            if (!vehicle) return;

            animatedZoomToVehicle(assetId, 17, () => {
                const marker = markerLayer[assetId];
                if (marker) marker.openPopup();
            });
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

                updateFilterPillCounts();
                applyFilters();
                addMarkersToMap(allVehicles);

                if (activeDetailId != null) {
                    const updated = allVehicles.find(v => v.asset_id === activeDetailId);
                    if (updated) openDetailPanel(updated);
                    else closeDetailPanel();
                }

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
