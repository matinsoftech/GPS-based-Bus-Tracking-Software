<x-app-layout page="trips">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Trip History</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View all trips for your school</p>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <table class="w-full">
                <thead class="border-b border-gray-200 dark:border-gray-800">
                    <tr class="text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-3 text-left font-medium">Date</th>
                        <th class="px-5 py-3 text-left font-medium">Bus</th>
                        <th class="px-5 py-3 text-left font-medium">Route</th>
                        <th class="px-5 py-3 text-left font-medium">Driver</th>
                        <th class="px-5 py-3 text-left font-medium">Type</th>
                        <th class="px-5 py-3 text-left font-medium">Status</th>
                        <th class="px-5 py-3 text-left font-medium">Duration</th>
                        <th class="px-5 py-3 text-left font-medium">Started</th>
                        <th class="px-5 py-3 text-left font-medium">Ended</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($trips as $trip)
                        <tr class="text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-3">{{ $trip->started_at->format('M d, Y') }}</td>
                            <td class="px-5 py-3">{{ $trip->bus->bus_number }}</td>
                            <td class="px-5 py-3">{{ $trip->route?->name ?? '—' }}</td>
                            <td class="px-5 py-3">{{ $trip->driver?->full_name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    @if ($trip->trip_type === 'home_to_school')
                                        bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400
                                    @else
                                        bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400
                                    @endif
                                ">
                                    {{ $trip->trip_type_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                @if ($trip->status === 'in_progress')
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-500/10 dark:text-green-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse mr-1"></span>
                                        In Progress
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        Completed
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3">{{ $trip->durationInMinutes() ?? '—' }} min</td>
                            <td class="px-5 py-3">{{ $trip->started_at->format('H:i:s') }}</td>
                            <td class="px-5 py-3">{{ $trip->ended_at ? $trip->ended_at->format('H:i:s') : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                No trips recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($trips->hasPages())
                <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-800">
                    {{ $trips->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>