<x-app-layout page="school-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $school->name }}</h1>
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('schools.edit', $school) }}"
                    class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"
                >
                    Edit
                </a>
                <a
                    href="{{ route('schools.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    Back to Schools
                </a>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">
            @php
                $statTiles = [
                    [
                        'label' => 'Students',
                        'value' => $totalStudents,
                        'sub' => "{$activeStudents} active",
                        'icon' => 'academic-cap',
                    ],
                    [
                        'label' => 'Drivers',
                        'value' => $totalDrivers,
                        'sub' => "{$activeDrivers} active",
                        'icon' => 'user-circle',
                    ],
                    [
                        'label' => 'Buses',
                        'value' => $totalBuses,
                        'sub' => "{$activeBuses} active · {$maintenanceBuses} maintenance · {$inactiveBuses} inactive",
                        'icon' => 'truck',
                    ],
                    [
                        'label' => 'Routes',
                        'value' => $totalRoutes,
                        'sub' => "{$activeRoutes} active",
                        'icon' => 'map',
                    ],
                    [
                        'label' => 'Route Stops',
                        'value' => $totalStops,
                        'sub' => 'across all routes',
                        'icon' => 'map-pin',
                    ],
                    [
                        'label' => 'Parents',
                        'value' => $totalParents,
                        'sub' => 'registered parents',
                        'icon' => 'users',
                    ],
                    [
                        'label' => 'School Admins',
                        'value' => $totalSchoolAdmins,
                        'sub' => 'administrators',
                        'icon' => 'shield-check',
                    ],
                ];
            @endphp

            @foreach ($statTiles as $tile)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-start justify-between">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $tile['label'] }}</p>
                        <x-heroicon-o-{{ $tile['icon'] }} class="h-5 w-5 text-brand-500" />
                    </div>
                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $tile['value'] }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $tile['sub'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">School Code</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $school->code }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1">
                        <span
                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium
                                @if ($school->status === 'active')
                                    bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400
                                @elseif ($school->status === 'inactive')
                                    bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400
                                @else
                                    bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400
                                @endif
                            "
                        >
                            {{ ucfirst($school->status) }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->email }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->phone ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Principal Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->principal_name ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->address ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Latitude</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->latitude ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Longitude</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->longitude ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->created_at->format('M d, Y H:i') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->updated_at->format('M d, Y H:i') }}</dd>
                </div>
            </dl>

            @if ($school->logo)
                <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-800">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Logo</dt>
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::url($school->logo) }}"
                        alt="{{ $school->name }} logo"
                        class="mt-2 h-20 w-20 rounded-lg object-cover"
                    >
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
