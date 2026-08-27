<x-app-layout page="student-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create Student</h1>
            <a
                href="{{ route('students.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            >
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

        <form
            action="{{ route('students.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            @csrf

            <div>
                <h2 class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    Student Details
                </h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="admission_no" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Admission No</label>
                        <input
                            type="text"
                            id="admission_no"
                            name="admission_no"
                            value="{{ old('admission_no') }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('admission_no')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="first_name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">First Name</label>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="{{ old('first_name') }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name</label>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="{{ old('last_name') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date_of_birth" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Birth</label>
                        <input
                            type="date"
                            id="date_of_birth"
                            name="date_of_birth"
                            value="{{ old('date_of_birth') }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('date_of_birth')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="gender" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
                        <select
                            id="gender"
                            name="gender"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
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
                        <label for="grade" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Grade</label>
                        <input
                            type="text"
                            id="grade"
                            name="grade"
                            value="{{ old('grade') }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('grade')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="section" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Section</label>
                        <input
                            type="text"
                            id="section"
                            name="section"
                            value="{{ old('section') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('section')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="roll_no" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Roll No</label>
                        <input
                            type="text"
                            id="roll_no"
                            name="roll_no"
                            value="{{ old('roll_no') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('roll_no')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="photo" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Photo</label>
                        <input
                            type="file"
                            id="photo"
                            name="photo"
                            accept="image/*"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('photo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    School & Parent
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
                                The student will automatically be assigned to your school.
                            </p>
                        </div>
                    @else
                        <div>
                            <label for="school_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">School</label>
                            <select
                                id="school_id"
                                name="school_id"
                                required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="" disabled @selected(old('school_id') === null)>Select School</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>{{ $school->name }}</option>
                                @endforeach
                            </select>
                            @error('school_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div>
                        <label for="parent_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Parent</label>
                        <select
                            id="parent_id"
                            name="parent_id"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="" disabled @selected(old('parent_id') === null)>Select Parent</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" data-school-id="{{ $parent->school_id }}" @selected(old('parent_id') == $parent->id)>{{ $parent->user->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="route_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Assigned Route</label>
                        @if ($routes->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                No routes available. <a href="{{ route('routes.create') }}" class="text-brand-500 hover:text-brand-600">Create a route</a> first to assign one to this student.
                            </p>
                        @else
                            <select
                                id="route_id"
                                name="route_id"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">No route</option>
                                @foreach ($routes as $route)
                                    <option value="{{ $route->id }}" @selected(old('route_id') == $route->id)>{{ $route->name }}@if ($route->route_code) ({{ $route->route_code }})@endif</option>
                                @endforeach
                            </select>
                            @error('route_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <div class="md:col-span-2">
                        <label for="is_active" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <input
                                type="checkbox"
                                id="is_active"
                                name="is_active"
                                value="1"
                                checked
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                            >
                            Active
                        </label>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    Pickup & Drop Locations
                </h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="pickup_location" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pickup Location</label>
                        <input
                            type="text"
                            id="pickup_location"
                            name="pickup_location"
                            value="{{ old('pickup_location') }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('pickup_location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="drop_location" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Drop Location</label>
                        <input
                            type="text"
                            id="drop_location"
                            name="drop_location"
                            value="{{ old('drop_location') }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('drop_location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="pickup_latitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pickup Latitude</label>
                        <input
                            type="text"
                            id="pickup_latitude"
                            name="pickup_latitude"
                            value="{{ old('pickup_latitude') }}"
                            step="any"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('pickup_latitude')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="pickup_longitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pickup Longitude</label>
                        <input
                            type="text"
                            id="pickup_longitude"
                            name="pickup_longitude"
                            value="{{ old('pickup_longitude') }}"
                            step="any"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('pickup_longitude')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="drop_latitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Drop Latitude</label>
                        <input
                            type="text"
                            id="drop_latitude"
                            name="drop_latitude"
                            value="{{ old('drop_latitude') }}"
                            step="any"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('drop_latitude')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="drop_longitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Drop Longitude</label>
                        <input
                            type="text"
                            id="drop_longitude"
                            name="drop_longitude"
                            value="{{ old('drop_longitude') }}"
                            step="any"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                        @error('drop_longitude')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
                <a
                    href="{{ route('students.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    Cancel
                </a>
                <button
                    type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600"
                >
                    Create Student
                </button>
            </div>
        </form>
    </div>
    <script>
        (function () {
            const schoolSelect = document.getElementById('school_id');
            const parentSelect = document.getElementById('parent_id');
            if (!schoolSelect || !parentSelect) return;

            function filterParents() {
                const selectedSchool = schoolSelect.value;
                let selectedHidden = false;
                parentSelect.querySelectorAll('option[data-school-id]').forEach(function (option) {
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
