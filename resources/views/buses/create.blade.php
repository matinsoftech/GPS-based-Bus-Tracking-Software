<x-app-layout page="buses">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create Bus</h1>
            <a href="{{ route('buses.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                Back to Buses
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

        <form action="{{ route('buses.store') }}" method="POST"
            class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            @csrf

            <div>
                <h2
                    class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    Vehicle Details
                </h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="bus_number"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Bus Number</label>
                        <input type="text" id="bus_number" name="bus_number" value="{{ old('bus_number') }}"
                            placeholder="BUS-001" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('bus_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="registration_number"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Registration
                            Number</label>
                        <input type="text" id="registration_number" name="registration_number"
                            value="{{ old('registration_number') }}" placeholder="BA 1 KHA 1234" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('registration_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="make"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Make</label>
                        <input type="text" id="make" name="make" value="{{ old('make') }}"
                            placeholder="Ashok Leyland"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('make')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="model"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                        <input type="text" id="model" name="model" value="{{ old('model') }}"
                            placeholder="Viking"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('model')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="year"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Year</label>
                        <input type="number" id="year" name="year" value="{{ old('year') }}" min="1950"
                            max="{{ now()->year }}" placeholder="{{ now()->year }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="capacity"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Capacity</label>
                        <input type="number" id="capacity" name="capacity" value="{{ old('capacity', 40) }}"
                            min="1" max="200" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('capacity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="fuel_type"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Fuel Type</label>
                        <select id="fuel_type" name="fuel_type"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="">Select Fuel Type</option>
                            <option value="Diesel" @selected(old('fuel_type') === 'Diesel')>Diesel</option>
                            <option value="Petrol" @selected(old('fuel_type') === 'Petrol')>Petrol</option>
                            <option value="Electric" @selected(old('fuel_type') === 'Electric')>Electric</option>
                            <option value="CNG" @selected(old('fuel_type') === 'CNG')>CNG</option>
                            <option value="Hybrid" @selected(old('fuel_type') === 'Hybrid')>Hybrid</option>
                        </select>
                        @error('fuel_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="gps_device_id"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">IOT IMEI</label>
                        <input type="text" id="gps_device_id" name="gps_device_id"
                            value="{{ old('gps_device_id') }}" placeholder="GPS-1001"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('gps_device_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="insurance_number"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Insurance
                            Number</label>
                        <input type="text" id="insurance_number" name="insurance_number"
                            value="{{ old('insurance_number') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('insurance_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="insurance_expiry_date"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Insurance Expiry
                            Date</label>
                        <input type="date" id="insurance_expiry_date" name="insurance_expiry_date"
                            value="{{ old('insurance_expiry_date') }}"
                            onclick="if (this.showPicker) { try { this.showPicker(); } catch (e) {} }"
                            class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white [&::-webkit-calendar-picker-indicator]:cursor-pointer dark:[&::-webkit-calendar-picker-indicator]:invert">
                        @error('insurance_expiry_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_service_date"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Last Service
                            Date</label>
                        <input type="date" id="last_service_date" name="last_service_date"
                            value="{{ old('last_service_date') }}" max="{{ date('Y-m-d') }}"
                            onclick="if (this.showPicker) { try { this.showPicker(); } catch (e) {} }"
                            class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white [&::-webkit-calendar-picker-indicator]:cursor-pointer dark:[&::-webkit-calendar-picker-indicator]:invert">
                        @error('last_service_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select id="status" name="status" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="Active" @selected(old('status', 'Active') === 'Active')>Active</option>
                            <option value="Maintenance" @selected(old('status') === 'Maintenance')>Maintenance</option>
                            <option value="Inactive" @selected(old('status') === 'Inactive')>Inactive</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if (auth()->user()->hasAnyRole(['School Admin', 'Principal']))
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">School</label>
                            <input type="text"
                                value="{{ isset($school) && $school ? $school->name : 'School not assigned' }}"
                                readonly
                                class="w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                The bus will automatically be assigned to your school.
                            </p>
                        </div>
                    @else
                        <div>
                            <label for="school_id"
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">School</label>
                            <select id="school_id" name="school_id"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="">Select School (optional)</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>
                                        {{ $school->name }}</option>
                                @endforeach
                            </select>
                            @error('school_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="md:col-span-2">
                        <label for="notes"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                        <textarea id="notes" name="notes" rows="3"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div>
                <h2
                    class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    Driver Assignment
                </h2>

                <div>
                    <label for="driver_ids"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Drivers</label>
                    <div
                        class="max-h-48 overflow-y-auto rounded-lg border border-gray-300 p-3 dark:border-gray-600 dark:bg-gray-800">
                        @forelse ($drivers as $driver)
                            <label class="flex items-center gap-2 py-1">
                                <input type="checkbox" name="driver_ids[]" value="{{ $driver->id }}"
                                    @checked(in_array($driver->id, old('driver_ids', [])))
                                    class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $driver->full_name }}
                                    ({{ $driver->employee_id }})
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">No drivers available.</p>
                        @endforelse
                    </div>
                    @error('driver_ids')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
                <a href="{{ route('buses.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    Create Bus
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
