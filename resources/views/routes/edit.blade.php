<x-app-layout page="route-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Route</h1>
            <a
                href="{{ route('routes.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            >
                Back to Routes
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
            action="{{ route('routes.update', $route) }}"
            method="POST"
            class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            @csrf
            @method('PUT')

            <div>
                <h2 class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    Route Details
                </h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <x-label for="name" value="Route Name" required />
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $route->name) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-label for="route_code" value="Route Code" required />
                        <input
                            type="text"
                            id="route_code"
                            name="route_code"
                            value="{{ old('route_code', $route->route_code) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('route_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-label for="route_type" value="Route Type" required />
                        <select
                            id="route_type"
                            name="route_type"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="home_to_school" @selected(old('route_type', $route->route_type) === 'home_to_school')>Home to School</option>
                            <option value="school_to_home" @selected(old('route_type', $route->route_type) === 'school_to_home')>School to Home</option>
                        </select>
                        @error('route_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-label for="start_location" value="Start Location" required />
                        <input
                            type="text"
                            id="start_location"
                            name="start_location"
                            value="{{ old('start_location', $route->start_location) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('start_location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-label for="end_location" value="End Location" required />
                        <input
                            type="text"
                            id="end_location"
                            name="end_location"
                            value="{{ old('end_location', $route->end_location) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('end_location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-label for="estimated_distance" value="Estimated Distance (km)" />
                        <input
                            type="number"
                            id="estimated_distance"
                            name="estimated_distance"
                            value="{{ old('estimated_distance', $route->estimated_distance) }}"
                            step="0.01"
                            min="0"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('estimated_distance')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-label for="estimated_duration" value="Estimated Duration (minutes)" />
                        <input
                            type="number"
                            id="estimated_duration"
                            name="estimated_duration"
                            value="{{ old('estimated_duration', $route->estimated_duration) }}"
                            min="0"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('estimated_duration')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    School & Status
                </h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    @if (isset($school) && $school)
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">School</label>
                            <input
                                type="text"
                                value="{{ $school->name }}"
                                readonly
                                class="w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400"
                            >
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                The route is assigned to your school.
                            </p>
                        </div>
                    @else
                        <div>
                            <x-label for="school_id" value="School" required />
                            <select
                                id="school_id"
                                name="school_id"
                                required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="" disabled>Select School</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}" @selected(old('school_id', $route->school_id) == $school->id)>{{ $school->name }}</option>
                                @endforeach
                            </select>
                            @error('school_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="md:col-span-2">
                        <label for="is_active" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <input
                                type="checkbox"
                                id="is_active"
                                name="is_active"
                                value="1"
                                @checked(old('is_active', $route->is_active))
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                            >
                            Active
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
                <a
                    href="{{ route('routes.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    Cancel
                </a>
                <button
                    type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600"
                >
                    Update Route
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
