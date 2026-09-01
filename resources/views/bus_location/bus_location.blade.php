<x-app-layout page="bus-location">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Live Bus Tracking</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Real-time GPS positions and telemetry for every bus in the fleet, sourced directly from the live GPS provider.
                </p>
            </div>
            <span id="lastUpdateBadge" class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400"></span>
        </div>

        @include('partials.fleet-map', [
            'fleetMap' => $fleetMap,
            'fleetMapRefreshUrl' => route('bus_location.latest'),
        ])

        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Fleet Telemetry</h2>
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-800">
                        <tr class="text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3 font-medium">Bus</th>
                            <th class="px-5 py-3 font-medium">Route</th>
                            <th class="px-5 py-3 font-medium">Driver</th>
                            <th class="px-5 py-3 font-medium">School</th>
                            <th class="px-5 py-3 font-medium">Speed</th>
                            <th class="px-5 py-3 font-medium">Coordinates</th>
                            <th class="px-5 py-3 font-medium">Last Signal</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($fleetMap['buses'] as $bus)
                            @php
                                $lat = $bus['latitude'] ?? null;
                                $lng = $bus['longitude'] ?? null;
                                $status = $bus['tracking_status'] ?? 'offline';
                                $statusLabel = $bus['status_label'] ?? ucfirst($status);
                                $statusColor = $bus['status_color'] ?? '#9ca3af';
                            @endphp
                            <tr
                                class="cursor-pointer text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800/40"
                                x-on:click="focusBus({{ $lat ?? 'null' }}, {{ $lng ?? 'null' }})"
                            >
                                <td class="px-5 py-3 font-medium">
                                    {{ $bus['bus_number'] ?? '—' }}
                                    @if ($bus['registration_number'])
                                        <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-mono text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                            {{ $bus['registration_number'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">{{ $bus['route_name'] ?? '—' }}</td>
                                <td class="px-5 py-3">{{ $bus['driver_name'] ?? '—' }}</td>
                                <td class="px-5 py-3">{{ $bus['school_name'] ?? '—' }}</td>
                                <td class="px-5 py-3 font-mono">{{ number_format($bus['speed'] ?? 0, 0) }} km/h</td>
                                <td class="px-5 py-3 font-mono">
                                    {{ $lat !== null ? number_format((float) $lat, 5) : '—' }},
                                    {{ $lng !== null ? number_format((float) $lng, 5) : '—' }}
                                </td>
                                <td class="px-5 py-3">
                                    @if ($bus['last_updated_ago'])
                                        {{ $bus['last_updated_ago'] }}
                                    @elseif ($bus['recorded_at'])
                                        {{ \Illuminate\Support\Carbon::parse($bus['recorded_at'])->format('M d, Y H:i:s') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        style="background-color: {{ $statusColor }}1a; color: {{ $statusColor }};">
                                        <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $statusColor }};"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No buses found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const badge = document.getElementById('lastUpdateBadge');
        const updateBadge = () => {
            const el = document.getElementById('fleetMapLastUpdate');
            if (el && el.textContent) {
                badge.textContent = el.textContent;
            }
        };

        // Mirror the fleet map's "updated" label into the page header badge.
        updateBadge();
        setInterval(updateBadge, 5000);
    });

    function focusBus(lat, lng) {
        if (typeof window.fleetMapInstance === 'undefined' || lat === null || lng === null) return;
        window.fleetMapInstance.flyTo([lat, lng], 15, { duration: 0.8 });
    }
</script>
