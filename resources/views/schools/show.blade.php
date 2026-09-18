<x-app-layout page="school-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $school->name }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('schools.edit', $school) }}"
                    class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    Edit
                </a>
                <a href="{{ route('schools.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    Back to Schools
                </a>
            </div>
        </div>

        <div class="mb-6">
            @php
                $schoolTabs = [
                    [
                        'key' => 'overview',
                        'label' => 'Overview',
                        'url' => route('schools.show', $school),
                        'icon' => 'squares-2x2',
                    ],
                    [
                        'key' => 'principal',
                        'label' => 'School Admins',
                        'url' => route('schools.show', [$school, 'tab' => 'principal']),
                        'icon' => 'identification',
                        'count' => $totalSchoolAdmins,
                    ],
                    [
                        'key' => 'students',
                        'label' => 'Students',
                        'url' => route('schools.show', [$school, 'tab' => 'students']),
                        'icon' => 'academic-cap',
                        'count' => $totalStudents,
                    ],
                    [
                        'key' => 'drivers',
                        'label' => 'Drivers',
                        'url' => route('schools.show', [$school, 'tab' => 'drivers']),
                        'icon' => 'user-circle',
                        'count' => $totalDrivers,
                    ],
                    [
                        'key' => 'parents',
                        'label' => 'Parents',
                        'url' => route('schools.show', [$school, 'tab' => 'parents']),
                        'icon' => 'users',
                        'count' => $totalParents,
                    ],
                    [
                        'key' => 'buses',
                        'label' => 'Buses',
                        'url' => route('schools.show', [$school, 'tab' => 'buses']),
                        'icon' => 'truck',
                        'count' => $totalBuses,
                    ],
                    [
                        'key' => 'routes',
                        'label' => 'Routes',
                        'url' => route('schools.show', [$school, 'tab' => 'routes']),
                        'icon' => 'map',
                        'count' => $totalRoutes,
                    ],
                    [
                        'key' => 'subscriptions',
                        'label' => 'Subscriptions',
                        'url' => route('schools.show', [$school, 'tab' => 'subscriptions']),
                        'icon' => 'credit-card',
                        'count' => $totalSubscriptions,
                    ],
                    [
                        'key' => 'invoices',
                        'label' => 'Invoices',
                        'url' => route('schools.show', [$school, 'tab' => 'invoices']),
                        'icon' => 'document-text',
                        'count' => $totalInvoices,
                    ],
                    // ['key' => 'gps-devices', 'label' => 'GPS Devices', 'url' => route('schools.show', [$school, 'tab' => 'gps-devices']), 'icon' => 'signal', 'count' => $totalGpsDevices],
                    // ['key' => 'route-stops', 'label' => 'Route Stops', 'url' => route('schools.show', [$school, 'tab' => 'route-stops']), 'icon' => 'map-pin', 'count' => $totalStops],
                ];
            @endphp
            <x-pill-tabs :tabs="$schoolTabs" :active="$tab" />
        </div>

        @if ($tab === 'overview')
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
                    <div
                        class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex items-start justify-between">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $tile['label'] }}</p>
                            <x-heroicon-o-{{ $tile['icon'] }} class="h-5 w-5 text-brand-500" />
                        </div>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $tile['value'] }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $tile['sub'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Subscription --}}
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Subscription</h2>
                    <a href="{{ route('subscriptions.index') }}"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        Manage Subscriptions
                    </a>
                </div>

                @if ($activeSubscription)
                    @php
                        $sub = $activeSubscription;
                        $statusColors = [
                            'trialing' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
                            'active' => 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400',
                        ];
                    @endphp
                    <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Plan</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $sub->plan->name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Billing</dt>
                            <dd class="mt-1 text-sm capitalize text-gray-900 dark:text-white">{{ $sub->billing_cycle }}
                                · ₹{{ number_format($sub->amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $statusColors[$sub->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-400' }}">
                                    {{ ucfirst(str_replace('_', ' ', $sub->status)) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Expires</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $sub->ends_at ? $sub->ends_at->format('M d, Y') : '—' }}</dd>
                        </div>
                    </div>
                @else
                    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            This school does not have an active subscription yet.
                        </p>
                        <a href="{{ route('subscriptions.create', ['school' => $school->id]) }}"
                            class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                            Assign Plan
                        </a>
                    </div>
                @endif
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
                                @if ($school->status === 'active') bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400
                                @elseif ($school->status === 'inactive')
                                    bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400
                                @else
                                    bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400 @endif
                            ">
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
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->principal_name ?? '—' }}
                        </dd>
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
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $school->created_at->format('M d, Y H:i') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $school->updated_at->format('M d, Y H:i') }}</dd>
                    </div>
                </dl>

                @if ($school->logo)
                    <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Logo</dt>
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($school->logo) }}"
                            alt="{{ $school->name }} logo" class="mt-2 h-20 w-20 rounded-lg object-cover">
                    </div>
                @endif
            </div>
        @elseif ($tab === 'principal')
            {{-- <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2
                    class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                    Principal
                </h2>

                <dl class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Principal Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->principal_name ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->phone ?? '—' }}</dd>
                    </div>
                </dl>
            </div> --}}

            <div
                class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">School Admins</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $schoolAdmins->total() }}
                        admin{{ $schoolAdmins->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 dark:border-gray-800">
                            <tr class="text-gray-500 dark:text-gray-400">
                                <th class="px-5 py-3 font-medium">Name</th>
                                <th class="px-5 py-3 font-medium">Email</th>
                                <th class="px-5 py-3 font-medium">Designation</th>
                                <th class="px-5 py-3 font-medium">Phone</th>
                                <th class="px-5 py-3 font-medium">Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($schoolAdmins as $admin)
                                <tr class="text-gray-700 dark:text-gray-200">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('school-admins.show', $admin) }}"
                                            class="font-medium text-brand-600 hover:underline dark:text-brand-400">
                                            {{ $admin->name ?: $admin->user?->name }}
                                        </a>
                                    </td>
                                    <td class="px-5 py-3">{{ $admin->user?->email ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $admin->designation ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $admin->phone ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $admin->address ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                        No school admins found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-800">
                    {{ $schoolAdmins->links() }}
                </div>
            </div>
        @elseif ($tab === 'students')
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Students</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $students->total() }}
                        student{{ $students->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 dark:border-gray-800">
                            <tr class="text-gray-500 dark:text-gray-400">
                                <th class="px-5 py-3 font-medium">Admission No</th>
                                <th class="px-5 py-3 font-medium">Name</th>
                                <th class="px-5 py-3 font-medium">Grade / Section</th>
                                <th class="px-5 py-3 font-medium">Parent</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($students as $student)
                                <tr class="text-gray-700 dark:text-gray-200">
                                    <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">
                                        {{ $student->admission_no }}</td>
                                    <td class="px-5 py-3">
                                        <a href="{{ route('students.show', $student) }}"
                                            class="font-medium text-brand-600 hover:underline dark:text-brand-400">
                                            {{ $student->full_name }}
                                        </a>
                                    </td>
                                    <td class="px-5 py-3">{{ trim($student->grade . ' ' . $student->section) }}</td>
                                    <td class="px-5 py-3">
                                        @if ($student->parent)
                                            <a href="{{ route('parents.show', $student->parent) }}"
                                                class="font-medium text-brand-600 hover:underline dark:text-brand-400">
                                                {{ $student->parent->user?->name ?? $student->parent->name }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @if ($student->is_active)
                                            <span
                                                class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                                        @else
                                            <span
                                                class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                        No students found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-800">
                    {{ $students->links() }}
                </div>
            </div>
        @elseif ($tab === 'drivers')
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Drivers</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $drivers->total() }}
                        driver{{ $drivers->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 dark:border-gray-800">
                            <tr class="text-gray-500 dark:text-gray-400">
                                <th class="px-5 py-3 font-medium">Name</th>
                                <th class="px-5 py-3 font-medium">Phone</th>
                                <th class="px-5 py-3 font-medium">License No</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($drivers as $driver)
                                <tr class="text-gray-700 dark:text-gray-200">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('drivers.show', $driver) }}"
                                            class="font-medium text-brand-600 hover:underline dark:text-brand-400">
                                            {{ $driver->full_name ?: $driver->user?->name }}
                                        </a>
                                    </td>
                                    <td class="px-5 py-3">{{ $driver->phone ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $driver->license_number ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        @if ($driver->status === 'Active')
                                            <span
                                                class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                                        @else
                                            <span
                                                class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $driver->status ?? '—' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                        No drivers found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-800">
                    {{ $drivers->links() }}
                </div>
            </div>
        @elseif ($tab === 'parents')
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Parents</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $parents->total() }}
                        parent{{ $parents->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 dark:border-gray-800">
                            <tr class="text-gray-500 dark:text-gray-400">
                                <th class="px-5 py-3 font-medium">Name</th>
                                <th class="px-5 py-3 font-medium">Email</th>
                                <th class="px-5 py-3 font-medium">Phone</th>
                                <th class="px-5 py-3 font-medium">Children</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($parents as $parent)
                                <tr class="text-gray-700 dark:text-gray-200">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('parents.show', $parent) }}"
                                            class="font-medium text-brand-600 hover:underline dark:text-brand-400">
                                            {{ $parent->user?->name ?? $parent->name }}
                                        </a>
                                    </td>
                                    <td class="px-5 py-3">{{ $parent->user?->email ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $parent->phone ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $parent->children->count() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                        No parents found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-800">
                    {{ $parents->links() }}
                </div>
            </div>
        @elseif ($tab === 'buses')
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Buses</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $buses->total() }}
                        bus{{ $buses->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 dark:border-gray-800">
                            <tr class="text-gray-500 dark:text-gray-400">
                                <th class="px-5 py-3 font-medium">Bus Number</th>
                                <th class="px-5 py-3 font-medium">Make / Model</th>
                                <th class="px-5 py-3 font-medium">Capacity</th>
                                <th class="px-5 py-3 font-medium">GPS</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($buses as $bus)
                                <tr class="text-gray-700 dark:text-gray-200">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('buses.show', $bus) }}"
                                            class="font-medium text-brand-600 hover:underline dark:text-brand-400">
                                            {{ $bus->bus_number }}
                                        </a>
                                    </td>
                                    <td class="px-5 py-3">{{ trim($bus->make . ' ' . $bus->model) ?: '—' }}</td>
                                    <td class="px-5 py-3">{{ $bus->capacity ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $bus->gpsDevice?->device_name ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        @if ($bus->status === 'Active')
                                            <span
                                                class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                                        @elseif ($bus->status === 'Maintenance')
                                            <span
                                                class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">Maintenance</span>
                                        @else
                                            <span
                                                class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $bus->status ?? '—' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                        No buses found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-800">
                    {{ $buses->links() }}
                </div>
            </div>
        @elseif ($tab === 'routes')
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Routes</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $routes->total() }}
                        route{{ $routes->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 dark:border-gray-800">
                            <tr class="text-gray-500 dark:text-gray-400">
                                <th class="px-5 py-3 font-medium">Name</th>
                                <th class="px-5 py-3 font-medium">Code</th>
                                <th class="px-5 py-3 font-medium">Type</th>
                                <th class="px-5 py-3 font-medium">Stops</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($routes as $route)
                                <tr class="text-gray-700 dark:text-gray-200">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('routes.show', $route) }}"
                                            class="font-medium text-brand-600 hover:underline dark:text-brand-400">
                                            {{ $route->name }}
                                        </a>
                                    </td>
                                    <td class="px-5 py-3">{{ $route->route_code ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs font-medium {{ $route->route_type_color_classes }}">
                                            {{ $route->route_type_label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">{{ $route->stops_count }}</td>
                                    <td class="px-5 py-3">
                                        @if ($route->is_active)
                                            <span
                                                class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                                        @else
                                            <span
                                                class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                        No routes found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-800">
                    {{ $routes->links() }}
                </div>
            </div>
        @elseif ($tab === 'subscriptions')
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Subscriptions</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $subscriptions->total() }}
                        subscription{{ $subscriptions->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 dark:border-gray-800">
                            <tr class="text-gray-500 dark:text-gray-400">
                                <th class="px-5 py-3 font-medium">Plan</th>
                                <th class="px-5 py-3 font-medium">Billing</th>
                                <th class="px-5 py-3 font-medium">Amount</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 font-medium">Created</th>
                                <th class="px-5 py-3 font-medium">Expires</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($subscriptions as $subscription)
                                @php
                                    $subStatusColors = [
                                        'trialing' =>
                                            'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
                                        'active' =>
                                            'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400',
                                    ];
                                @endphp
                                <tr class="text-gray-700 dark:text-gray-200">
                                    <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">
                                        {{ $subscription->plan?->name ?? '—' }}</td>
                                    <td class="px-5 py-3 capitalize">{{ $subscription->billing_cycle ?? '—' }}</td>
                                    <td class="px-5 py-3">₹{{ number_format((float) $subscription->amount, 2) }}</td>
                                    <td class="px-5 py-3">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $subStatusColors[$subscription->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-400' }}">
                                            {{ ucfirst(str_replace('_', ' ', $subscription->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">{{ $subscription->created_at?->format('M d, Y') ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        {{ $subscription->ends_at ? $subscription->ends_at->format('M d, Y') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                        No subscriptions found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-800">
                    {{ $subscriptions->links() }}
                </div>
            </div>
        @elseif ($tab === 'invoices')
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Invoices</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $invoices->total() }}
                        invoice{{ $invoices->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 dark:border-gray-800">
                            <tr class="text-gray-500 dark:text-gray-400">
                                <th class="px-5 py-3 font-medium">Invoice No</th>
                                <th class="px-5 py-3 font-medium">Plan</th>
                                <th class="px-5 py-3 font-medium">Amount</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 font-medium">Issued</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($invoices as $invoice)
                                @php
                                    $invStatusColors = [
                                        'paid' =>
                                            'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400',
                                        'unpaid' =>
                                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400',
                                        'overdue' => 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400',
                                        'void' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                    ];
                                @endphp
                                <tr class="text-gray-700 dark:text-gray-200">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('invoices.show', $invoice) }}"
                                            class="font-medium text-brand-600 hover:underline dark:text-brand-400">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="px-5 py-3">{{ $invoice->plan?->name ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        {{ $invoice->currency ?? '₹' }}{{ number_format((float) $invoice->amount, 2) }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $invStatusColors[$invoice->statusLabel()] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-400' }}">
                                            {{ ucfirst($invoice->statusLabel()) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">{{ $invoice->issued_at?->format('M d, Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                        No invoices found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-800">
                    {{ $invoices->links() }}
                </div>
            </div>
        @elseif ($tab === 'gps-devices')
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">GPS Devices</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $gpsDevices->total() }}
                        device{{ $gpsDevices->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 dark:border-gray-800">
                            <tr class="text-gray-500 dark:text-gray-400">
                                <th class="px-5 py-3 font-medium">Device</th>
                                <th class="px-5 py-3 font-medium">IMEI</th>
                                <th class="px-5 py-3 font-medium">Bus</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($gpsDevices as $device)
                                <tr class="text-gray-700 dark:text-gray-200">
                                    <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">
                                        {{ $device->device_name }}</td>
                                    <td class="px-5 py-3">{{ $device->device_imei ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        @if ($device->bus)
                                            <a href="{{ route('buses.show', $device->bus) }}"
                                                class="font-medium text-brand-600 hover:underline dark:text-brand-400">
                                                {{ $device->bus->bus_number }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @if ($device->status === 'active')
                                            <span
                                                class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                                        @else
                                            <span
                                                class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $device->status ?? '—' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                        No GPS devices found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-800">
                    {{ $gpsDevices->links() }}
                </div>
            </div>
        @elseif ($tab === 'route-stops')
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Route Stops</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $routeStops->total() }}
                        stop{{ $routeStops->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 dark:border-gray-800">
                            <tr class="text-gray-500 dark:text-gray-400">
                                <th class="px-5 py-3 font-medium">Name</th>
                                <th class="px-5 py-3 font-medium">Route</th>
                                <th class="px-5 py-3 font-medium">Order</th>
                                <th class="px-5 py-3 font-medium">Pickup</th>
                                <th class="px-5 py-3 font-medium">Drop</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($routeStops as $stop)
                                <tr class="text-gray-700 dark:text-gray-200">
                                    <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">
                                        {{ $stop->name }}</td>
                                    <td class="px-5 py-3">
                                        <a href="{{ route('routes.show', $stop->route) }}"
                                            class="text-brand-600 hover:underline dark:text-brand-400">
                                            {{ $stop->route?->name ?? '—' }}
                                        </a>
                                    </td>
                                    <td class="px-5 py-3">{{ $stop->stop_order ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $stop->pickup_time ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $stop->drop_time ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                        No route stops found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-800">
                    {{ $routeStops->links() }}
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
