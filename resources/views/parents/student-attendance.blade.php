<x-app-layout page="my-children">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Attendance</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $student->full_name }} ·
                    {{ trim($student->grade.' '.$student->section) }} ·
                    {{ $student->admission_no }} ·
                    {{ $student->school?->name ?? '—' }}
                </p>
            </div>
            <a
                href="{{ route('parent.children') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            >
                Back to My Students
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Attendance Records</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $totalRecords }} record{{ $totalRecords === 1 ? '' : 's' }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-800">
                        <tr class="text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3 font-medium">Date</th>
                            <th class="px-5 py-3 font-medium">Trip</th>
                            <th class="px-5 py-3 font-medium">Check In</th>
                            <th class="px-5 py-3 font-medium">Check Out</th>
                            <th class="px-5 py-3 font-medium">Bus</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($records as $record)
                            <tr class="text-gray-700 dark:text-gray-200">
                                <td class="px-5 py-3 whitespace-nowrap">{{ $record->date->format('M d, Y') }}</td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                        {{ $record->tripLabel() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    @if ($record->check_in_at)
                                        <span class="text-xs font-medium text-green-700 dark:text-green-400">
                                            {{ $record->check_in_at->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    @if ($record->check_out_at)
                                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                            {{ $record->check_out_at->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">{{ $record->route?->name ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    @if ($record->isCheckedOut())
                                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Completed</span>
                                    @elseif ($record->isCheckedIn())
                                        <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">Checked In</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Not Checked In</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No attendance records found for this student yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
