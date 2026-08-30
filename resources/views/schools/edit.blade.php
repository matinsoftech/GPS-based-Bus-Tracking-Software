<x-app-layout page="school-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit School</h1>
            <a
                href="{{ route('schools.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            >
                Back to Schools
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route('schools.update', $school) }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">School Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $school->name) }}"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">School Code</label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        value="{{ old('code', $school->code) }}"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $school->email) }}"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="{{ old('phone', $school->phone) }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="principal_name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Principal Name</label>
                    <input
                        type="text"
                        id="principal_name"
                        name="principal_name"
                        value="{{ old('principal_name', $school->principal_name) }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                    @error('principal_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="active" @selected(old('status', $school->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $school->status) === 'inactive')>Inactive</option>
                        <option value="suspended" @selected(old('status', $school->status) === 'suspended')>Suspended</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="latitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Latitude</label>
                    <input
                        type="number"
                        step="any"
                        id="latitude"
                        name="latitude"
                        value="{{ old('latitude', $school->latitude) }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                    @error('latitude')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="longitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Longitude</label>
                    <input
                        type="number"
                        step="any"
                        id="longitude"
                        name="longitude"
                        value="{{ old('longitude', $school->longitude) }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                    @error('longitude')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pick on Map</span>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                onclick="useMyLocationForSchool()"
                                title="Use your current location, then refine by clicking the map"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-600 transition hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-400 dark:hover:bg-brand-500/20"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Use My Location
                            </button>
                            <button
                                type="button"
                                onclick="clearSchoolPicker()"
                                title="Clear the picked location"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Clear
                            </button>
                        </div>
                    </div>
                    <div class="mt-2 flex gap-2">
                        <input
                            type="text"
                            id="schoolSearch"
                            placeholder="Search for a place, e.g. Biratnagar Airport"
                            autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        <button
                            type="button"
                            onclick="searchSchoolLocation()"
                            title="Search for a location on the map"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Search
                        </button>
                    </div>
                    <!-- Leaflet CSS & JS (inline like fleet-map) -->
                    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                    <script>
                        (function () {
                            if (typeof L === 'undefined') return;

                            const latInput = document.getElementById('latitude');
                            const lngInput = document.getElementById('longitude');
                            const statusEl = document.getElementById('schoolPickerStatus');
                            const nameEl = document.getElementById('schoolPickerName');

                            let schoolMap = null;
                            let schoolMarker = null;
                            let reverseGeocodeTimer = null;
                            const reverseGeocodeCache = {};

                            function setStatus(text) {
                                statusEl.textContent = text;
                                statusEl.className = 'font-medium text-gray-500 dark:text-gray-400';
                            }

                            function updateReadout(lat, lng, name) {
                                if (lat === null || lng === null) {
                                    nameEl.classList.add('hidden');
                                    setStatus('Click the map to pick a location');
                                    return;
                                }
                                if (name) {
                                    nameEl.textContent = name;
                                    nameEl.classList.remove('hidden');
                                } else {
                                    nameEl.classList.add('hidden');
                                }
                                statusEl.textContent = 'Selected: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
                                statusEl.className = 'font-mono font-medium text-brand-600 dark:text-brand-400';
                            }

                            function initSchoolMap(initialLat, initialLng) {
                                const container = document.getElementById('schoolMap');
                                if (!container || schoolMap) return;

                                // Create map with proper options matching fleet-map pattern
                                schoolMap = L.map('schoolMap', {
                                    preferCanvas: true,
                                    updateWhenIdle: true,
                                    keepBuffer: 1,
                                    zoomControl: false,
                                    scrollWheelZoom: false,
                                });

                                // Set initial view
                                if (initialLat !== null && initialLng !== null) {
                                    schoolMap.setView([initialLat, initialLng], 15);
                                } else {
                                    schoolMap.setView([0, 0], 2);
                                }

                                // Add OSM tile layer (matching fleet-map)
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    maxZoom: 19,
                                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                }).addTo(schoolMap);

                                // Click handler
                                schoolMap.on('click', function (e) {
                                    placeSchoolMarker(e.latlng.lat, e.latlng.lng);
                                });

                                // Force size recalculation after layout settles
                                setTimeout(function () {
                                    if (schoolMap) schoolMap.invalidateSize();
                                }, 50);
                            }

                            function placeSchoolMarker(lat, lng) {
                                initSchoolMap(null, null);
                                if (!schoolMap) return;

                                if (!schoolMarker) {
                                    schoolMarker = L.marker([lat, lng], { draggable: true }).addTo(schoolMap);
                                    schoolMarker.on('dragend', function () {
                                        const pos = schoolMarker.getLatLng();
                                        syncSchoolInputs(pos.lat, pos.lng);
                                    });
                                } else {
                                    schoolMarker.setLatLng([lat, lng]);
                                }

                                syncSchoolInputs(lat, lng);
                            }

                            function syncSchoolInputs(lat, lng, name) {
                                latInput.value = lat.toFixed(6);
                                lngInput.value = lng.toFixed(6);
                                updateReadout(lat, lng, name);
                                if (!name) reverseGeocodeSchool(lat, lng);
                            }

                            function reverseGeocodeSchool(lat, lng) {
                                const key = lat.toFixed(4) + ',' + lng.toFixed(4);
                                if (reverseGeocodeCache.hasOwnProperty(key)) {
                                    updateReadout(lat, lng, reverseGeocodeCache[key]);
                                    return;
                                }
                                if (reverseGeocodeTimer) clearTimeout(reverseGeocodeTimer);

                                reverseGeocodeTimer = setTimeout(async function () {
                                    try {
                                        const response = await fetch(
                                            'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng + '&zoom=18'
                                        );
                                        const data = await response.json();
                                        const name = data.display_name || null;
                                        reverseGeocodeCache[key] = name;
                                        updateReadout(lat, lng, name);
                                    } catch (err) {
                                        updateReadout(lat, lng, null);
                                    }
                                }, 400);
                            }

                            function syncFromManualInputs() {
                                const lat = parseFloat(latInput.value);
                                const lng = parseFloat(lngInput.value);

                                if (!isNaN(lat) && !isNaN(lng)) {
                                    placeSchoolMarker(lat, lng);
                                } else if (schoolMarker) {
                                    schoolMap.removeLayer(schoolMarker);
                                    schoolMarker = null;
                                    updateReadout(null, null);
                                }
                            }

                            function clearSchoolPicker() {
                                if (schoolMarker) {
                                    schoolMap.removeLayer(schoolMarker);
                                    schoolMarker = null;
                                }
                                latInput.value = '';
                                lngInput.value = '';
                                updateReadout(null, null);
                                if (schoolMap) schoolMap.setView([0, 0], 2);
                            }

                            function useMyLocationForSchool() {
                                initSchoolMap(null, null);
                                if (!schoolMap) {
                                    setStatus('Map unavailable');
                                    return;
                                }
                                if (!navigator.geolocation) {
                                    setStatus('Location unavailable — click the map instead');
                                    return;
                                }

                                setStatus('Locating…');

                                navigator.geolocation.getCurrentPosition(
                                    function (pos) {
                                        placeSchoolMarker(pos.coords.latitude, pos.coords.longitude);
                                        schoolMap.setView([pos.coords.latitude, pos.coords.longitude], 15);
                                    },
                                    function () {
                                        setStatus('Location unavailable — click the map instead');
                                    },
                                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
                                );
                            }

                            async function searchSchoolLocation() {
                                const queryEl = document.getElementById('schoolSearch');
                                const q = (queryEl ? queryEl.value : '').trim();
                                if (!q) return;

                                setStatus('Searching…');

                                try {
                                    const response = await fetch(
                                        'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q)
                                    );
                                    const results = await response.json();

                                    if (results && results.length > 0) {
                                        const lat = parseFloat(results[0].lat);
                                        const lng = parseFloat(results[0].lon);

                                        initSchoolMap(null, null);
                                        if (!schoolMap) return;
                                        // Fix: ensure map recalculates size after lazy initialization
                                        setTimeout(function () {
                                            if (schoolMap) schoolMap.invalidateSize();
                                        }, 50);

                                        placeSchoolMarker(lat, lng, results[0].display_name);
                                        schoolMap.setView([lat, lng], 16);
                                    } else {
                                        setStatus('No results found for "' + q + '"');
                                    }
                                } catch (err) {
                                    setStatus('Search failed — try again or click the map');
                                }
                            }

                            document.getElementById('schoolSearch').addEventListener('keydown', function (e) {
                                if (e.key === 'Enter') {
                                    e.preventDefault();
                                    searchSchoolLocation();
                                }
                            });

                            latInput.addEventListener('input', syncFromManualInputs);
                            lngInput.addEventListener('input', syncFromManualInputs);

                            // Initialize map on DOMContentLoaded (single init, matching fleet-map pattern)
                            document.addEventListener('DOMContentLoaded', function () {
                                const initialLat = latInput.value ? parseFloat(latInput.value) : null;
                                const initialLng = lngInput.value ? parseFloat(lngInput.value) : null;
                                initSchoolMap(initialLat, initialLng);
                            });
                        })();
                    </script>
                    <div id="schoolMap" class="mt-2 h-96 min-h-[384px] w-full overflow-hidden rounded-xl border border-gray-300 bg-gray-100 z-0 dark:border-gray-700"></div>
                    <p id="schoolPickerReadout" class="mt-2 space-y-0.5 text-xs">
                        <span id="schoolPickerName" class="hidden font-medium text-gray-900 dark:text-white"></span>
                        <span id="schoolPickerStatus" class="font-medium text-gray-500 dark:text-gray-400">Click the map to pick a location</span>
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label for="address" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >{{ old('address', $school->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="logo" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Logo</label>
                    @if ($school->logo)
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::url($school->logo) }}"
                            alt="{{ $school->name }} logo"
                            class="mb-3 h-16 w-16 rounded-lg object-cover"
                        >
                    @endif
                    <input
                        type="file"
                        id="logo"
                        name="logo"
                        accept="image/*"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                    @error('logo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
                <a
                    href="{{ route('schools.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    Cancel
                </a>
                <button
                    type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600"
                >
                    Update School
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
    <script>
        (function () {
            if (typeof L === 'undefined') return;

            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const statusEl = document.getElementById('schoolPickerStatus');
            const nameEl = document.getElementById('schoolPickerName');

            let schoolMap = null;
            let schoolMarker = null;
            let reverseGeocodeTimer = null;
            const reverseGeocodeCache = {};

            function setStatus(text) {
                statusEl.textContent = text;
                statusEl.className = 'font-medium text-gray-500 dark:text-gray-400';
            }

            function updateReadout(lat, lng, name) {
                if (lat === null || lng === null) {
                    nameEl.classList.add('hidden');
                    setStatus('Click the map to pick a location');
                    return;
                }
                if (name) {
                    nameEl.textContent = name;
                    nameEl.classList.remove('hidden');
                } else {
                    nameEl.classList.add('hidden');
                }
                statusEl.textContent = 'Selected: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
                statusEl.className = 'font-mono font-medium text-brand-600 dark:text-brand-400';
            }

            function initSchoolMap(initialLat, initialLng) {
                const container = document.getElementById('schoolMap');
                if (!container || schoolMap) return;

                // Create map with proper options matching fleet-map pattern
                schoolMap = L.map('schoolMap', {
                    preferCanvas: true,
                    updateWhenIdle: true,
                    keepBuffer: 1,
                    zoomControl: false,
                    scrollWheelZoom: false,
                });

                // Set initial view
                if (initialLat !== null && initialLng !== null) {
                    schoolMap.setView([initialLat, initialLng], 15);
                } else {
                    schoolMap.setView([0, 0], 2);
                }

                // Add OSM tile layer (matching fleet-map)
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(schoolMap);

                // Click handler
                schoolMap.on('click', function (e) {
                    placeSchoolMarker(e.latlng.lat, e.latlng.lng);
                });

                // Force size recalculation after layout settles
                setTimeout(function () {
                    if (schoolMap) schoolMap.invalidateSize();
                }, 50);
            }

            function placeSchoolMarker(lat, lng) {
                initSchoolMap(null, null);
                if (!schoolMap) return;

                if (!schoolMarker) {
                    schoolMarker = L.marker([lat, lng], { draggable: true }).addTo(schoolMap);
                    schoolMarker.on('dragend', function () {
                        const pos = schoolMarker.getLatLng();
                        syncSchoolInputs(pos.lat, pos.lng);
                    });
                } else {
                    schoolMarker.setLatLng([lat, lng]);
                }

                syncSchoolInputs(lat, lng);
            }

            function syncSchoolInputs(lat, lng, name) {
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                updateReadout(lat, lng, name);
                if (!name) reverseGeocodeSchool(lat, lng);
            }

            function reverseGeocodeSchool(lat, lng) {
                const key = lat.toFixed(4) + ',' + lng.toFixed(4);
                if (reverseGeocodeCache.hasOwnProperty(key)) {
                    updateReadout(lat, lng, reverseGeocodeCache[key]);
                    return;
                }
                if (reverseGeocodeTimer) clearTimeout(reverseGeocodeTimer);

                reverseGeocodeTimer = setTimeout(async function () {
                    try {
                        const response = await fetch(
                            'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng + '&zoom=18'
                        );
                        const data = await response.json();
                        const name = data.display_name || null;
                        reverseGeocodeCache[key] = name;
                        updateReadout(lat, lng, name);
                    } catch (err) {
                        updateReadout(lat, lng, null);
                    }
                }, 400);
            }

            function syncFromManualInputs() {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);

                if (!isNaN(lat) && !isNaN(lng)) {
                    placeSchoolMarker(lat, lng);
                } else if (schoolMarker) {
                    schoolMap.removeLayer(schoolMarker);
                    schoolMarker = null;
                    updateReadout(null, null);
                }
            }

            function clearSchoolPicker() {
                if (schoolMarker) {
                    schoolMap.removeLayer(schoolMarker);
                    schoolMarker = null;
                }
                latInput.value = '';
                lngInput.value = '';
                updateReadout(null, null);
                if (schoolMap) schoolMap.setView([0, 0], 2);
            }

            function useMyLocationForSchool() {
                initSchoolMap(null, null);
                if (!schoolMap) {
                    setStatus('Map unavailable');
                    return;
                }
                if (!navigator.geolocation) {
                    setStatus('Location unavailable — click the map instead');
                    return;
                }

                setStatus('Locating…');

                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        placeSchoolMarker(pos.coords.latitude, pos.coords.longitude);
                        schoolMap.setView([pos.coords.latitude, pos.coords.longitude], 15);
                    },
                    function () {
                        setStatus('Location unavailable — click the map instead');
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
                );
            }

            async function searchSchoolLocation() {
                const queryEl = document.getElementById('schoolSearch');
                const q = (queryEl ? queryEl.value : '').trim();
                if (!q) return;

                setStatus('Searching…');

                try {
                    const response = await fetch(
                        'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q)
                    );
                    const results = await response.json();

                    if (results && results.length > 0) {
                        const lat = parseFloat(results[0].lat);
                        const lng = parseFloat(results[0].lon);

                        initSchoolMap(null, null);
                        if (!schoolMap) return;
                        // Fix: ensure map recalculates size after lazy initialization
                        setTimeout(function () {
                            if (schoolMap) schoolMap.invalidateSize();
                        }, 50);

                        placeSchoolMarker(lat, lng, results[0].display_name);
                        schoolMap.setView([lat, lng], 16);
                    } else {
                        setStatus('No results found for "' + q + '"');
                    }
                } catch (err) {
                    setStatus('Search failed — try again or click the map');
                }
            }

            document.getElementById('schoolSearch').addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchSchoolLocation();
                }
            });

            latInput.addEventListener('input', syncFromManualInputs);
            lngInput.addEventListener('input', syncFromManualInputs);

            // Initialize map on DOMContentLoaded (single init, matching fleet-map pattern)
            document.addEventListener('DOMContentLoaded', function () {
                const initialLat = latInput.value ? parseFloat(latInput.value) : null;
                const initialLng = lngInput.value ? parseFloat(lngInput.value) : null;
                initSchoolMap(initialLat, initialLng);
            });
        })();
    </script>
{{-- @endpush --}}
