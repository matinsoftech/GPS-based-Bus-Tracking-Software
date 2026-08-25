<x-app-layout page="student-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $student->full_name }}</h1>
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('students.edit', $student) }}"
                    class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"
                >
                    Edit
                </a>
                <a
                    href="{{ route('students.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    Back to Students
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-6 flex items-center gap-4">
                @if ($student->photo)
                    <img
                        src="{{ asset('storage/' . $student->photo) }}"
                        alt="{{ $student->full_name }}"
                        class="h-16 w-16 rounded-full object-cover"
                    >
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-200 text-xl font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $student->admission_no }}</p>
                    @if ($student->is_active)
                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                    @else
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                    @endif
                </div>
            </div>

            <h2 class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                Student Details
            </h2>

            <dl class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->full_name }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Admission No</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->admission_no }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date of Birth</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->date_of_birth->format('M d, Y') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Gender</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->gender }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Grade</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->grade }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Section</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->section ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Roll No</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->roll_no ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">School</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->school->name ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Parent</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->parent->user->name ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned Bus</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                        @if ($student->bus)
                            {{ $student->bus->bus_number }}@if ($student->bus->routes->isNotEmpty()) ({{ $student->bus->routes->pluck('name')->join(', ') }})@endif
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>

            <h2 class="mb-4 mt-6 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                Pickup & Drop Locations
            </h2>

            <dl class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pickup Location</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->pickup_location }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Drop Location</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->drop_location }}</dd>
                </div>

                @if ($student->pickup_latitude && $student->pickup_longitude)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pickup Coordinates</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->pickup_latitude }}, {{ $student->pickup_longitude }}</dd>
                    </div>
                @endif

                @if ($student->drop_latitude && $student->drop_longitude)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Drop Coordinates</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->drop_latitude }}, {{ $student->drop_longitude }}</dd>
                    </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->created_at->format('M d, Y H:i') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->updated_at->format('M d, Y H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-app-layout>
