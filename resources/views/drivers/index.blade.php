<x-app-layout page="drivers">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Drivers</h1>
            <a href="{{ route('drivers.create') }}"
                class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                Create Driver
            </a>
        </div>

        @if (session('success'))
            <div
                class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
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

        <form action="{{ route('drivers.index') }}" method="GET" class="mb-4">
            <div class="flex flex-col gap-2 sm:flex-row">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search drivers..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 sm:flex-none">
                        Search
                    </button>

                    <a href="{{ route('drivers.index') }}"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 sm:flex-none">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        {{-- Desktop / tablet: table view --}}
        <div
            class="hidden overflow-hidden rounded-2xl border border-gray-200 bg-white md:block dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-800">
                        <tr class="text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3 font-medium">Photo</th>
                            <th class="px-5 py-3 font-medium">Employee ID</th>
                            <th class="px-5 py-3 font-medium">Name</th>
                            <th class="px-5 py-3 font-medium">School</th>
                            <th class="px-5 py-3 font-medium">Phone</th>
                            <th class="px-5 py-3 font-medium">License No.</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($drivers as $driver)
                            <tr class="text-gray-700 dark:text-gray-200">
                                <td class="px-5 py-3">
                                    @if ($driver->profile_photo)
                                        <img src="{{ asset('storage/' . $driver->profile_photo) }}"
                                            alt="{{ $driver->full_name }}" class="h-10 w-10 rounded-full object-cover">
                                    @else
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                                            {{ strtoupper(substr($driver->first_name, 0, 1)) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3 font-medium">{{ $driver->employee_id }}</td>
                                <td class="px-5 py-3">
                                    <div>{{ $driver->full_name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $driver->gender }}</div>
                                </td>
                                <td class="px-5 py-3">{{ $driver->school->name ?? '—' }}</td>
                                <td class="px-5 py-3">{{ $driver->phone }}</td>
                                <td class="px-5 py-3">{{ $driver->license_number }}</td>
                                <td class="px-5 py-3">
                                    @if ($driver->status === 'Active')
                                        <span
                                            class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                                    @elseif ($driver->status === 'Suspended')
                                        <span
                                            class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400">Suspended</span>
                                    @else
                                        <span
                                            class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('drivers.show', $driver) }}"
                                            class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                            View
                                        </a>
                                        <a href="{{ route('drivers.edit', $driver) }}"
                                            class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">
                                            Edit
                                        </a>
                                        <form action="{{ route('drivers.destroy', $driver) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete {{ $driver->full_name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-600">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No drivers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile: stacked card view --}}
        <div class="space-y-3 md:hidden">
            @forelse ($drivers as $driver)
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-start gap-3">
                        @if ($driver->profile_photo)
                            <img src="{{ asset('storage/' . $driver->profile_photo) }}" alt="{{ $driver->full_name }}"
                                class="h-12 w-12 shrink-0 rounded-full object-cover">
                        @else
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gray-200 text-sm font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                                {{ strtoupper(substr($driver->first_name, 0, 1)) }}
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-gray-900 dark:text-white">
                                        {{ $driver->full_name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $driver->employee_id }} ·
                                        {{ $driver->gender }}</p>
                                </div>
                                @if ($driver->status === 'Active')
                                    <span
                                        class="shrink-0 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                                @elseif ($driver->status === 'Suspended')
                                    <span
                                        class="shrink-0 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400">Suspended</span>
                                @else
                                    <span
                                        class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                                @endif
                            </div>

                            <dl class="mt-3 grid grid-cols-2 gap-x-3 gap-y-1.5 text-xs">
                                <div>
                                    <dt class="text-gray-400 dark:text-gray-500">School</dt>
                                    <dd class="text-gray-700 dark:text-gray-200">{{ $driver->school->name ?? '—' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-400 dark:text-gray-500">Phone</dt>
                                    <dd class="text-gray-700 dark:text-gray-200">{{ $driver->phone }}</dd>
                                </div>
                                <div class="col-span-2">
                                    <dt class="text-gray-400 dark:text-gray-500">License No.</dt>
                                    <dd class="text-gray-700 dark:text-gray-200">{{ $driver->license_number }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                        <a href="{{ route('drivers.show', $driver) }}"
                            class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-center text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            View
                        </a>
                        <a href="{{ route('drivers.edit', $driver) }}"
                            class="flex-1 rounded-lg bg-brand-500 px-3 py-1.5 text-center text-xs font-medium text-white hover:bg-brand-600">
                            Edit
                        </a>
                        <form action="{{ route('drivers.destroy', $driver) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete {{ $driver->full_name }}?');"
                            class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full rounded-lg bg-red-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-600">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div
                    class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                    No drivers found.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $drivers->links() }}
        </div>
    </div>
</x-app-layout>
