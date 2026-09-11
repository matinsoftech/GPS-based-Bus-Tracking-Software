<x-app-layout page="student-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create Student</h1>
            <a href="{{ route('students.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                Back to Students
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

        @php
            $selectedRoutesInit = old('route_ids', []);
            if (!is_array($selectedRoutesInit)) {
                $selectedRoutesInit = [];
            }

            $selectedStopInit = array_map('intval', old('stops', []));

            $stopsByRoute = $routes
                ->filter(fn ($route) => $route->stops->isNotEmpty())
                ->mapWithKeys(fn ($route) => [
                    (string) $route->id => [
                        'name' => $route->name . " ({$route->route_type_label})",
                        'stops' => $route->stops->map(fn ($stop) => [
                            'id' => (int) $stop->id,
                            'name' => $stop->name,
                            'stop_order' => (int) $stop->stop_order,
                            'pickup_time' => $stop->pickup_time,
                        ]),
                    ],
                ]);
        @endphp

        <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data"
            class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
            x-data="{
                availableRoutes: @js($stopsByRoute),
                selectedRoutes: @js($selectedRoutesInit),
                selected: @js($selectedStopInit),
                get routeIds() {
                    return Object.keys(this.availableRoutes);
                },
                isRouteSelected(id) {
                    return this.selectedRoutes.includes(String(id));
                },
                toggleRoute(id) {
                    const idStr = String(id);
                    if (this.selectedRoutes.includes(idStr)) {
                        this.selectedRoutes = this.selectedRoutes.filter(x => x !== idStr);
                        this.availableRoutes[idStr]?.stops?.forEach(stop => {
                            this.selected = this.selected.filter(x => x !== stop.id);
                        });
                    } else {
                        this.selectedRoutes.push(idStr);
                    }
                },
                get groupedStops() {
                    return this.selectedRoutes
                        .filter(id => this.availableRoutes[id] && this.availableRoutes[id].stops.length > 0)
                        .map(id => ({
                            routeId: id,
                            routeName: this.availableRoutes[id].name,
                            stops: this.availableRoutes[id].stops,
                        }));
                },
                toggleStop(id) {
                    this.selected.includes(id)
                        ? (this.selected = this.selected.filter(x => x !== id))
                        : (this.selected.push(id));
                },
            }">
            @csrf

            <div>
                <h2
                    class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    Student Details
                </h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <x-label for="admission_no" value="Admission No" required />
                        <input type="text" id="admission_no" name="admission_no" value="{{ old('admission_no') }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('admission_no')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-label for="first_name" value="First Name" required />
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-label for="last_name" value="Last Name" />
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-label for="date_of_birth" value="Date of Birth" required />
                        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                            max="{{ date('Y-m-d') }}" required
                            onclick="if (this.showPicker) { try { this.showPicker(); } catch (e) {} }"
                            class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white [&::-webkit-calendar-picker-indicator]:cursor-pointer dark:[&::-webkit-calendar-picker-indicator]:invert">
                        @error('date_of_birth')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-label for="gender" value="Gender" required />
                        <select id="gender" name="gender" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="" disabled @selected(old('gender') === null)>Select Gender</option>
                            <option value="Male" @selected(old('gender') === 'Male')>Male</option>
                            <option value="Female" @selected(old('gender') === 'Female')>Female</option>
                            <option value="Other" @selected(old('gender') === 'Other')>Other</option>
                        </select>
                        @error('gender')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-label for="grade" value="Grade" required />
                        <input type="text" id="grade" name="grade" value="{{ old('grade') }}" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('grade')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-label for="section" value="Section" />
                        <input type="text" id="section" name="section" value="{{ old('section') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('section')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-label for="roll_no" value="Roll No" />
                        <input type="text" id="roll_no" name="roll_no" value="{{ old('roll_no') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('roll_no')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-label for="photo" value="Photo" />
                        <input type="file" id="photo" name="photo" accept="image/*"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('photo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div>
                <h2
                    class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    School & Parent
                </h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    @if (isset($school) && $school)
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">School</label>
                            <input type="text" value="{{ $school->name }}" readonly
                                class="w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                The student will automatically be assigned to your school.
                            </p>
                        </div>
                    @else
                        <div>
                            <x-label for="school_id" value="School" required />
                            <select id="school_id" name="school_id" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="" disabled @selected(old('school_id') === null)>Select School</option>
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

                    <div>
                        <x-label for="parent_id" value="Parent" required />
                        <select id="parent_id" name="parent_id" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="" disabled @selected(old('parent_id') === null)>Select Parent</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" data-school-id="{{ $parent->school_id }}"
                                    @selected(old('parent_id') == $parent->id)>{{ $parent->user->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <x-label
                            value="Assigned
                            Routes" />
                        @if ($routes->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                No routes available. <a href="{{ route('routes.create') }}"
                                    class="text-brand-500 hover:text-brand-600">Create a route</a> first to assign one
                                to this student.
                            </p>
                        @else
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                                @foreach ($routes as $route)
                                    <label
                                        class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-white/[0.03]">
                                        <input type="checkbox" name="route_ids[]"
                                            value="{{ $route->id }}"
                                            :checked="isRouteSelected({{ $route->id }})"
                                            @change="toggleRoute({{ $route->id }})"
                                            class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ $route->name }} ({{ $route->route_type_label }})
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('route_ids')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <div class="md:col-span-2">
                        <label for="is_active"
                            class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <input type="checkbox" id="is_active" name="is_active" value="1" checked
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                            Active
                        </label>
                    </div>
                </div>
            </div>

            <div>
                <h2
                    class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    Assigned Stops
                </h2>

                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Select one or more stops from the assigned routes. The stops shown below update automatically
                    based on the selected routes, grouped by route.
                </p>

                <template x-if="groupedStops.length > 0">
                    <div class="space-y-5">
                        <template x-for="group in groupedStops" :key="group.routeId">
                            <div
                                class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-white/[0.03]">
                                <h3
                                    class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
                                    <span x-text="group.routeName"></span>
                                </h3>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                                    <template x-for="stop in group.stops" :key="stop.id">
                                        <label
                                            class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-gray-600 dark:bg-gray-800">
                                            <input type="checkbox" name="stops[]" :value="stop.id"
                                                :checked="selected.includes(stop.id)" @change="toggleStop(stop.id)"
                                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                <span x-text="stop.name"></span>
                                                <span class="text-gray-400" x-show="stop.pickup_time"
                                                    x-text="'(' + stop.pickup_time + ')'"></span>
                                            </span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="groupedStops.length === 0">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <span x-show="selectedRoutes.length === 0">Select one or more routes above to see their stops.</span>
                        <span x-show="selectedRoutes.length > 0">The selected routes have no stops configured yet.</span>
                    </p>
                </template>

                @error('stops')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
                <a href="{{ route('students.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    Create Student
                </button>
            </div>
        </form>
    </div>
    <script>
        (function() {
            const schoolSelect = document.getElementById('school_id');
            const parentSelect = document.getElementById('parent_id');
            if (!schoolSelect || !parentSelect) return;

            function filterParents() {
                const selectedSchool = schoolSelect.value;
                let selectedHidden = false;
                parentSelect.querySelectorAll('option[data-school-id]').forEach(function(option) {
                    const show = !selectedSchool || option.dataset.schoolId === selectedSchool;
                    option.hidden = !show;
                    if (!show && option.selected) {
                        selectedHidden = true;
                    }
                });
                if (selectedHidden) {
                    parentSelect.value = '';
                }
            }

            schoolSelect.addEventListener('change', filterParents);
            filterParents();
        })();
    </script>
</x-app-layout>
