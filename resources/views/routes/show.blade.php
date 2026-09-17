<x-app-layout page="route-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6 space-y-6">
        
        <!-- Header Bar: Route Name, Edit, Back -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-5 dark:border-gray-800">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $route->name }}</h1>
                    <span class="rounded-md bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 font-mono">
                        {{ $route->route_code }}
                    </span>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Route details, ordered stops management, and visual route preview map.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <a
                    href="{{ route('routes.edit', $route) }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50 sm:w-auto"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Route
                </a>
                <a
                    href="{{ route('routes.index') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-xs transition hover:bg-gray-50 sm:w-auto dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Routes
                </a>
            </div>
        </div>

        <!-- Success Alert -->
        @if (session('success'))
            <div class="flex items-center gap-3 rounded-xl bg-green-50 p-4 text-sm font-medium text-green-800 dark:bg-green-500/10 dark:text-green-400 border border-green-200 dark:border-green-800/40">
                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Validation Errors Alert -->
        @if ($errors->any())
            <div class="rounded-xl bg-red-50 p-4 text-sm text-red-800 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-800/40">
                <div class="flex items-center gap-2 font-semibold mb-1">
                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Please fix the following errors:</span>
                </div>
                <ul class="list-inside list-disc ml-6 text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $routeTabs = [
                ['key' => 'overview', 'label' => 'Overview', 'url' => route('routes.show', $route), 'icon' => 'chart-pie'],
                ['key' => 'stops', 'label' => 'Stops', 'url' => route('routes.show', [$route, 'tab' => 'stops']), 'icon' => 'map-pin', 'count' => $route->stops->count()],
                ['key' => 'map', 'label' => 'Map', 'url' => route('routes.show', [$route, 'tab' => 'map']), 'icon' => 'map'],
                ['key' => 'trips', 'label' => 'Trip History', 'url' => route('routes.show', [$route, 'tab' => 'trips']), 'icon' => 'clock', 'count' => $tripCount],
            ];
        @endphp

        <x-pill-tabs :tabs="$routeTabs" :active="$tab" />

        {{-- TAB: OVERVIEW --}}
        @if ($tab === 'overview')
            @include('routes.partials.route-info-card')
        @endif

        {{-- TAB: STOPS --}}
        @if ($tab === 'stops')
            <!-- Card 2: Route Stops with [+ Add Stop] button & Table -->
            @include('routes.partials.route-stops-card')

            <!-- Add & Edit Stop Modal -->
            @include('routes.partials.stop-modal')
        @endif

        {{-- TAB: MAP --}}
        @if ($tab === 'map')
            @include('routes.partials.route-map-preview')
        @endif

        {{-- TAB: TRIP HISTORY --}}
        @if ($tab === 'trips')
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Trip History</h2>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $trips->total() }} trip{{ $trips->total() === 1 ? '' : 's' }}</span>
                        <a href="{{ route('routes.trips', $route) }}"
                            class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            View Full History
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 dark:border-gray-800">
                            <tr class="text-gray-500 dark:text-gray-400">
                                <th class="px-5 py-3 font-medium">Date</th>
                                <th class="px-5 py-3 font-medium">Bus</th>
                                <th class="px-5 py-3 font-medium">Driver</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 font-medium">Duration</th>
                                <th class="px-5 py-3 font-medium">Started</th>
                                <th class="px-5 py-3 font-medium">Ended</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($trips as $trip)
                                <tr class="text-gray-700 dark:text-gray-200">
                                    <td class="px-5 py-3">{{ $trip->started_at?->format('M d, Y') ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        @if ($trip->bus)
                                            <a href="{{ route('buses.show', $trip->bus) }}" class="text-brand-600 hover:underline dark:text-brand-400">{{ $trip->bus->bus_number }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @if ($trip->driver)
                                            <a href="{{ route('drivers.show', $trip->driver) }}" class="text-brand-600 hover:underline dark:text-brand-400">{{ $trip->driver->full_name }}</a>
                                        @else
                                            —
                                        @endif
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
                                    <td class="px-5 py-3">{{ $trip->started_at?->format('H:i:s') ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $trip->ended_at?->format('H:i:s') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No trips recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if ($trips->hasPages())
                        <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-800">
                            {{ $trips->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>
</x-app-layout>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
