<x-app-layout page="overview">
    @php
        $bar = fn ($count, $total) => $total > 0 ? round(($count / $total) * 100) : 0;
    @endphp

    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ $school?->name ?? 'Dashboard' }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Welcome back, {{ $user->name }}. Here is the transport overview for today.
                </p>
            </div>
            <span class="text-theme-sm inline-flex w-fit items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <span class="h-2 w-2 rounded-full bg-success-500"></span>
                {{ now()->format('l, F j, Y') }}
            </span>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4">
            <div class="flex min-h-[150px] flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/15">
                    <svg class="fill-brand-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.46447 3.46447C4.92893 2 7.07107 2 10 2H14C16.9289 2 19.0711 2 20.5355 3.46447C22 4.92893 22 7.07107 22 10V14C22 16.9289 22 19.0711 20.5355 20.5355C19.0711 22 16.9289 22 14 22H10C7.07107 22 4.92893 22 3.46447 20.5355C2 19.0711 2 16.9289 2 14V10C2 7.07107 2 4.92893 3.46447 3.46447ZM4 10C4 7.17157 4 5.75736 4.87868 4.87868C5.75736 4 7.17157 4 10 4H14C16.8284 4 18.2426 4 19.1213 4.87868C20 5.75736 20 7.17157 20 10V14C20 16.8284 20 18.2426 19.1213 19.1213C18.2426 20 16.8284 20 14 20H10C7.17157 20 5.75736 20 4.87868 19.1213C4 18.2426 4 16.8284 4 14V10ZM12.75 8C12.75 7.58579 12.4142 7.25 12 7.25C11.5858 7.25 11.25 7.58579 11.25 8V11.25H8C7.58579 11.25 7.25 11.5858 7.25 12C7.25 12.4142 7.58579 12.75 8 12.75H11.25V16C11.25 16.4142 11.5858 16.75 12 16.75C12.4142 16.75 12.75 16.4142 12.75 16V12.75H16C16.4142 12.75 16.75 12.4142 16.75 12C16.75 11.5858 16.4142 11.25 16 11.25H12.75V8Z" fill=""/>
                    </svg>
                </div>
                <div class="mt-5 flex items-end justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Buses</span>
                        <h4 class="mt-2 text-3xl font-bold text-gray-800 dark:text-white/90">{{ $totalBuses }}</h4>
                    </div>
                    <a href="{{ route('buses.index') }}" class="text-theme-xs font-medium text-brand-500 hover:text-brand-600">View all</a>
                </div>
            </div>

            <div class="flex min-h-[150px] flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-success-50 dark:bg-success-500/15">
                    <svg class="fill-success-600 dark:fill-success-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.25 12C2.25 6.61522 6.61522 2.25 12 2.25C17.3848 2.25 21.75 6.61522 21.75 12C21.75 17.3848 17.3848 21.75 12 21.75C6.61522 21.75 2.25 17.3848 2.25 12ZM12 3.75C7.44365 3.75 3.75 7.44365 3.75 12C3.75 16.5563 7.44365 20.25 12 20.25C16.5563 20.25 20.25 16.5563 20.25 12C20.25 7.44365 16.5563 3.75 12 3.75ZM15.5303 9.71967C15.8232 10.0126 15.8232 10.4874 15.5303 10.7803L11.0303 15.2803C10.7374 15.5732 10.2626 15.5732 9.96967 15.2803L8.46967 13.7803C8.17678 13.4874 8.17678 13.0126 8.46967 12.7197C8.76256 12.4268 9.23744 12.4268 9.53033 12.7197L10.5 13.6893L14.4697 9.71967C14.7626 9.42678 15.2374 9.42678 15.5303 9.71967Z" fill=""/>
                    </svg>
                </div>
                <div class="mt-5 flex items-end justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Active Buses</span>
                        <h4 class="mt-2 text-3xl font-bold text-gray-800 dark:text-white/90">{{ $activeBuses }}</h4>
                    </div>
                    <span class="flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-sm font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                        {{ $bar($activeBuses, $totalBuses) }}%
                    </span>
                </div>
            </div>

            <div class="flex min-h-[150px] flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-warning-50 dark:bg-warning-500/15">
                    <svg class="fill-warning-600 dark:fill-warning-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.94499 4.41572C10.7734 3.06667 12.7258 3.07634 13.5408 4.43298L21.2763 16.7068C22.0518 18C21.3943 19.75 19.849 20.5 18.5986 20.5H5.40343C4.15309 20.5 2.60483 19.7527 3.38358 18.4652L9.94499 4.41572ZM12.0031 4.9375C11.8346 4.9375 11.6787 5.02678 11.5904 5.17413L5.03025 19.2215C4.94023 19.3716 5.04583 19.537 5.21229 19.537H18.789C18.9555 19.537 19.0611 19.3716 18.971 19.2215L12.411 5.17413C12.3214 5.0248 12.1575 4.9375 12.0031 4.9375ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13.5C12.75 13.9142 12.4142 14.25 12 14.25C11.5858 14.25 11.25 13.9142 11.25 13.5V9C11.25 8.58579 11.5858 8.25 12 8.25ZM11.25 17C11.25 16.5858 11.5858 16.25 12 16.25H12.01C12.4242 16.25 12.76 16.5858 12.76 17C12.76 17.4142 12.4242 17.75 12.01 17.75H12C11.5858 17.75 11.25 17.4142 11.25 17Z" fill=""/>
                    </svg>
                </div>
                <div class="mt-5 flex items-end justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Buses in Maintenance</span>
                        <h4 class="mt-2 text-3xl font-bold text-gray-800 dark:text-white/90">{{ $maintenanceBuses }}</h4>
                    </div>
                    <span class="flex items-center gap-1 rounded-full bg-warning-50 px-2 py-0.5 text-sm font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">
                        {{ $bar($maintenanceBuses, $totalBuses) }}%
                    </span>
                </div>
            </div>

            <div class="flex min-h-[150px] flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                    <svg class="fill-gray-800 dark:fill-white/90" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M10 4.25C8.067 4.25 6.5 5.817 6.5 7.75C6.5 9.683 8.067 11.25 10 11.25C11.933 11.25 13.5 9.683 13.5 7.75C13.5 5.817 11.933 4.25 10 4.25ZM5 7.75C5 5.40279 6.90279 3.5 10 3.5C13.0972 3.5 15 5.40279 15 7.75C15 10.0972 13.0972 12 10 12C6.90279 12 5 10.0972 5 7.75ZM3.5 16.75C3.5 14.6789 5.17893 13 7.25 13H12.75C14.8211 13 16.5 14.6789 16.5 16.75V17.25C16.5 17.6642 16.1642 18 15.75 18C15.3358 18 15 17.6642 15 17.25V16.75C15 15.5074 13.9926 14.5 12.75 14.5H7.25C6.00736 14.5 5 15.5074 5 16.75V17.25C5 17.6642 4.66421 18 4.25 18C3.83579 18 3.5 17.6642 3.5 17.25V16.75ZM18.75 4.25C18.3358 4.25 18 4.58579 18 5V5.25C18 5.66421 18.3358 6 18.75 6C19.1642 6 19.5 5.66421 19.5 5.25V5C19.5 4.58579 19.1642 4.25 18.75 4.25ZM16.5 5.25C16.5 4.00736 17.5074 3 18.75 3C19.9926 3 21 4.00736 21 5.25C21 6.17854 20.4192 6.97421 19.6075 7.30776C19.7659 7.8956 19.85 8.51369 19.85 9.15C19.85 9.56421 19.5142 9.9 19.1 9.9C18.6858 9.9 18.35 9.56421 18.35 9.15C18.35 8.39458 18.2426 7.66218 18.0463 6.96738C17.7942 6.39555 17.2664 6 16.6739 6H16.5C16.0858 6 15.75 5.66421 15.75 5.25C15.75 5.25 15.75 5.25 16.5 5.25ZM20.5 12C20.9142 12 21.25 12.3358 21.25 12.75V13.25C21.25 13.6642 20.9142 14 20.5 14C20.0858 14 19.75 13.6642 19.75 13.25V12.75C19.75 12.3358 20.0858 12 20.5 12ZM20.5 16.75C20.9142 16.75 21.25 17.0858 21.25 17.5V17.75C21.25 18.1642 20.9142 18.5 20.5 18.5C20.0858 18.5 19.75 18.1642 19.75 17.75V17.5C19.75 17.0858 20.0858 16.75 20.5 16.75Z" fill=""/>
                    </svg>
                </div>
                <div class="mt-5 flex items-end justify-between">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Drivers</span>
                        <h4 class="mt-2 text-3xl font-bold text-gray-800 dark:text-white/90">{{ $totalDrivers }}</h4>
                    </div>
                    <a href="{{ route('drivers.index') }}" class="text-theme-xs font-medium text-brand-500 hover:text-brand-600">View all</a>
                </div>
            </div>
        </div>

        @include('partials.fleet-map', [
            'fleetMap' => $fleetMap,
            'fleetMapRefreshUrl' => route('principal.dashboard.fleet-data'),
            'fleetMapShowCards' => false,
        ])

        <div class="mt-6 grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-7">
                <div class="flex h-full min-h-[460px] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Fleet Status</h3>
                            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                                Breakdown of your bus fleet
                            </p>
                        </div>
                        <a href="{{ route('buses.index') }}" class="text-theme-sm font-medium text-brand-500 hover:text-brand-600">View all</a>
                    </div>

                    @if ($totalBuses > 0)
                        <div class="mt-6 flex h-3 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            @if ($activeBuses > 0)
                                <div class="bg-success-500" style="width: {{ $bar($activeBuses, $totalBuses) }}%"></div>
                            @endif
                            @if ($maintenanceBuses > 0)
                                <div class="bg-warning-500" style="width: {{ $bar($maintenanceBuses, $totalBuses) }}%"></div>
                            @endif
                            @if ($inactiveBuses > 0)
                                <div class="bg-gray-400 dark:bg-gray-600" style="width: {{ $bar($inactiveBuses, $totalBuses) }}%"></div>
                            @endif
                        </div>

                        <div class="mt-4 flex flex-wrap gap-6">
                            <span class="flex items-center gap-2 text-theme-sm text-gray-500 dark:text-gray-400">
                                <span class="h-2.5 w-2.5 rounded-full bg-success-500"></span>
                                Active ({{ $activeBuses }})
                            </span>
                            <span class="flex items-center gap-2 text-theme-sm text-gray-500 dark:text-gray-400">
                                <span class="h-2.5 w-2.5 rounded-full bg-warning-500"></span>
                                Maintenance ({{ $maintenanceBuses }})
                            </span>
                            <span class="flex items-center gap-2 text-theme-sm text-gray-500 dark:text-gray-400">
                                <span class="h-2.5 w-2.5 rounded-full bg-gray-400 dark:bg-gray-600"></span>
                                Inactive ({{ $inactiveBuses }})
                            </span>
                        </div>

                        <div class="mt-5 min-h-0 flex-1 overflow-auto custom-scrollbar">
                            <table class="w-full text-left text-sm">
                                <thead class="border-b border-gray-200 text-theme-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                    <tr>
                                        <th class="pb-3 font-medium">Bus</th>
                                        <th class="pb-3 font-medium">Status</th>
                                        <th class="pb-3 font-medium">Drivers</th>
                                        <th class="pb-3 text-right font-medium">Capacity</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @forelse ($fleet as $bus)
                                        <tr class="text-gray-700 dark:text-gray-200">
                                            <td class="py-3.5">
                                                <a href="{{ route('buses.show', $bus) }}" class="font-medium text-gray-800 hover:text-brand-500 dark:text-white/90">
                                                    {{ $bus->bus_number }}
                                                </a>
                                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $bus->registration_number }}</p>
                                            </td>
                                            <td class="py-3.5">
                                                @if ($bus->status === 'Active')
                                                    <span class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Active</span>
                                                @elseif ($bus->status === 'Maintenance')
                                                    <span class="rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">Maintenance</span>
                                                @else
                                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="py-3.5">
                                                @if ($bus->drivers->isNotEmpty())
                                                    <span class="text-theme-xs inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                        {{ $bus->drivers->first()?->full_name }}
                                                    </span>
                                                @else
                                                    <span class="text-theme-xs text-gray-400 dark:text-gray-500">No driver</span>
                                                @endif
                                            </td>
                                            <td class="py-3.5 text-right text-theme-sm">{{ $bus->capacity }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                                No buses in your fleet yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-8 text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No buses in your fleet yet.</p>
                            <a href="{{ route('buses.create') }}" class="mt-3 inline-flex rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                                Add your first bus
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-span-12 xl:col-span-5">
                <div class="flex h-full min-h-[460px] flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Needs Attention</h3>
                        <span class="rounded-full bg-error-50 px-2.5 py-1 text-theme-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                            {{ $expiringBuses->count() + $suspendedDrivers->count() }} alerts
                        </span>
                    </div>

                    <div class="mt-5 min-h-0 flex-1 space-y-4 overflow-y-auto custom-scrollbar pr-1">
                        @forelse ($expiringBuses as $bus)
                            <div class="flex items-start gap-3 rounded-xl border border-error-100 bg-error-50/50 p-3 dark:border-error-500/20 dark:bg-error-500/5">
                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-error-100 text-error-600 dark:bg-error-500/15 dark:text-error-500">
                                    <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 1.25C4.27208 1.25 1.25 4.27208 1.25 8C1.25 11.7279 4.27208 14.75 8 14.75C11.7279 14.75 14.75 11.7279 14.75 8C14.75 4.27208 11.7279 1.25 8 1.25ZM8.75 5C8.75 4.58579 8.41421 4.25 8 4.25C7.58579 4.25 7.25 4.58579 7.25 5V8C7.25 8.41421 7.58579 8.75 8 8.75C8.41421 8.75 8.75 8.41421 8.75 8V5ZM8 11.5C8.41421 11.5 8.75 11.1642 8.75 10.75C8.75 10.3358 8.41421 10 8 10C7.58579 10 7.25 10.3358 7.25 10.75C7.25 11.1642 7.58579 11.5 8 11.5Z" fill=""/>
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                        Insurance expiring for {{ $bus->bus_number }}
                                    </p>
                                    <p class="text-theme-xs mt-0.5 text-gray-500 dark:text-gray-400">
                                        {{ $bus->insurance_expiry_date->format('M d, Y') }} · {{ $bus->insurance_expiry_date->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-success-100 bg-success-50/50 p-3 text-theme-sm text-success-600 dark:border-success-500/20 dark:bg-success-500/5 dark:text-success-500">
                                No insurance expiring within the next 2 months.
                            </div>
                        @endforelse

                        @forelse ($suspendedDrivers as $driver)
                            <div class="flex items-start gap-3 rounded-xl border border-warning-100 bg-warning-50/50 p-3 dark:border-warning-500/20 dark:bg-warning-500/5">
                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-warning-100 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">
                                    <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8 2C7.17157 2 6.5 2.67157 6.5 3.5V4.25H9.5V3.5C9.5 2.67157 8.82843 2 8 2ZM11 4.25V3.5C11 1.84315 9.65685 0.5 8 0.5C6.34315 0.5 5 1.84315 5 3.5V4.25H4.25C3.00736 4.25 2 5.25736 2 6.5V12.5C2 13.7426 3.00736 14.75 4.25 14.75H11.75C12.9926 14.75 14 13.7426 14 12.5V6.5C14 5.25736 12.9926 4.25 11.75 4.25H11ZM3.5 6.5C3.5 6.08579 3.83579 5.75 4.25 5.75H11.75C12.1642 5.75 12.5 6.08579 12.5 6.5V12.5C12.5 12.9142 12.1642 13.25 11.75 13.25H4.25C3.83579 13.25 3.5 12.9142 3.5 12.5V6.5Z" fill=""/>
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ $driver->full_name }} is suspended
                                    </p>
                                    <p class="text-theme-xs mt-0.5 text-gray-500 dark:text-gray-400">
                                        {{ $driver->employee_id }} · {{ $driver->license_number }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            @if ($expiringBuses->isEmpty())
                                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Everything looks good.</p>
                            @endif
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-7">
                <div class="flex h-full min-h-[460px] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Routes</h3>
                            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                                {{ $activeRoutes }} of {{ $totalRoutes }} routes active
                            </p>
                        </div>
                        <a href="{{ route('routes.index') }}" class="text-theme-sm font-medium text-brand-500 hover:text-brand-600">View all</a>
                    </div>

                    <div class="mt-5 min-h-0 flex-1 overflow-auto custom-scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-gray-200 text-theme-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="pb-3 font-medium">Route</th>
                                    <th class="pb-3 font-medium">Driver</th>
                                    <th class="pb-3 font-medium">Stops</th>
                                    <th class="pb-3 text-right font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse ($upcomingRoutes as $route)
                                    <tr class="text-gray-700 dark:text-gray-200">
                                        <td class="py-3.5">
                                            <a href="{{ route('routes.show', $route) }}" class="font-medium text-gray-800 hover:text-brand-500 dark:text-white/90">
                                                {{ $route->name }}
                                            </a>
                                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                                {{ $route->start_location }} → {{ $route->end_location }}
                                            </p>
                                        </td>
                                        <td class="py-3.5 text-theme-sm">{{ $route->buses->first()?->drivers->first()?->full_name ?? 'Not assigned' }}</td>
                                        <td class="py-3.5 text-theme-sm">{{ $route->stops->count() }}</td>
                                        <td class="py-3.5 text-right">
                                            @if ($route->is_active)
                                                <span class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Active</span>
                                            @else
                                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                            No routes defined yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-span-12 xl:col-span-5">
                <div class="flex h-full min-h-[460px] flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">School Snapshot</h3>
                            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                                Enrolled students and staffing
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 min-h-0 flex-1 overflow-y-auto custom-scrollbar pr-1">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/[0.03]">
                                <span class="text-theme-sm text-gray-500 dark:text-gray-400">Students</span>
                                <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $totalStudents }}</h4>
                                <a href="{{ route('students.index') }}" class="text-theme-xs mt-1 inline-block font-medium text-brand-500 hover:text-brand-600">View all</a>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/[0.03]">
                                <span class="text-theme-sm text-gray-500 dark:text-gray-400">Active Drivers</span>
                                <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $activeDrivers }}</h4>
                                <a href="{{ route('drivers.index') }}" class="text-theme-xs mt-1 inline-block font-medium text-brand-500 hover:text-brand-600">View all</a>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/[0.03]">
                                <span class="text-theme-sm text-gray-500 dark:text-gray-400">Active Routes</span>
                                <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $activeRoutes }}</h4>
                                <a href="{{ route('routes.index') }}" class="text-theme-xs mt-1 inline-block font-medium text-brand-500 hover:text-brand-600">View all</a>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/[0.03]">
                                <span class="text-theme-sm text-gray-500 dark:text-gray-400">Bus Coverage</span>
                                <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                                    {{ $bar($activeBuses, max($totalBuses, 1)) }}%
                                </h4>
                                <span class="text-theme-xs mt-1 block text-gray-500 dark:text-gray-400">of fleet active</span>
                            </div>
                        </div>

                        <div class="mt-5 rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                            <h4 class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">Quick Actions</h4>
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <a href="{{ route('buses.create') }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-center text-theme-sm font-medium text-white hover:bg-brand-600">
                                    Add Bus
                                </a>
                                <a href="{{ route('drivers.create') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-center text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                    Add Driver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
