<!-- Add / Edit Route Stop Modal -->
<div id="stopModal" class="fixed inset-0 z-[100000] hidden overflow-y-auto scrollbar-none bg-gray-900/60 backdrop-blur-xs transition-opacity" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-screen items-center justify-center p-4 pt-16 text-center sm:p-0 sm:pt-16">
        <div class="relative w-full max-w-4xl max-h-[90vh] transform overflow-y-auto scrollbar-none rounded-2xl bg-white p-6 text-left shadow-2xl transition-all dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
            <!-- Modal Header -->
            <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="modalTitle">
                    Add Route Stop
                </h3>
                <button
                    type="button"
                    onclick="closeStopModal()"
                    class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form id="stopForm" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" id="stopFormMethod" value="POST">

                <div class="space-y-4">
                    <!-- Stop Name -->
                    <div>
                        <label for="stop_name" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider dark:text-gray-300">
                            Stop Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="stop_name"
                            required
                            placeholder="e.g. Central Square / Green Park"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>

                    <!-- Stop Order & Status -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="stop_order" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider dark:text-gray-300">
                                Stop Sequence (#) <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                name="stop_order"
                                id="stop_order"
                                min="1"
                                required
                                value="{{ ($route->stops->max('stop_order') ?? 0) + 1 }}"
                                class="mt-1 w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                        </div>

                        <div>
                            <label for="stop_is_active" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider dark:text-gray-300">
                                Status
                            </label>
                            <select
                                name="is_active"
                                id="stop_is_active"
                                class="mt-1 w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Pickup & Drop Times -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="stop_pickup_time" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider dark:text-gray-300">
                                Pickup Time
                            </label>
                            <input type="hidden" name="pickup_time" id="stop_pickup_time">
                            <div class="relative mt-1">
                                <div class="flex items-center gap-1.5">
                                    <button
                                        type="button"
                                        onclick="adjustPickupTime(-5)"
                                        title="5 minutes earlier"
                                        class="shrink-0 rounded-xl border border-gray-300 px-2.5 py-2 text-xs font-bold text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                    >
                                        −5
                                    </button>
                                    <button
                                        type="button"
                                        id="stop_pickup_time_display"
                                        onclick="togglePickupTimeDropdown()"
                                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    >
                                        <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span id="stop_pickup_time_text" class="font-mono">--:--</span>
                                        <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        onclick="adjustPickupTime(5)"
                                        title="5 minutes later"
                                        class="shrink-0 rounded-xl border border-gray-300 px-2.5 py-2 text-xs font-bold text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                    >
                                        +5
                                    </button>
                                </div>
                                <div id="stop_pickup_time_dropdown" class="absolute left-0 right-0 z-20 mt-1 hidden max-h-56 overflow-y-auto rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-700 dark:bg-gray-800"></div>
                            </div>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                Auto-filled from the previous stop — you can change it manually.
                            </p>
                        </div>

                        <div>
                            <label for="stop_drop_time" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider dark:text-gray-300">
                                Arrival / Drop Time
                            </label>
                            <input type="hidden" name="drop_time" id="stop_drop_time">
                            <div class="relative mt-1">
                                <div class="flex items-center gap-1.5">
                                    <button
                                        type="button"
                                        onclick="adjustDropTime(-5)"
                                        title="5 minutes earlier"
                                        class="shrink-0 rounded-xl border border-gray-300 px-2.5 py-2 text-xs font-bold text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                    >
                                        −5
                                    </button>
                                    <button
                                        type="button"
                                        id="stop_drop_time_display"
                                        onclick="toggleDropTimeDropdown()"
                                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    >
                                        <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span id="stop_drop_time_text" class="font-mono">--:--</span>
                                        <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        onclick="adjustDropTime(5)"
                                        title="5 minutes later"
                                        class="shrink-0 rounded-xl border border-gray-300 px-2.5 py-2 text-xs font-bold text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                    >
                                        +5
                                    </button>
                                </div>
                                <div id="stop_drop_time_dropdown" class="absolute left-0 right-0 z-20 mt-1 hidden max-h-56 overflow-y-auto rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-700 dark:bg-gray-800"></div>
                            </div>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                You can pick a time manually.
                            </p>
                        </div>
                    </div>

                    <!-- Optional Coordinates -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="stop_latitude" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider dark:text-gray-300">
                                Latitude (Optional)
                            </label>
                            <input
                                type="number"
                                step="any"
                                name="latitude"
                                id="stop_latitude"
                                placeholder="e.g. 27.7172"
                                class="mt-1 w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                        </div>

                        <div>
                            <label for="stop_longitude" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider dark:text-gray-300">
                                Longitude (Optional)
                            </label>
                            <input
                                type="number"
                                step="any"
                                name="longitude"
                                id="stop_longitude"
                                placeholder="e.g. 85.3240"
                                class="mt-1 w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                        </div>
                    </div>

                    <!-- Map Coordinate Picker -->
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-700 uppercase tracking-wider dark:text-gray-300">Pick on Map</span>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    onclick="useMyLocationForStop()"
                                    title="Show your current location on the map, then pick by clicking"
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
                                    onclick="clearStopPicker()"
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
                                id="stopPickerSearch"
                                placeholder="Search for a place, e.g. Biratnagar Airport"
                                autocomplete="off"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                            <button
                                type="button"
                                onclick="searchStopPickerLocation()"
                                title="Search for a location on the map"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Search
                            </button>
                        </div>
                        <div id="stopPickerMap" class="mt-2 h-96 w-full overflow-hidden rounded-xl border border-gray-300 bg-gray-900 z-0 dark:border-gray-700"></div>
                        <p id="stopPickerReadout" class="mt-2 space-y-0.5 text-xs">
                            <span id="stopPickerName" class="hidden font-medium text-gray-900 dark:text-white"></span>
                            <span id="stopPickerStatus" class="font-medium text-gray-500 dark:text-gray-400">Click the map to pick a location</span>
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button
                        type="button"
                        onclick="closeStopModal()"
                        class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        id="submitStopBtn"
                        class="rounded-xl bg-brand-500 px-5 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50"
                    >
                        Save Stop
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAddStopModal() {
        document.getElementById('modalTitle').innerText = 'Add Route Stop';
        document.getElementById('stopForm').action = "{{ route('routes.stops.store', $route) }}";
        document.getElementById('stopFormMethod').value = 'POST';
        
        document.getElementById('stop_name').value = '';
        document.getElementById('stop_order').value = "{{ ($route->stops->max('stop_order') ?? 0) + 1 }}";
        document.getElementById('stop_pickup_time').value = '';
        document.getElementById('stop_drop_time').value = '';
        document.getElementById('stop_latitude').value = '';
        document.getElementById('stop_longitude').value = '';
        document.getElementById('stop_is_active').value = '1';
        document.getElementById('submitStopBtn').innerText = 'Add Stop';
        syncPickupTimeDisplay();
        syncDropTimeDisplay();
        
        document.getElementById('stopModal').classList.remove('hidden');

        fillSuggestedPickupTime();
        resetStopPicker();
    }

    function openEditStopModal(stop) {
        document.getElementById('modalTitle').innerText = 'Edit Route Stop';
        document.getElementById('stopForm').action = "/route-stops/" + stop.id;
        document.getElementById('stopFormMethod').value = 'PUT';
        
        document.getElementById('stop_name').value = stop.name || '';
        document.getElementById('stop_order').value = stop.stop_order || 1;
        
        // Format times to HH:MM for time input if present
        let pickupTime = stop.pickup_time ? stop.pickup_time.substring(0, 5) : '';
        let dropTime = stop.drop_time ? stop.drop_time.substring(0, 5) : '';
        
        document.getElementById('stop_pickup_time').value = pickupTime;
        document.getElementById('stop_drop_time').value = dropTime;
        document.getElementById('stop_latitude').value = stop.latitude || '';
        document.getElementById('stop_longitude').value = stop.longitude || '';
        document.getElementById('stop_is_active').value = (stop.is_active === false || stop.is_active === 0) ? '0' : '1';
        document.getElementById('submitStopBtn').innerText = 'Update Stop';
        syncPickupTimeDisplay();
        syncDropTimeDisplay();
        
        document.getElementById('stopModal').classList.remove('hidden');

        if (stop.latitude && stop.longitude && stop.latitude != 0 && stop.longitude != 0) {
            seedStopPicker(parseFloat(stop.latitude), parseFloat(stop.longitude));
        } else {
            resetStopPicker();
        }
    }

    function closeStopModal() {
        closePickupTimeDropdown();
        closeDropTimeDropdown();
        document.getElementById('stopModal').classList.add('hidden');
    }

    // Close on ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeStopModal();
        }
    });
</script>

<script>
    // ---- Map Coordinate Picker (Leaflet is loaded earlier on the routes.show page) ----
    let stopPickerMap = null;
    let stopPickerMarker = null;
    let stopPickerHereMarker = null;
    let stopPickerHereAccuracy = null;
    let stopPickerInitialized = false;
    const routeStopsData = @json($route->stops);
    const STOP_PICKER_DEFAULT_CENTER = [27.7172, 85.3240];
    const STOP_AVG_SPEED_KMH = 32;
    const STOP_DWELL_MIN = 2;
    const DEFAULT_STOP_INTERVAL_MIN = 5;

    function haversineKm(lat1, lng1, lat2, lng2) {
        const R = 6371;
        const toRad = function (deg) { return deg * Math.PI / 180; };
        const dLat = toRad(lat2 - lat1);
        const dLng = toRad(lng2 - lng1);
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                  Math.sin(dLng / 2) * Math.sin(dLng / 2);
        return 2 * R * Math.asin(Math.sqrt(a));
    }

    function addMinutesToTime(timeStr, minutes) {
        if (!timeStr) return '';
        const parts = timeStr.split(':');
        let total = parseInt(parts[0], 10) * 60 + parseInt(parts[1] || '0', 10) + Math.round(minutes);
        total = ((total % 1440) + 1440) % 1440;
        const hh = String(Math.floor(total / 60)).padStart(2, '0');
        const mm = String(total % 60).padStart(2, '0');
        return hh + ':' + mm;
    }

    function getPreviousStop(newOrder) {
        const ordered = routeStopsData.slice().sort((a, b) => a.stop_order - b.stop_order);
        for (let i = ordered.length - 1; i >= 0; i--) {
            const s = ordered[i];
            if (s.stop_order < newOrder && s.pickup_time && s.latitude && s.longitude && s.latitude != 0 && s.longitude != 0) {
                return s;
            }
        }
        return null;
    }

    function fillSuggestedPickupTime() {
        const pickupInput = document.getElementById('stop_pickup_time');
        if (!pickupInput || pickupInput.value) return;

        const orderInput = document.getElementById('stop_order');
        const newOrder = parseInt(orderInput ? orderInput.value : '', 10) || 1;
        const lat = parseFloat(document.getElementById('stop_latitude').value);
        const lng = parseFloat(document.getElementById('stop_longitude').value);
        const prev = getPreviousStop(newOrder);

        if (prev) {
            let travelMin = DEFAULT_STOP_INTERVAL_MIN;
            if (!isNaN(lat) && !isNaN(lng) && prev.latitude && prev.longitude) {
                const distKm = haversineKm(
                    parseFloat(prev.latitude), parseFloat(prev.longitude), lat, lng
                );
                travelMin = Math.max(DEFAULT_STOP_INTERVAL_MIN, Math.round((distKm / STOP_AVG_SPEED_KMH) * 60 + STOP_DWELL_MIN));
            }
            pickupInput.value = addMinutesToTime(prev.pickup_time.substring(0, 5), travelMin);
        }
        syncPickupTimeDisplay();
    }

    function formatTime12h(timeStr) {
        if (!timeStr) return '--:--';
        const parts = timeStr.split(':');
        let h = parseInt(parts[0], 10);
        const m = parts[1] || '00';
        const suffix = h >= 12 ? 'PM' : 'AM';
        h = h % 12;
        if (h === 0) h = 12;
        return h + ':' + m + ' ' + suffix;
    }

    function timePickerIds(key) {
        return {
            input: 'stop_' + key + '_time',
            text: 'stop_' + key + '_time_text',
            dropdown: 'stop_' + key + '_time_dropdown'
        };
    }

    function syncTimePickerDisplay(key) {
        const ids = timePickerIds(key);
        const input = document.getElementById(ids.input);
        const textEl = document.getElementById(ids.text);
        if (!textEl) return;
        textEl.textContent = formatTime12h(input ? input.value : '');
        highlightCurrentTimePicker(key);
    }

    function buildTimePickerOptions(key) {
        const ids = timePickerIds(key);
        const dropdown = document.getElementById(ids.dropdown);
        if (!dropdown) return;
        dropdown.innerHTML = '';
        for (let total = 0; total < 1440; total += 5) {
            const hh = String(Math.floor(total / 60)).padStart(2, '0');
            const mm = String(total % 60).padStart(2, '0');
            const value = hh + ':' + mm;
            const option = document.createElement('button');
            option.type = 'button';
            option.dataset.value = value;
            option.textContent = formatTime12h(value);
            option.className = 'block w-full rounded-lg px-3 py-1.5 text-left text-sm text-gray-700 transition hover:bg-brand-50 hover:text-brand-600 dark:text-gray-300 dark:hover:bg-brand-500/10 dark:hover:text-brand-400';
            option.addEventListener('click', function () {
                selectTimePickerOption(key, this.dataset.value);
            });
            dropdown.appendChild(option);
        }
    }

    function highlightCurrentTimePicker(key) {
        const ids = timePickerIds(key);
        const dropdown = document.getElementById(ids.dropdown);
        const input = document.getElementById(ids.input);
        if (!dropdown || !input) return;
        const value = input.value;
        Array.prototype.forEach.call(dropdown.children, function (option) {
            const isCurrent = option.dataset.value === value;
            option.classList.toggle('bg-brand-50', isCurrent);
            option.classList.toggle('text-brand-600', isCurrent);
            option.classList.toggle('font-semibold', isCurrent);
            option.classList.toggle('dark:bg-brand-500/10', isCurrent);
            option.classList.toggle('dark:text-brand-400', isCurrent);
        });
    }

    function toggleTimePickerDropdown(key) {
        const ids = timePickerIds(key);
        const dropdown = document.getElementById(ids.dropdown);
        if (!dropdown) return;
        if (dropdown.classList.contains('hidden')) {
            if (!dropdown.hasChildNodes()) buildTimePickerOptions(key);
            highlightCurrentTimePicker(key);
            dropdown.classList.remove('hidden');
        } else {
            dropdown.classList.add('hidden');
        }
    }

    function closeTimePickerDropdown(key) {
        const dropdown = document.getElementById(timePickerIds(key).dropdown);
        if (dropdown) dropdown.classList.add('hidden');
    }

    function selectTimePickerOption(key, value) {
        document.getElementById(timePickerIds(key).input).value = value;
        syncTimePickerDisplay(key);
        closeTimePickerDropdown(key);
    }

    function adjustTimePicker(key, delta) {
        const input = document.getElementById(timePickerIds(key).input);
        const current = input.value || '00:00';
        input.value = addMinutesToTime(current, delta);
        syncTimePickerDisplay(key);
    }

    // Pickup-time wrappers (existing callers)
    function syncPickupTimeDisplay() { syncTimePickerDisplay('pickup'); }
    function togglePickupTimeDropdown() { toggleTimePickerDropdown('pickup'); }
    function closePickupTimeDropdown() { closeTimePickerDropdown('pickup'); }
    function adjustPickupTime(delta) { adjustTimePicker('pickup', delta); }

    // Drop-time wrappers
    function syncDropTimeDisplay() { syncTimePickerDisplay('drop'); }
    function toggleDropTimeDropdown() { toggleTimePickerDropdown('drop'); }
    function closeDropTimeDropdown() { closeTimePickerDropdown('drop'); }
    function adjustDropTime(delta) { adjustTimePicker('drop', delta); }

    function initStopPicker() {
        if (stopPickerInitialized || typeof L === 'undefined') return;

        stopPickerMap = L.map('stopPickerMap', {
            preferCanvas: true,
            updateWhenIdle: true,
            keepBuffer: 1,
            zoomControl: false,
            scrollWheelZoom: true,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(stopPickerMap);

        L.control.zoom({
            position: 'bottomright'
        }).addTo(stopPickerMap);

        stopPickerMap.on('click', function (e) {
            placeStopPickerMarker(e.latlng.lat, e.latlng.lng);
        });

        stopPickerInitialized = true;
    }

    function getStopPickerDefaultCenter() {
        const stops = routeStopsData.filter(s => s.latitude && s.longitude && s.latitude != 0 && s.longitude != 0);
        if (stops.length > 0) {
            const bounds = L.latLngBounds(stops.map(s => [parseFloat(s.latitude), parseFloat(s.longitude)]));
            return { center: bounds.getCenter(), zoom: 14 };
        }
        return { center: STOP_PICKER_DEFAULT_CENTER, zoom: 13 };
    }

    function openStopPicker() {
        initStopPicker();
        if (!stopPickerMap) return;
        setTimeout(function () {
            stopPickerMap.invalidateSize();
        }, 50);
    }

    function placeStopPickerMarker(lat, lng, name) {
        initStopPicker();
        if (!stopPickerMap) return;

        if (!stopPickerMarker) {
            stopPickerMarker = L.marker([lat, lng], { draggable: true }).addTo(stopPickerMap);
            stopPickerMarker.on('dragend', function () {
                const pos = stopPickerMarker.getLatLng();
                syncStopPickerInputs(pos.lat, pos.lng);
            });
        } else {
            stopPickerMarker.setLatLng([lat, lng]);
        }

        syncStopPickerInputs(lat, lng, name);
    }

    function syncStopPickerInputs(lat, lng, name) {
        document.getElementById('stop_latitude').value = lat.toFixed(6);
        document.getElementById('stop_longitude').value = lng.toFixed(6);
        updateStopPickerReadout(lat, lng, name);
        fillSuggestedPickupTime();
        if (!name) {
            reverseGeocodeStop(lat, lng);
        }
    }

    function updateStopPickerReadout(lat, lng, name) {
        const nameEl = document.getElementById('stopPickerName');
        const statusEl = document.getElementById('stopPickerStatus');
        if (lat !== null && lng !== null) {
            if (name) {
                nameEl.textContent = name;
                nameEl.classList.remove('hidden');
            } else {
                nameEl.classList.add('hidden');
            }
            statusEl.textContent = 'Selected: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
            statusEl.className = 'font-mono font-medium text-brand-600 dark:text-brand-400';
        } else {
            nameEl.classList.add('hidden');
            statusEl.textContent = 'Click the map to pick a location';
            statusEl.className = 'font-medium text-gray-500 dark:text-gray-400';
        }
    }

    // Reverse geocode via Nominatim (debounced + cached per location)
    let reverseGeocodeTimer = null;
    const reverseGeocodeCache = {};

    function reverseGeocodeStop(lat, lng) {
        const key = lat.toFixed(4) + ',' + lng.toFixed(4);
        if (reverseGeocodeCache.hasOwnProperty(key)) {
            updateStopPickerReadout(lat, lng, reverseGeocodeCache[key]);
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
                updateStopPickerReadout(lat, lng, name);
            } catch (err) {
                updateStopPickerReadout(lat, lng, null);
            }
        }, 400);
    }

    function seedStopPicker(lat, lng) {
        openStopPicker();
        if (!stopPickerMap) return;
        placeStopPickerMarker(lat, lng);
        stopPickerMap.setView([lat, lng], Math.max(stopPickerMap.getZoom(), 15));
    }

    function resetStopPicker() {
        if (stopPickerMarker) {
            stopPickerMap.removeLayer(stopPickerMarker);
            stopPickerMarker = null;
        }
        openStopPicker();
        if (!stopPickerMap) return;

        const defaults = getStopPickerDefaultCenter();
        centerOnCurrentLocation(defaults.center, defaults.zoom);
    }

    // "You are here" dot (informational, distinct from the selection marker)
    function clearCurrentLocationMarker() {
        if (stopPickerHereAccuracy) {
            stopPickerMap.removeLayer(stopPickerHereAccuracy);
            stopPickerHereAccuracy = null;
        }
        if (stopPickerHereMarker) {
            stopPickerMap.removeLayer(stopPickerHereMarker);
            stopPickerHereMarker = null;
        }
    }

    function showCurrentLocationMarker(lat, lng, accuracy) {
        if (!stopPickerMap) return;
        clearCurrentLocationMarker();

        if (accuracy > 0) {
            stopPickerHereAccuracy = L.circle([lat, lng], {
                radius: accuracy,
                color: '#3B82F6',
                weight: 1,
                fillColor: '#3B82F6',
                fillOpacity: 0.12
            }).addTo(stopPickerMap);
        }

        stopPickerHereMarker = L.circleMarker([lat, lng], {
            radius: 8,
            color: '#FFFFFF',
            weight: 2,
            fillColor: '#3B82F6',
            fillOpacity: 1
        }).addTo(stopPickerMap);
    }

    // Center the map on the browser's current location by default (does NOT auto-select)
    function centerOnCurrentLocation(fallbackCenter, fallbackZoom) {
        if (!stopPickerMap) return;

        if (!navigator.geolocation) {
            stopPickerMap.setView(fallbackCenter, fallbackZoom);
            updateStopPickerReadout(null, null);
            return;
        }

        setStopPickerStatus('Locating your position…');

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                if (!stopPickerMap) return;
                stopPickerMap.setView([pos.coords.latitude, pos.coords.longitude], 15);
                showCurrentLocationMarker(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy || 0);
                setStopPickerStatus('Showing your location — click the map to pick');
            },
            function () {
                if (!stopPickerMap) return;
                stopPickerMap.setView(fallbackCenter, fallbackZoom);
                updateStopPickerReadout(null, null);
            },
            { enableHighAccuracy: true, timeout: 8000, maximumAge: 30000 }
        );
    }

    function clearStopPicker() {
        if (stopPickerMarker) {
            stopPickerMap.removeLayer(stopPickerMarker);
            stopPickerMarker = null;
        }
        document.getElementById('stop_latitude').value = '';
        document.getElementById('stop_longitude').value = '';
        updateStopPickerReadout(null, null);
        if (stopPickerMap) {
            stopPickerMap.setView(getStopPickerDefaultCenter().center, 13);
        }
    }

    function setStopPickerStatus(text) {
        const statusEl = document.getElementById('stopPickerStatus');
        statusEl.textContent = text;
        statusEl.className = 'font-medium text-gray-500 dark:text-gray-400';
    }

    function useMyLocationForStop() {
        openStopPicker();
        if (!stopPickerMap) {
            setStopPickerStatus('Map unavailable');
            return;
        }
        if (!navigator.geolocation) {
            setStopPickerStatus('Location unavailable — click the map instead');
            return;
        }

        setStopPickerStatus('Locating…');

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                placeStopPickerMarker(pos.coords.latitude, pos.coords.longitude);
                stopPickerMap.setView([pos.coords.latitude, pos.coords.longitude], 15);
            },
            function () {
                setStopPickerStatus('Location unavailable — click the map instead');
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
        );
    }

    // Search a location via OpenStreetMap Nominatim and fly to it
    async function searchStopPickerLocation() {
        const queryEl = document.getElementById('stopPickerSearch');
        const q = (queryEl ? queryEl.value : '').trim();
        if (!q) return;

        setStopPickerStatus('Searching…');

        try {
            const response = await fetch(
                'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q)
            );
            const results = await response.json();

            if (results && results.length > 0) {
                const lat = parseFloat(results[0].lat);
                const lng = parseFloat(results[0].lon);

                openStopPicker();
                if (!stopPickerMap) return;

                placeStopPickerMarker(lat, lng, results[0].display_name);
                stopPickerMap.setView([lat, lng], 16);
            } else {
                setStopPickerStatus('No results found for "' + q + '"');
            }
        } catch (err) {
            setStopPickerStatus('Search failed — try again or click the map');
        }
    }

    // Typing coordinates manually moves the map marker (bidirectional sync)
    document.addEventListener('DOMContentLoaded', function () {
        const latInput = document.getElementById('stop_latitude');
        const lngInput = document.getElementById('stop_longitude');
        const searchInput = document.getElementById('stopPickerSearch');

        function syncFromManualInputs() {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);

            if (!isNaN(lat) && !isNaN(lng)) {
                placeStopPickerMarker(lat, lng);
            } else if (stopPickerMarker) {
                stopPickerMap.removeLayer(stopPickerMarker);
                stopPickerMarker = null;
                updateStopPickerReadout(null, null);
            }
        }

        latInput.addEventListener('input', syncFromManualInputs);
        lngInput.addEventListener('input', syncFromManualInputs);

        if (searchInput) {
            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchStopPickerLocation();
                }
            });
        }

        // Close the time picker dropdowns when clicking outside
        document.addEventListener('click', function (event) {
            ['pickup', 'drop'].forEach(function (key) {
                const ids = timePickerIds(key);
                const dropdown = document.getElementById(ids.dropdown);
                const display = document.getElementById('stop_' + key + '_time_display');
                if (dropdown && !dropdown.classList.contains('hidden')) {
                    if (!dropdown.contains(event.target) && event.target !== display && !display.contains(event.target)) {
                        closeTimePickerDropdown(key);
                    }
                }
            });
        });

        buildTimePickerOptions('pickup');
        buildTimePickerOptions('drop');
        syncPickupTimeDisplay();
        syncDropTimeDisplay();
    });
</script>
