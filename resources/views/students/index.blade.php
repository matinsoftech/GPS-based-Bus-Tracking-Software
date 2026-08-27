<x-app-layout page="student-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Students</h1>
            <a
                href="{{ route('students.create') }}"
                class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"
            >
                Create Student
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('students.index') }}" method="GET" class="mb-4">
            <div class="flex gap-2">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search students..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                >

                <button
                    type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"
                >
                    Search
                </button>

                <a
                    href="{{ route('students.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    Reset
                </a>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 dark:border-gray-800">
                    <tr class="text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-3 font-medium">Photo</th>
                        <th class="px-5 py-3 font-medium">Admission No</th>
                        <th class="px-5 py-3 font-medium">Name</th>
                        <th class="px-5 py-3 font-medium">Grade</th>
                        <th class="px-5 py-3 font-medium">Route</th>
                        <th class="px-5 py-3 font-medium">School</th>
                        <th class="px-5 py-3 font-medium">Parent</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($students as $student)
                        <tr class="text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-3">
                                @if ($student->photo)
                                    <img
                                        src="{{ asset('storage/' . $student->photo) }}"
                                        alt="{{ $student->first_name }}"
                                        class="h-10 w-10 rounded-full object-cover"
                                    >
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3 font-medium">{{ $student->admission_no }}</td>
                            <td class="px-5 py-3">{{ $student->full_name }}</td>
                            <td class="px-5 py-3">{{ $student->grade }}{{ $student->section ? ' - ' . $student->section : '' }}</td>
                            <td class="px-5 py-3">{{ $student->route->name ?? '—' }}</td>
                            <td class="px-5 py-3">{{ $student->school->name ?? '—' }}</td>
                            <td class="px-5 py-3">{{ $student->parent->user->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @if ($student->is_active)
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('students.show', $student) }}"
                                        class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                    >
                                        View
                                    </a>
                                    <a
                                        href="{{ route('students.edit', $student) }}"
                                        class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600"
                                    >
                                        Edit
                                    </a>
                                    <form
                                        action="{{ route('students.destroy', $student) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete {{ $student->full_name }}?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-600"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                No students found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $students->links() }}
        </div>
    </div>
</x-app-layout>
