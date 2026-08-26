<x-app-layout page="attendance">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Attendance History</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $route->name }}@if ($route->route_code) ({{ $route->route_code }})@endif ·
                    {{ $route->school->name ?? '—' }}
                </p>
            </div>
            <a
                href="{{ route('attendance.routes.show', $route) }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            >
                Back to Attendance
            </a>
        </div>

        <form action="{{ route('attendance.routes.history', $route) }}" method="GET" class="mb-6">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="from" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">From</label>
                    <input
                        type="date"
                        id="from"
                        name="from"
                        value="{{ $from->toDateString() }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                </div>
                <div>
                    <label for="to" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">To</label>
                    <input
                        type="date"
                        id="to"
                        name="to"
                        value="{{ $to->toDateString() }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                </div>
                <button
                    type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"
                >
                    Apply
                </button>
                <a
                    href="{{ route('attendance.routes.history', $route) }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    Clear
                </a>
            </div>
        </form>

        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

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
                            <th class="px-5 py-3 font-medium">Student</th>
                            <th class="px-5 py-3 font-medium">Trip</th>
                            <th class="px-5 py-3 font-medium">Check In</th>
                            <th class="px-5 py-3 font-medium">Check Out</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Marked By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($records as $record)
                            <tr class="text-gray-700 dark:text-gray-200">
                                <td class="px-5 py-3 whitespace-nowrap">{{ $record->date->format('M d, Y') }}</td>
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $record->student?->full_name ?? '—' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $record->student?->admission_no ?? '' }}</p>
                                </td>
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
                                <td class="px-5 py-3">
                                    @if ($record->isCheckedOut())
                                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Completed</span>
                                    @else
                                        <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">Checked In</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    {{ $record->markedBy?->name ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No attendance records found for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($records->hasPages())
                <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
