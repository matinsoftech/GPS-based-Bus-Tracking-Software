<x-app-layout page="route-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Trip History</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Trips using route {{ $route->name }} ({{ $route->route_code }})
                </p>
            </div>
            <a href="{{ route('routes.show', $route) }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                Back to Route
            </a>
        </div>

        @if (session('success'))
            <div
                class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif
        @if (session('warning'))
            <div
                class="mb-4 rounded-lg bg-yellow-50 px-4 py-3 text-sm text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">
                {{ session('warning') }}
            </div>
        @endif

        @if ($errors->any())
            <div
                class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('routes.trips', $route) }}" method="GET" class="mb-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-end">
                <div class="w-full sm:w-auto sm:flex-1">
                    <label for="search"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                        placeholder="Search trips by bus, driver or school..."
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                </div>

                <div>
                    <label for="from"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">From</label>
                    <input type="date" id="from" name="from" value="{{ request('from') }}"
                        onclick="if (this.showPicker) { try { this.showPicker(); } catch (e) {} }"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                </div>

                <div>
                    <label for="to" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">To</label>
                    <input type="date" id="to" name="to" value="{{ request('to') }}"
                        onclick="if (this.showPicker) { try { this.showPicker(); } catch (e) {} }"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                </div>

                <div>
                    <label for="bus_id"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Bus</label>
                    <select name="bus_id" id="bus_id"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">All Buses</option>
                        @foreach ($buses as $bus)
                            <option value="{{ $bus->id }}" @selected((string) request('bus_id') === (string) $bus->id)>
                                {{ $bus->bus_number }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="driver_id"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Driver</label>
                    <select name="driver_id" id="driver_id"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">All Drivers</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}" @selected((string) request('driver_id') === (string) $driver->id)>
                                {{ $driver->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="trip_type"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                    <select name="trip_type" id="trip_type"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">All Types</option>
                        @foreach (\App\Models\Trip::types() as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}" @selected(request('trip_type') === $typeKey)>
                                {{ $typeLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" id="status"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">All Statuses</option>
                        @foreach (\App\Models\Trip::statuses() as $statusKey => $statusLabel)
                            <option value="{{ $statusKey }}" @selected(request('status') === $statusKey)>
                                {{ $statusLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <div>
                        <span class="mb-2 block text-sm font-medium text-transparent select-none"
                            aria-hidden="true">Actions</span>
                        <div class="flex gap-2">
                            <button type="submit"
                                class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                                Filter
                            </button>
                            @if ($trips->total() > 0 || request()->query())
                                <a href="{{ route('routes.trips', $route) }}"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Desktop / tablet: table view --}}
        <div
            class="hidden overflow-hidden rounded-2xl border border-gray-200 bg-white md:block dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-gray-200 dark:border-gray-800">
                        <tr class="text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3 text-left font-medium">Date</th>
                            <th class="px-5 py-3 text-left font-medium">Bus</th>
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
                                <td class="px-5 py-3">{{ $trip->started_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-5 py-3">{{ $trip->bus?->bus_number ?? '—' }}</td>
                                <td class="px-5 py-3">{{ $trip->driver?->full_name ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                        @if ($trip->trip_type === 'home_to_school') bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400
                                        @else
                                            bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400 @endif
                                    ">
                                        {{ $trip->trip_type_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    @if ($trip->status === 'in_progress')
                                        <span
                                            class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-500/10 dark:text-green-500">
                                            <span
                                                class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse mr-1"></span>
                                            In Progress
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
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
                                <td colspan="8"
                                    class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No trips recorded for this route yet.
                                </td>
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

        {{-- Mobile: stacked card view --}}
        <div class="space-y-3 md:hidden">
            @forelse ($trips as $trip)
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $trip->started_at?->format('M d, Y') ?? '—' }}</p>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                {{ $trip->bus?->bus_number ?? '—' }} · {{ $trip->driver?->full_name ?? '—' }}</p>
                        </div>
                        @if ($trip->status === 'in_progress')
                            <span
                                class="inline-flex shrink-0 items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-500/10 dark:text-green-500">
                                <span class="mr-1 h-1.5 w-1.5 animate-pulse rounded-full bg-green-500"></span>
                                In Progress
                            </span>
                        @else
                            <span
                                class="shrink-0 rounded-full bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                Completed
                            </span>
                        @endif
                    </div>

                    <div class="mt-2">
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                            @if ($trip->trip_type === 'home_to_school') bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400
                            @else
                                bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400 @endif
                        ">
                            {{ $trip->trip_type_label }}
                        </span>
                    </div>

                    <dl class="mt-3 grid grid-cols-2 gap-x-3 gap-y-1.5 text-xs">
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">Duration</dt>
                            <dd class="text-gray-700 dark:text-gray-200">{{ $trip->durationInMinutes() ?? '—' }} min
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">Started</dt>
                            <dd class="text-gray-700 dark:text-gray-200">{{ $trip->started_at?->format('H:i:s') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">Ended</dt>
                            <dd class="text-gray-700 dark:text-gray-200">{{ $trip->ended_at?->format('H:i:s') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            @empty
                <div
                    class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                    No trips recorded for this route yet.
                </div>
            @endforelse

            @if ($trips->hasPages())
                <div class="pt-2">
                    {{ $trips->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>