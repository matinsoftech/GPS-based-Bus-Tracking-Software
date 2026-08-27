<x-app-layout page="trips">
    <div class="mx-auto max-w-2xl p-4 md:p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Start New Trip</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select bus and route to begin your trip</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('driver.trips.toggle') }}" method="POST" class="space-y-5" x-data="tripForm()">
            @csrf

            <!-- Bus Selection -->
            <div>
                <label for="bus_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Bus <span class="text-red-500">*</span></label>
                <select name="bus_id" id="bus_id" required x-model="selectedBusId" @change="prefillGps($event.target.value)"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">Select a bus</option>
                    @foreach ($buses as $bus)
                        <option value="{{ $bus->id }}"
                            :data-lat="{{ $bus->gpsDevice?->latitude ?? '' }}"
                            :data-lng="{{ $bus->gpsDevice?->longitude ?? '' }}">
                            {{ $bus->bus_number }} ({{ $bus->registration_number }})
                        </option>
                    @endforeach
                </select>
                @error('bus_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Route Selection -->
            <div>
                <label for="route_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Route <span class="text-red-500">*</span></label>
                <select name="route_id" id="route_id" required x-model="selectedRouteId"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">Select a route</option>
                    @foreach ($routes as $route)
                        <option value="{{ $route->id }}">{{ $route->name }} ({{ $route->route_code }})</option>
                    @endforeach
                </select>
                @error('route_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Trip Type Selection -->
            <div>
                <label for="trip_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Trip Type <span class="text-red-500">*</span></label>
                <select name="trip_type" id="trip_type" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="home_to_school" selected>Home to School</option>
                    <option value="school_to_home">School to Home</option>
                </select>
                @error('trip_type')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- GPS Location (optional, prefilled) -->
            <fieldset class="border border-gray-200 rounded-lg p-4 dark:border-gray-700">
                <legend class="text-sm font-medium text-gray-700 dark:text-gray-300">Start Location (Optional)</legend>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Auto-filled from bus GPS. Adjust if needed.</p>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <div>
                        <label for="latitude" class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Latitude</label>
                        <input type="number" name="latitude" id="latitude" step="0.000001"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            x-ref="latInput"
                            :value="prefillLat ?? ''">
                    </div>
                    <div>
                        <label for="longitude" class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Longitude</label>
                        <input type="number" name="longitude" id="longitude" step="0.000001"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            x-ref="lngInput"
                            :value="prefillLng ?? ''">
                    </div>
                </div>
            </fieldset>

            <!-- Notes -->
            <div>
                <label for="notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea name="notes" id="notes" rows="3"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    placeholder="Any notes for this trip..."></textarea>
            </div>

            <!-- Submit -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-800 flex justify-end gap-3">
                <a href="{{ route('driver.trips.index') }}" class="rounded-lg border border-gray-300 bg-white px-6 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-6 py-2 text-sm font-medium text-white hover:bg-brand-600"
                    :disabled="!selectedBusId || !selectedRouteId">
                    Start Trip
                </button>
            </div>
        </form>
    </div>
</x-app-layout>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('tripForm', () => ({
        selectedBusId: '',
        selectedRouteId: '',
        prefillLat: '{{ $prefillGps['latitude'] ?? '' }}',
        prefillLng: '{{ $prefillGps['longitude'] ?? '' }}',
        busGpsData: @json($buses->mapWithKeys(fn($b) => [$b->id => [
            'lat' => $b->gpsDevice?->latitude ?? null,
            'lng' => $b->gpsDevice?->longitude ?? null,
        ]])->toArray()),

        init() {
            this.$watch('selectedBusId', (id) => {
                if (id && this.busGpsData[id]) {
                    this.prefillLat = this.busGpsData[id].lat ?? '';
                    this.prefillLng = this.busGpsData[id].lng ?? '';
                }
            });
        },

        prefillGps(busId) {
            if (busId && this.busGpsData[busId]) {
                this.prefillLat = this.busGpsData[busId].lat ?? '';
                this.prefillLng = this.busGpsData[busId].lng ?? '';
            }
        }
    }));
});
</script>
@endpush