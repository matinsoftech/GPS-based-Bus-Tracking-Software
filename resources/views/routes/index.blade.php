<x-app-layout page="route-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Routes</h1>
            <a
                href="{{ route('routes.create') }}"
                class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"
            >
                Create Route
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

        <form action="{{ route('routes.index') }}" method="GET" class="mb-4">
            <div class="flex gap-2">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search routes..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                >

                <button
                    type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"
                >
                    Search
                </button>

                <a
                    href="{{ route('routes.index') }}"
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
                        <th class="px-5 py-3 font-medium">Name</th>
                        <th class="px-5 py-3 font-medium">Route Code</th>
                        <th class="px-5 py-3 font-medium">Start Location</th>
                        <th class="px-5 py-3 font-medium">End Location</th>
                        <th class="px-5 py-3 font-medium">Distance</th>
                        <th class="px-5 py-3 font-medium">Duration</th>
                        <th class="px-5 py-3 font-medium">School</th>
                        <th class="px-5 py-3 font-medium">Buses</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($routes as $route)
                        <tr class="text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-3 font-medium">{{ $route->name }}</td>
                            <td class="px-5 py-3">{{ $route->route_code }}</td>
                            <td class="px-5 py-3">{{ $route->start_location }}</td>
                            <td class="px-5 py-3">{{ $route->end_location }}</td>
                            <td class="px-5 py-3">{{ $route->estimated_distance ? $route->estimated_distance . ' km' : '—' }}</td>
                            <td class="px-5 py-3">{{ $route->estimated_duration ? $route->estimated_duration . ' min' : '—' }}</td>
                            <td class="px-5 py-3">{{ $route->school->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @if ($route->activeTrip?->bus)
                                    <span class="inline-block rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-900/20 dark:text-brand-400">
                                        {{ $route->activeTrip->bus->bus_number }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($route->is_active)
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('routes.show', $route) }}"
                                        class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                    >
                                        View
                                    </a>
                                    <a
                                        href="{{ route('routes.edit', $route) }}"
                                        class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600"
                                    >
                                        Edit
                                    </a>
                                    <form
                                        action="{{ route('routes.destroy', $route) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete {{ $route->name }}?');"
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
                            <td colspan="10" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                No routes found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $routes->links() }}
        </div>
    </div>
</x-app-layout>
