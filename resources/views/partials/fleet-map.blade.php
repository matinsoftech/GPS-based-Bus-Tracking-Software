@php
    $fleetBuses = $fleetMap['buses'] ?? [];
    $fleetSummary = $fleetMap['summary'] ?? [];
    $fleetSchool = $fleetMap['school'] ?? null;
    $fleetRoutes = $fleetMap['routes'] ?? [];
    $fleetMapRefreshUrl = $fleetMapRefreshUrl ?? null;
    $fleetMapTitle = $fleetMapTitle ?? 'Fleet Overview Map';
    $fleetMapSubtitle = $fleetMapSubtitle ?? 'Live location of every school bus, route paths, and stops';
    $fleetMapHeight = $fleetMapHeight ?? 'h-[520px]';
    $fleetMapCompact = $fleetMapCompact ?? false;
    $fleetMapShowCards = $fleetMapShowCards ?? true;
    $fleetHereApiKey = config('gps.heremaps_api_key') ?? '';

    $fleetCards = [
        'total' => ['label' => 'Total Buses', 'value' => $fleetSummary['total'] ?? 0, 'classes' => 'text-gray-800 dark:text-white/90'],
        'active' => ['label' => 'Active Buses', 'value' => $fleetSummary['active'] ?? 0, 'classes' => 'text-success-600 dark:text-success-500'],
        'inactive' => ['label' => 'Inactive Buses', 'value' => $fleetSummary['inactive'] ?? 0, 'classes' => 'text-gray-500 dark:text-gray-400'],
        'moving' => ['label' => 'Moving Now', 'value' => $fleetSummary['moving'] ?? 0, 'classes' => 'text-emerald-600 dark:text-emerald-500'],
        'stopped' => ['label' => 'Stopped Now', 'value' => $fleetSummary['stopped'] ?? 0, 'classes' => 'text-amber-600 dark:text-amber-500'],
        'idle' => ['label' => 'Idle Now', 'value' => $fleetSummary['idle'] ?? 0, 'classes' => 'text-yellow-600 dark:text-yellow-500'],
        'offline' => ['label' => 'Offline', 'value' => $fleetSummary['offline'] ?? 0, 'classes' => 'text-gray-500 dark:text-gray-400'],
        'routes_running' => ['label' => 'Routes Running', 'value' => $fleetSummary['routes_running'] ?? 0, 'classes' => 'text-brand-600 dark:text-brand-400'],
    ];
@endphp

<!-- Fleet Overview Summary Cards -->
@if ($fleetMapShowCards)
<div class="mt-6 grid grid-cols-2 gap-4 {{ $fleetMapCompact ? 'sm:grid-cols-3' : 'md:grid-cols-4 xl:grid-cols-8' }}">
    @foreach ($fleetCards as $key => $card)
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $card['label'] }}</span>
            <h4 id="fleetSummary_{{ $key }}" class="mt-2 text-3xl font-bold {{ $card['classes'] }}">{{ $card['value'] }}</h4>
        </div>
    @endforeach
</div>
@endif

<!-- Fleet Overview Map -->
<div class="{{ $fleetMapShowCards ? 'mt-6' : '' }} overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $fleetMapTitle }}</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $fleetMapSubtitle }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" id="fleetMapFitRouteBtn"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="6" cy="19" r="2.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 16.5H13a3.5 3.5 0 000-7H11a3.5 3.5 0 010-7h8.5"/><circle cx="19.5" cy="2.5" r="2.5"/></svg>
                Fit Route
            </button>
            <button type="button" id="fleetMapRecenterBusBtn"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><line x1="2" x2="5" y1="12" y2="12"/><line x1="19" x2="22" y1="12" y2="12"/><line x1="12" x2="12" y1="2" y2="5"/><line x1="12" x2="12" y1="19" y2="22"/><circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="3"/></svg>
                Recenter Bus
            </button>
            <button type="button" id="fleetMapRecenterBtn"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4l6 6M20 4l-6 6M4 20l6-6M20 20l-6-6M4 4h16M4 4v16M20 4v16"/></svg>
                Fit All
            </button>
            <button type="button" id="fleetMapRefreshBtn"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M20 8A8 8 0 005.6 5.6L4 8m16 8l-1.6 2.4A8 8 0 014 16"/></svg>
                Refresh
            </button>
            <div class="relative" id="fleetMapLayersWrap">
                <button type="button" id="fleetMapLayersBtn"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7l8-4 8 4-8 4-8-4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 12l8 4 8-4"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 17l8 4 8-4"/></svg>
                    Map Type
                </button>
                <div id="fleetMapLayersMenu" class="absolute right-0 z-30 mt-2 hidden w-48 rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-900">
                    <button type="button" class="fleetMapLayerItem flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05]" data-map-type="street">
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M3 12h18M12 3c-2.5 2.5-2.5 15 0 18M12 3c2.5 2.5 2.5 15 0 18"/></svg>
                            Street
                        </span>
                        <svg class="fleetMapLayerCheck hidden h-4 w-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </button>
                    <button type="button" class="fleetMapLayerItem flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05]" data-map-type="satellite">
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v6M12 16v6M2 12h6M16 12h6M4.9 4.9l4.2 4.2M14.9 14.9l4.2 4.2M19.1 4.9l-4.2 4.2M9.1 14.9l-4.2 4.2"/></svg>
                            Satellite
                        </span>
                        <svg class="fleetMapLayerCheck hidden h-4 w-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </button>
                    <button type="button" class="fleetMapLayerItem flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05]" data-map-type="hybrid">
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="3" width="15" height="15" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 17l4-6 3 4 2-2 4 5"/><path stroke-linecap="round" d="M14 3v3"/></svg>
                            Hybrid
                        </span>
                        <svg class="fleetMapLayerCheck hidden h-4 w-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </button>
                    <button type="button" class="fleetMapLayerItem flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05]" data-map-type="terrain">
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M3 19l6-9 4 5 3-3 5 7H3z"/></svg>
                            Terrain
                        </span>
                        <svg class="fleetMapLayerCheck hidden h-4 w-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </button>
                    <div class="my-1 h-px bg-gray-200 dark:bg-gray-800"></div>
                    <button type="button" class="fleetMapTrafficItem flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:text-gray-300 dark:hover:bg-white/[0.05]" data-traffic="1" @if(!$fleetHereApiKey) disabled title="Traffic layer requires a HERE Maps API key (HEREMAPS_API_KEY)" @endif>
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><rect x="7" y="3" width="10" height="18" rx="3"/><circle cx="12" cy="7" r="1.5" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="12" cy="17" r="1.5" fill="currentColor" stroke="none"/></svg>
                            Traffic
                        </span>
                        <svg class="fleetMapLayerCheck hidden h-4 w-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </button>
                    <button type="button" class="fleetMapPoiItem flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05]" data-poi="1">
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-5.5-7-11a7 7 0 0114 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                            Points of Interest
                        </span>
                        <svg class="fleetMapLayerCheck hidden h-4 w-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </div>
            </div>
            <span id="fleetMapLastUpdate" class="rounded-full bg-gray-50 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700"></span>
        </div>
    </div>

    <div class="relative overflow-hidden rounded-xl border-t border-gray-200 bg-gray-900 dark:border-gray-800" style="min-height: 520px;">
        <div id="fleetMapCanvas" class="{{ $fleetMapHeight }} w-full z-0"></div>

        <!-- Bus Quick-Nav Strip -->
        <div id="fleetBusStrip" class="absolute left-3 top-3 z-10 hidden max-w-[calc(100%-6rem)] gap-2 overflow-x-auto rounded-xl bg-white/90 p-2 shadow-lg backdrop-blur-md border border-gray-200/80 dark:bg-gray-900/90 dark:border-gray-800 md:flex no-scrollbar"></div>

        <!-- Legend Overlay -->
        <div class="absolute bottom-4 left-4 z-10 hidden flex-wrap items-center gap-3 rounded-xl bg-white/90 p-3 text-xs shadow-lg backdrop-blur-md sm:flex dark:bg-gray-900/90 border border-gray-200/80 dark:border-gray-800">
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
                <span class="inline-block h-3 w-3 rounded-full bg-gray-400"></span> Offline
            </span>
            <span class="h-3 w-px bg-gray-300 dark:bg-gray-700"></span>
            <span class="flex items-center gap-1.5 font-medium text-gray-700 dark:text-gray-300">
                <span class="inline-block h-3 w-3 rounded-full bg-rose-500"></span> School
            </span>
        </div>

        <!-- Zoom Controls -->
        <div class="absolute bottom-4 right-4 z-10 flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900">
            <button type="button" id="fleetMapZoomInBtn" title="Zoom in"
                class="flex h-9 w-9 items-center justify-center text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
            </button>
            <div class="h-px bg-gray-200 dark:bg-gray-800"></div>
            <button type="button" id="fleetMapZoomOutBtn" title="Zoom out"
                class="flex h-9 w-9 items-center justify-center text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" d="M5 12h14"/></svg>
            </button>
        </div>
    </div>
</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .fleet-map-bus-marker {
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease-out;
    }

    .fleet-map-bus-svg {
        width: 44px;
        height: 44px;
        filter: drop-shadow(0px 4px 6px rgba(0, 0, 0, 0.4));
    }

    .fleet-poi-dot {
        width: 14px;
        height: 14px;
        border-radius: 9999px;
        border: 2px solid #ffffff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
    }

    .fleet-poi-tooltip {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        font-size: 10px;
        font-weight: 600;
        color: #111827;
        text-shadow: 0 1px 0 #fff, 0 -1px 0 #fff, 1px 0 0 #fff, -1px 0 0 #fff, 0 0 4px rgba(255, 255, 255, 0.9);
    }

    .fleet-poi-tooltip::before {
        display: none;
    }
</style>

<script>
    (function () {
        const mapEl = document.getElementById('fleetMapCanvas');
        if (!mapEl || typeof L === 'undefined') return;

        const initialPayload = {
            buses: @json($fleetBuses),
            routes: @json($fleetRoutes),
            school: @json($fleetSchool),
            updated_at: @json($fleetMap['updated_at'] ?? null),
        };

        const refreshUrl = @json($fleetMapRefreshUrl);
        const REFRESH_INTERVAL_MS = 30000;

        const STATUS_COLORS = {
            moving: '#059669',
            stopped: '#d97706',
            idle: '#ca8a04',
            inactive: '#6b7280',
            offline: '#6b7280',
        };

        const ROUTE_PALETTE = ['#4F46E5', '#0EA5E9', '#D946EF', '#F97316', '#10B981', '#EF4444'];

        let fleetMap = null;
        const busMarkers = new Map();
        let schoolMarker = null;
        const stopMarkers = [];
        const routeLayers = [];

        const BASE_LAYERS = {
            street: function () {
                return [
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        subdomains: 'abc',
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    }),
                ];
            },
            satellite: function () {
                return [
                    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="https://www.esri.com/">Esri</a>, Imagery &copy; <a href="https://www.maxar.com/">Maxar</a>',
                    }),
                ];
            },
            hybrid: function () {
                return [
                    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="https://www.esri.com/">Esri</a>, Imagery &copy; <a href="https://www.maxar.com/">Maxar</a>',
                    }),
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png', {
                        maxZoom: 19,
                        subdomains: 'abcd',
                    }),
                ];
            },
            terrain: function () {
                return [
                    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 18,
                        attribution: '&copy; <a href="https://www.esri.com/">Esri</a>',
                    }),
                ];
            },
        };

        const hereApiKey = @json($fleetHereApiKey);

        let currentBaseLayers = [];
        let currentMapType = 'street';
        let trafficLayer = null;
        let trafficOn = false;

        function setBaseMap(type) {
            const create = BASE_LAYERS[type];
            if (!create || typeof create !== 'function') return;

            currentBaseLayers.forEach(layer => fleetMap.removeLayer(layer));
            currentBaseLayers = (create() || []).map(layer => {
                layer.addTo(fleetMap);
                return layer;
            });

            currentMapType = type;
            if (trafficLayer && trafficOn) trafficLayer.bringToFront();
            updateLayerChecks(type);
        }

        function toggleTraffic(on) {
            if (!hereApiKey) return;
            trafficOn = on;

            if (on) {
                if (!trafficLayer) {
                    trafficLayer = L.tileLayer(
                        `https://traffic.ls.hereapi.com/traffic/6.1/tile/8/flowtile/{z}/{x}/{y}/256/png8?apiKey=${hereApiKey}`,
                        {
                            maxZoom: 19,
                            opacity: 0.85,
                            attribution: '&copy; <a href="https://www.here.com/">HERE</a>',
                        }
                    );
                }
                trafficLayer.addTo(fleetMap);
            } else if (trafficLayer) {
                fleetMap.removeLayer(trafficLayer);
            }

            updateTrafficCheck(on);
        }

        function updateLayerChecks(type) {
            document.querySelectorAll('.fleetMapLayerItem').forEach(item => {
                const check = item.querySelector('.fleetMapLayerCheck');
                const active = item.dataset.mapType === type;
                item.classList.toggle('bg-gray-50', active);
                item.classList.toggle('dark:bg-white/[0.05]', active);
                if (check) check.classList.toggle('hidden', !active);
            });
        }

        function updateTrafficCheck(on) {
            const item = document.querySelector('.fleetMapTrafficItem');
            if (!item) return;
            const check = item.querySelector('.fleetMapLayerCheck');
            item.classList.toggle('bg-gray-50', on);
            item.classList.toggle('dark:bg-white/[0.05]', on);
            if (check) check.classList.toggle('hidden', !on);
        }

        const POI_MIN_ZOOM = 14;
        const POI_CATEGORIES = [
            { key: 'fuel', label: 'Petrol Pump', color: '#F97316', test: t => t.amenity === 'fuel' },
            { key: 'shop', label: 'Store / Shop', color: '#8B5CF6', test: t => !!t.shop },
            { key: 'food', label: 'Restaurant / Cafe', color: '#EF4444', test: t => ['restaurant', 'fast_food', 'cafe'].includes(t.amenity) },
            { key: 'pharmacy', label: 'Pharmacy', color: '#10B981', test: t => t.amenity === 'pharmacy' },
            { key: 'bank', label: 'Bank / ATM', color: '#3B82F6', test: t => ['bank', 'atm'].includes(t.amenity) },
            { key: 'hotel', label: 'Hotel', color: '#EC4899', test: t => ['hotel', 'guest_house'].includes(t.tourism) },
            { key: 'medical', label: 'Hospital / Clinic', color: '#DC2626', test: t => ['hospital', 'clinic'].includes(t.amenity) },
        ];

        const POI_QUERY = [
            '[out:json][timeout:25]',
            '(',
            'node["amenity"~"^(fuel|restaurant|fast_food|cafe|pharmacy|bank|atm|hospital|clinic)$"]({bbox});',
            'node["shop"]({bbox});',
            'node["tourism"~"^(hotel|guest_house)$"]({bbox});',
            'way["amenity"~"^(fuel|restaurant|fast_food|cafe|pharmacy|bank|atm|hospital|clinic)$"]({bbox});',
            'way["shop"]({bbox});',
            ');',
            'out center tags 80;',
        ].join('\n');

        let poiLayerGroup = null;
        let poiOn = true;
        let poiFetchTimer = null;
        let poiFetchSeq = 0;

        function classifyPoi(tags) {
            for (const cat of POI_CATEGORIES) {
                if (cat.test(tags)) return cat;
            }
            return null;
        }

        function clearPois() {
            if (poiLayerGroup) poiLayerGroup.clearLayers();
        }

        function renderPois(elements) {
            clearPois();

            elements.forEach(el => {
                const lat = el.lat != null ? el.lat : (el.center && el.center.lat);
                const lon = el.lon != null ? el.lon : (el.center && el.center.lon);
                if (lat == null || lon == null) return;

                const tags = el.tags || {};
                const cat = classifyPoi(tags);
                if (!cat) return;

                const name = tags.name ? tags.name : cat.label;

                const icon = L.divIcon({
                    className: '',
                    html: `<div class="fleet-poi-dot" style="background:${cat.color};"></div>`,
                    iconSize: [14, 14],
                    iconAnchor: [7, 7],
                });

                const marker = L.marker([lat, lon], { icon, zIndexOffset: 50 });
                marker.bindTooltip(`<span>${name}</span>`, {
                    direction: 'top',
                    offset: [0, -8],
                    permanent: true,
                    className: 'fleet-poi-tooltip',
                });
                marker.bindPopup(`<b>${name}</b><br><span style="color:${cat.color};font-size:11px;">${cat.label}</span>`);
                marker.addTo(poiLayerGroup);
            });
        }

        async function fetchPois() {
            const seq = ++poiFetchSeq;
            const bounds = fleetMap.getBounds();
            const bbox = [bounds.getSouth(), bounds.getWest(), bounds.getNorth(), bounds.getEast()].join(',');
            const query = POI_QUERY.replace('{bbox}', bbox);

            try {
                const res = await fetch('https://overpass-api.de/api/interpreter', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'data=' + encodeURIComponent(query),
                });
                if (!res.ok) return;
                const data = await res.json();
                if (seq !== poiFetchSeq) return;
                renderPois(data.elements || []);
            } catch (err) {
                // Transient network / rate-limit failures should not break the map.
            }
        }

        function schedulePoiFetch() {
            if (!poiOn || !fleetMap) return;
            clearTimeout(poiFetchTimer);
            poiFetchTimer = setTimeout(() => {
                if (fleetMap.getZoom() >= POI_MIN_ZOOM) {
                    fetchPois();
                } else {
                    clearPois();
                }
            }, 400);
        }

        function togglePois(on) {
            poiOn = on;
            if (on) {
                schedulePoiFetch();
            } else {
                clearTimeout(poiFetchTimer);
                clearPois();
            }
            updatePoiCheck(on);
        }

        function updatePoiCheck(on) {
            const item = document.querySelector('.fleetMapPoiItem');
            if (!item) return;
            const check = item.querySelector('.fleetMapLayerCheck');
            item.classList.toggle('bg-gray-50', on);
            item.classList.toggle('dark:bg-white/[0.05]', on);
            if (check) check.classList.toggle('hidden', !on);
        }

        function busMarkerHtml(color) {
            return `
                <div class="fleet-map-bus-marker">
                    <svg class="fleet-map-bus-svg" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="32" cy="32" r="30" fill="${color}" fill-opacity="0.25"/>
                        <circle cx="32" cy="32" r="24" fill="${color}" stroke="#FFFFFF" stroke-width="3"/>
                        <rect x="18" y="20" width="28" height="24" rx="4" fill="#F59E0B"/>
                        <rect x="21" y="23" width="22" height="8" rx="2" fill="#1E293B"/>
                        <circle cx="23" cy="40" r="3" fill="#000000"/>
                        <circle cx="41" cy="40" r="3" fill="#000000"/>
                        <polygon points="32,6 38,18 26,18" fill="#10B981"/>
                    </svg>
                </div>
            `;
        }

        function busPopupHtml(bus) {
            const statusColor = bus.status_color || STATUS_COLORS[bus.tracking_status] || '#9CA3AF';
            const statusLabel = bus.status_label || bus.tracking_status;
            const speed = Number(bus.speed || 0).toFixed(0);
            const eta = bus.eta_minutes != null ? `${bus.eta_minutes} min` : '—';
            const lastUpdate = bus.recorded_at ? new Date(bus.recorded_at).toLocaleString() : '—';
            const coords = bus.latitude && bus.longitude
                ? `${Number(bus.latitude).toFixed(5)}, ${Number(bus.longitude).toFixed(5)}`
                : '—';

            return `
                <div style="font-family:inherit;min-width:220px;padding:2px;">
                    <div style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:14px;color:#111827;">
                        <span>🚍 Bus #${bus.bus_number}</span>
                        <span style="margin-left:auto;font-size:10px;font-weight:600;color:#fff;background:${statusColor};padding:2px 8px;border-radius:999px;">${statusLabel}</span>
                    </div>
                    <div style="font-size:12px;color:#4B5563;margin-top:6px;">Route: <strong>${bus.route_name || 'Not assigned'}</strong></div>
                    <div style="font-size:12px;color:#4B5563;">Driver: <strong>${bus.driver_name || '—'}</strong></div>
                    <div style="font-size:12px;color:#4B5563;">Speed: <strong>${speed} km/h</strong></div>
                    <div style="font-size:12px;color:#4B5563;">Next Stop: <strong>${bus.next_stop || '—'}</strong></div>
                    <div style="font-size:12px;color:#4B5563;">Est. Arrival: <strong>${eta}</strong></div>
                    <div style="font-size:11px;color:#6B7280;margin-top:4px;">📍 ${coords}</div>
                    <div style="font-size:11px;color:#6B7280;">Last Updated: ${lastUpdate}</div>
                </div>
            `;
        }

        function addSchoolMarker() {
            if (!initialPayload.school || !initialPayload.school.latitude || !initialPayload.school.longitude) return;

            const icon = L.divIcon({
                className: '',
                html: `
                    <div style="display:flex;align-items:center;justify-content:center;">
                        <svg width="34" height="34" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="32" cy="32" r="28" fill="#E11D48" fill-opacity="0.2"/>
                            <circle cx="32" cy="32" r="22" fill="#BE123C" stroke="#FFFFFF" stroke-width="3"/>
                            <path d="M32 14 L40 20 H24 Z" fill="#FFFFFF"/>
                            <rect x="25" y="20" width="14" height="22" rx="2" fill="#FFFFFF"/>
                            <rect x="28" y="24" width="8" height="5" rx="1" fill="#BE123C"/>
                            <rect x="28" y="32" width="8" height="4" rx="1" fill="#BE123C"/>
                        </svg>
                    </div>
                `,
                iconSize: [34, 34],
                iconAnchor: [17, 17],
            });

            schoolMarker = L.marker([initialPayload.school.latitude, initialPayload.school.longitude], {
                icon,
                zIndexOffset: 2000,
            }).addTo(fleetMap).bindPopup(`<b>🏫 ${initialPayload.school.name || 'School'}</b>`);
        }

        /**
         * Fetch the actual road path (via OSRM) between a route's ordered stops.
         * Falls back to null so callers can draw the straight stop-to-stop path.
         */
        async function fetchRoadLatLngs(waypoints) {
            const MAX_WAYPOINTS = 90;
            const latlngs = [];

            for (let i = 0; i < waypoints.length; i += MAX_WAYPOINTS) {
                const chunk = waypoints.slice(i, i + MAX_WAYPOINTS);
                const coordStr = chunk.map(w => `${w[1]},${w[0]}`).join(';');
                const url = `https://router.project-osrm.org/route/v1/driving/${coordStr}?overview=full&geometries=geojson`;

                const response = await fetch(url);
                const data = await response.json();

                if (data.code === 'Ok' && data.routes && data.routes[0] && data.routes[0].geometry) {
                    data.routes[0].geometry.coordinates.forEach(c => latlngs.push([c[1], c[0]]));
                }
            }

            return latlngs.length >= 2 ? latlngs : null;
        }

        async function addRoute(route, index) {
            const color = ROUTE_PALETTE[index % ROUTE_PALETTE.length];
            const stops = (route.stops || []).filter(
                s => s.latitude && s.longitude && Number(s.latitude) !== 0 && Number(s.longitude) !== 0
            );

            if (stops.length < 2) return;

            const waypoints = stops.map(s => [Number(s.latitude), Number(s.longitude)]);

            let latlngs = null;
            try {
                latlngs = await fetchRoadLatLngs(waypoints);
            } catch (err) {
                console.warn('OSRM routing failed, falling back to stop-to-stop path:', err);
            }
            if (!latlngs) latlngs = waypoints;

            const outer = L.polyline(latlngs, {
                color,
                weight: 7,
                opacity: 0.18,
                lineCap: 'round',
            }).addTo(fleetMap);

            const inner = L.polyline(latlngs, {
                color,
                weight: 3,
                opacity: 0.8,
                lineCap: 'round',
            }).addTo(fleetMap);

            routeLayers.push(outer, inner);

            stops.forEach(stop => {
                const stopIcon = L.divIcon({
                    className: '',
                    html: `
                        <div style="background:${color};color:#fff;border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:10px;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3);">${stop.stop_order}</div>
                    `,
                    iconSize: [22, 22],
                    iconAnchor: [11, 11],
                });

                stopMarkers.push(
                    L.marker([Number(stop.latitude), Number(stop.longitude)], { icon: stopIcon, zIndexOffset: 100 })
                        .addTo(fleetMap)
                        .bindTooltip(`<b>Stop ${stop.stop_order}:</b> ${stop.name}`)
                );
            });
        }

        async function addRoutes(routes) {
            const list = routes || [];
            for (let i = 0; i < list.length; i++) {
                await addRoute(list[i], i);
            }
        }

        function upsertBusMarker(bus) {
            if (!bus.latitude || !bus.longitude) return;

            const color = bus.status_color || STATUS_COLORS[bus.tracking_status] || '#9CA3AF';
            const latlng = [Number(bus.latitude), Number(bus.longitude)];
            let marker = busMarkers.get(bus.id);

            if (!marker) {
                const icon = L.divIcon({
                    className: '',
                    html: busMarkerHtml(color),
                    iconSize: [44, 44],
                    iconAnchor: [22, 22],
                });

                marker = L.marker(latlng, { icon, zIndexOffset: 1000 }).addTo(fleetMap);
                marker.busColor = color;
                marker.bindPopup(busPopupHtml(bus));
                marker.on('click', function () {
                    focusBus(bus.id);
                });
                busMarkers.set(bus.id, marker);
            } else {
                marker.setLatLng(latlng);

                if (marker.busColor !== color) {
                    marker.busColor = color;
                    marker.setIcon(L.divIcon({
                        className: '',
                        html: busMarkerHtml(color),
                        iconSize: [44, 44],
                        iconAnchor: [22, 22],
                    }));
                }

                marker.setPopupContent(busPopupHtml(bus));
            }
        }

        function renderBusStrip(buses) {
            const strip = document.getElementById('fleetBusStrip');
            if (!strip) return;

            strip.innerHTML = '';
            if (!buses.length) {
                strip.classList.add('hidden');
                return;
            }

            strip.classList.remove('hidden');
            buses.forEach(bus => {
                if (!bus.latitude || !bus.longitude) return;
                const color = bus.status_color || STATUS_COLORS[bus.tracking_status] || '#9CA3AF';
                const speed = Number(bus.speed || 0).toFixed(0);
                const chip = document.createElement('button');
                chip.type = 'button';
                chip.dataset.busId = bus.id;
                chip.className = 'flex shrink-0 items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700';
                chip.innerHTML = `
                    <span class="h-2 w-2 rounded-full" style="background-color:${color}"></span>
                    <span class="font-semibold">${bus.bus_number || 'Bus ' + bus.id}</span>
                    <span class="text-gray-400 dark:text-gray-500">${speed} km/h</span>
                `;
                chip.addEventListener('click', function () {
                    focusBus(bus.id);
                });
                strip.appendChild(chip);
            });
        }

        function focusBus(busId) {
            const marker = busMarkers.get(busId);
            if (!marker) return;
            const latlng = marker.getLatLng();
            fleetMap.flyTo([latlng.lat, latlng.lng], 15, { duration: 0.8 });
            marker.openPopup();
        }

        window.fleetMapFocusBus = focusBus;

        function renderFleet(payload) {
            const buses = payload.buses || [];
            const seen = new Set();

            buses.forEach(bus => {
                seen.add(bus.id);
                upsertBusMarker(bus);
            });

            for (const [id, marker] of busMarkers) {
                if (!seen.has(id)) {
                    fleetMap.removeLayer(marker);
                    busMarkers.delete(id);
                }
            }

            renderBusStrip(buses);

            const summary = payload.summary || {};
            const summaryEls = {
                total: 'fleetSummary_total',
                active: 'fleetSummary_active',
                inactive: 'fleetSummary_inactive',
                moving: 'fleetSummary_moving',
                stopped: 'fleetSummary_stopped',
                idle: 'fleetSummary_idle',
                offline: 'fleetSummary_offline',
                routes_running: 'fleetSummary_routes_running',
            };

            for (const [key, elId] of Object.entries(summaryEls)) {
                const el = document.getElementById(elId);
                if (el && summary[key] !== undefined) el.textContent = summary[key];
            }

            const lastEl = document.getElementById('fleetMapLastUpdate');
            if (lastEl) {
                lastEl.textContent = payload.updated_at ? `Updated ${timeAgo(payload.updated_at)}` : '';
            }
        }

        function timeAgo(dateStr) {
            if (!dateStr) return '—';
            const then = new Date(dateStr).getTime();
            if (isNaN(then)) return dateStr;

            const seconds = Math.floor((Date.now() - then) / 1000);
            if (seconds < 10) return 'Just now';
            if (seconds < 60) return `${seconds} seconds ago`;

            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return `${minutes} minute${minutes === 1 ? '' : 's'} ago`;

            const hours = Math.floor(minutes / 60);
            if (hours < 24) return `${hours} hour${hours === 1 ? '' : 's'} ago`;

            const days = Math.floor(hours / 24);
            return `${days} day${days === 1 ? '' : 's'} ago`;
        }

        function fitAllBounds() {
            const group = L.featureGroup([...busMarkers.values()]);

            if (schoolMarker) group.addLayer(schoolMarker);
            stopMarkers.forEach(m => group.addLayer(m));
            routeLayers.forEach(l => group.addLayer(l));

            if (group.getLayers().length > 0) {
                fleetMap.fitBounds(group.getBounds(), { padding: [50, 50], maxZoom: 14 });
            }
        }

        function fitRouteBounds() {
            if (routeLayers.length === 0) {
                fitAllBounds();
                return;
            }

            fleetMap.fitBounds(
                L.featureGroup(routeLayers).getBounds(),
                { padding: [50, 50], maxZoom: 15 }
            );
        }

        function recenterBus() {
            const marker = busMarkers.values().next().value;
            if (!marker) {
                fitAllBounds();
                return;
            }

            const latlng = marker.getLatLng();
            fleetMap.flyTo([latlng.lat, latlng.lng], 15, { duration: 0.8 });
            marker.openPopup();
        }

        window.fleetMapFitRoute = fitRouteBounds;
        window.fleetMapRecenterBus = recenterBus;

        document.addEventListener('DOMContentLoaded', async function () {
            fleetMap = L.map(mapEl, {
                preferCanvas: true,
                updateWhenIdle: true,
                keepBuffer: 1,
                zoomAnimation: true,
                fadeAnimation: false,
                markerZoomAnimation: false,
                worldCopyJump: true,
                zoomControl: false,
            }).setView([27.7172, 85.3240], 12);

            // Exposed so other scripts (e.g. the bus-location table row clicks)
            // can recenter the camera on a specific bus.
            window.fleetMapInstance = fleetMap;

            setBaseMap('street');

            poiLayerGroup = L.layerGroup().addTo(fleetMap);

            addSchoolMarker();
            renderFleet(initialPayload);
            fitAllBounds();
            await addRoutes(initialPayload.routes);

            const recenterBtn = document.getElementById('fleetMapRecenterBtn');
            if (recenterBtn) recenterBtn.addEventListener('click', fitAllBounds);

            const fitRouteBtn = document.getElementById('fleetMapFitRouteBtn');
            if (fitRouteBtn) fitRouteBtn.addEventListener('click', fitRouteBounds);

            const recenterBusBtn = document.getElementById('fleetMapRecenterBusBtn');
            if (recenterBusBtn) recenterBusBtn.addEventListener('click', recenterBus);

            const refreshBtn = document.getElementById('fleetMapRefreshBtn');
            if (refreshBtn && refreshUrl) {
                refreshBtn.addEventListener('click', async function () {
                    if (refreshBtn.disabled) return;
                    refreshBtn.disabled = true;
                    try {
                        const res = await fetch(refreshUrl, {
                            headers: { 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        });
                        if (!res.ok) return;
                        renderFleet(await res.json());
                    } catch (err) {
                        // Transient network failures should not break the dashboard.
                    } finally {
                        refreshBtn.disabled = false;
                    }
                });
            }

            const layersWrap = document.getElementById('fleetMapLayersWrap');
            const layersBtn = document.getElementById('fleetMapLayersBtn');
            const layersMenu = document.getElementById('fleetMapLayersMenu');

            function closeLayersMenu() {
                if (layersMenu) layersMenu.classList.add('hidden');
            }

            if (layersBtn && layersMenu) {
                layersBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    layersMenu.classList.toggle('hidden');
                });
            }

            document.addEventListener('click', function (e) {
                if (layersWrap && !layersWrap.contains(e.target)) closeLayersMenu();
            });

            document.querySelectorAll('.fleetMapLayerItem').forEach(item => {
                item.addEventListener('click', function () {
                    const type = item.dataset.mapType;
                    if (!type) return;
                    setBaseMap(type);
                    closeLayersMenu();
                });
            });

            const trafficItem = document.querySelector('.fleetMapTrafficItem');
            if (trafficItem) {
                trafficItem.addEventListener('click', function () {
                    if (!hereApiKey) return;
                    toggleTraffic(!trafficOn);
                });
            }

            const poiItem = document.querySelector('.fleetMapPoiItem');
            if (poiItem) {
                poiItem.addEventListener('click', function () {
                    togglePois(!poiOn);
                });
            }

            const zoomInBtn = document.getElementById('fleetMapZoomInBtn');
            const zoomOutBtn = document.getElementById('fleetMapZoomOutBtn');

            function updateZoomButtons() {
                const z = fleetMap.getZoom();
                if (zoomInBtn) zoomInBtn.disabled = z >= fleetMap.getMaxZoom();
                if (zoomOutBtn) zoomOutBtn.disabled = z <= fleetMap.getMinZoom();
            }

            if (zoomInBtn) zoomInBtn.addEventListener('click', function () { fleetMap.zoomIn(); });
            if (zoomOutBtn) zoomOutBtn.addEventListener('click', function () { fleetMap.zoomOut(); });

            fleetMap.on('zoomend', updateZoomButtons);
            updateZoomButtons();

            fleetMap.on('moveend zoomend', schedulePoiFetch);
            updatePoiCheck(true);
            schedulePoiFetch();

            let refreshing = false;
            if (refreshUrl) {
                setInterval(async () => {
                    if (refreshing) return;
                    refreshing = true;
                    try {
                        const res = await fetch(refreshUrl, {
                            headers: { 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        });
                        if (!res.ok) return;
                        renderFleet(await res.json());
                    } catch (err) {
                        // Transient network failures should not break the dashboard.
                    } finally {
                        refreshing = false;
                    }
                }, REFRESH_INTERVAL_MS);
            }
        });
    })();
</script>
