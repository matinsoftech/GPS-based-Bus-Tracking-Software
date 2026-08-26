<x-app-layout page="overview">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Welcome back, {{ $user->name }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Here is the status of your assigned bus{{ $buses->count() === 1 ? '' : 'es' }}.
                </p>
            </div>
            <span class="text-theme-sm inline-flex w-fit items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <span class="h-2 w-2 rounded-full bg-success-500"></span>
                {{ now()->format('l, F j, Y') }}
            </span>
        </div>

        @if (! $driver || $buses->isEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No buses are assigned to your account yet. Please contact your school administrator.
                </p>
            </div>
        @else
            @php
                $totalStudents = $buses->sum('students_count');
                $checkedIn = $checkedInByBus->sum();
                $fleetBuses = collect($fleetMap['buses'] ?? []);
                $onlineCount = $fleetBuses->filter(fn ($bus) => ! empty($bus['is_online']))->count();
            @endphp

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4">
                <div class="flex min-h-[150px] flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/15">
                        <svg class="fill-brand-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.262 2.402c.706-.54 1.683-.218 2.074.502L14.12 9.25h4.13c1.795 0 3.25 1.455 3.25 3.25V15c0 1.054-.5 1.99-1.277 2.586A3.75 3.75 0 0116.5 22.75a3.75 3.75 0 01-3.722-5.164h-2.556a3.751 3.751 0 01-6.444 0A2.25 2.25 0 011.75 15.5v-5A2.25 2.25 0 014 8.25h1.5a.75.75 0 000-1.5H3.5a.75.75 0 010-1.5H10V3.25a.75.75 0 010-1.5h1.5c.414 0 .75.336.75.75v.75a.75.75 0 01-1.5 0V3.25a.75.75 0 00-.75-.75H8.75a.75.75 0 00-.488 1.902zM5.5 18.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5zm9.5 0a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5zM16.5 11h1.75a1.75 1.75 0 011.75 1.75V15a.75.75 0 01-.75.75h-2.75v-4.75z" fill=""/>
                        </svg>
                    </div>
                    <div class="mt-5">
                        <span class="text-sm text-gray-500 dark:text-gray-400">My Buses</span>
                        <h4 class="mt-2 text-3xl font-bold text-gray-800 dark:text-white/90">{{ $buses->count() }}</h4>
                    </div>
                </div>

                <div class="flex min-h-[150px] flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-success-50 dark:bg-success-500/15">
                        <svg class="fill-success-600 dark:fill-success-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.5 2.25C5.01572 2.25 3 4.26572 3 6.75V17.25C3 19.7343 5.01572 21.75 7.5 21.75H16.5C18.9843 21.75 21 19.7343 21 17.25V6.75C21 4.26572 18.9843 2.25 16.5 2.25H7.5ZM7.25 7.75C7.25 7.33579 7.58579 7 8 7H16C16.4142 7 16.75 7.33579 16.75 7.75C16.75 8.16421 16.4142 8.5 16 8.5H8C7.58579 8.5 7.25 8.16421 7.25 7.75ZM7.25 12C7.25 11.5858 7.58579 11.25 8 11.25H16C16.4142 11.25 16.75 11.5858 16.75 12C16.75 12.4142 16.4142 12.75 16 12.75H8C7.58579 12.75 7.25 12.4142 7.25 12ZM8 15.25C7.58579 15.25 7.25 15.5858 7.25 16C7.25 16.4142 7.58579 16.75 8 16.75H10.25C10.6642 16.75 11 16.4142 11 16C11 15.5858 10.6642 15.25 10.25 15.25H8Z" fill=""/>
                        </svg>
                    </div>
                    <div class="mt-5">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Assigned Students</span>
                        <h4 class="mt-2 text-3xl font-bold text-gray-800 dark:text-white/90">{{ $totalStudents }}</h4>
                    </div>
                </div>

                <div class="flex min-h-[150px] flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-warning-50 dark:bg-warning-500/15">
                        <svg class="fill-warning-600 dark:fill-warning-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 4.25C7.71979 4.25 4.25 7.71979 4.25 12C4.25 16.2802 7.71979 19.75 12 19.75C16.2802 19.75 19.75 16.2802 19.75 12C19.75 7.71979 16.2802 4.25 12 4.25ZM2.75 12C2.75 6.89201 6.89201 2.75 12 2.75C17.108 2.75 21.25 6.89201 21.25 12C21.25 17.108 17.108 21.25 12 21.25C6.89201 21.25 2.75 17.108 2.75 12ZM11.25 8C11.25 7.58579 11.5858 7.25 12 7.25C12.4142 7.25 12.75 7.58579 12.75 8V12C12.75 12.4142 12.4142 12.75 12 12.75C11.5858 12.75 11.25 12.4142 11.25 12V8ZM12 16.75C12.4142 16.75 12.75 16.4142 12.75 16C12.75 15.5858 12.4142 15.25 12 15.25C11.5858 15.25 11.25 15.5858 11.25 16C11.25 16.4142 11.5858 16.75 12 16.75Z" fill=""/>
                        </svg>
                    </div>
                    <div class="mt-5">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Checked In Today</span>
                        <h4 class="mt-2 text-3xl font-bold text-gray-800 dark:text-white/90">{{ $checkedIn }}</h4>
                        <span class="text-theme-xs mt-1 block text-gray-400 dark:text-gray-500">of {{ $totalStudents }} students</span>
                    </div>
                </div>

                <div class="flex min-h-[150px] flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                        <svg class="fill-gray-800 dark:fill-white/90" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.262 2.402c.706-.54 1.683-.218 2.074.502L14.12 9.25h4.13c1.795 0 3.25 1.455 3.25 3.25V15c0 1.054-.5 1.99-1.277 2.586A3.75 3.75 0 0116.5 22.75a3.75 3.75 0 01-3.722-5.164h-2.556a3.751 3.751 0 01-6.444 0A2.25 2.25 0 011.75 15.5v-5A2.25 2.25 0 014 8.25h1.5a.75.75 0 000-1.5H3.5a.75.75 0 010-1.5H10V3.25a.75.75 0 010-1.5h1.5c.414 0 .75.336.75.75v.75a.75.75 0 01-1.5 0V3.25a.75.75 0 00-.75-.75H8.75a.75.75 0 00-.488 1.902zM5.5 18.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5zm9.5 0a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5zM16.5 11h1.75a1.75 1.75 0 011.75 1.75V15a.75.75 0 01-.75.75h-2.75v-4.75z" fill=""/>
                        </svg>
                    </div>
                    <div class="mt-5">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Buses Online</span>
                        <h4 class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $onlineCount }}</h4>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-x-4 gap-y-6 xl:grid-cols-2 xl:gap-6">
                <div class="min-w-0">
                    @include('partials.fleet-map', [
                        'fleetMap' => $fleetMap,
                        'fleetMapRefreshUrl' => $fleetMapRefreshUrl,
                        'fleetMapCompact' => true,
                    ])
                </div>

                <div class="min-w-0 xl:mt-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-1">
                @foreach ($buses as $bus)
                    @php
                        $fleetBus = $fleetBuses->firstWhere('id', $bus->id);
                        $online = ! empty($fleetBus['is_online']);
                    @endphp
                    <div class="flex h-full flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div>
                                <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $bus->bus_number }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $bus->registration_number }}</p>
                            </div>
                            @if ($bus->status === 'Active')
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                            @elseif ($bus->status === 'Maintenance')
                                <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">Maintenance</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                            @endif
                        </div>

                        <dl class="mb-4 flex-1 space-y-1.5 text-sm">
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Route</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $bus->routes->pluck('name')->join(', ') ?: '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Capacity</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $bus->capacity }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Assigned Students</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $bus->students_count }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Checked In Today</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $checkedInByBus[$bus->id] ?? 0 }} / {{ $bus->students_count }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Live Status</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">
                                    @if ($online)
                                        <span class="inline-flex items-center gap-1.5 text-green-700 dark:text-green-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                            {{ number_format($fleetBus['speed'] ?? 0, 0) }} km/h
                                        </span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">No live signal</span>
                                    @endif
                                </dd>
                            </div>
                            @if ($fleetBus)
                                <div class="flex justify-between gap-3">
                                    <dt class="text-gray-500 dark:text-gray-400">Last Update</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white">
                                        {{ $fleetBus['last_updated_ago'] ?? (\Illuminate\Support\Carbon::parse($fleetBus['recorded_at'])->format('M d, H:i:s') ?? '—') }}
                                    </dd>
                                </div>
                            @endif
                        </dl>

                        <div class="grid grid-cols-2 gap-2">
                            <a
                                href="{{ route('attendance.index') }}"
                                class="rounded-lg bg-brand-500 px-4 py-2 text-center text-sm font-medium text-white hover:bg-brand-600"
                            >
                                Mark Attendance
                            </a>
                            <button
                                type="button"
                                x-on:click="focusBusOnMap({{ $fleetBus['latitude'] ?? 'null' }}, {{ $fleetBus['longitude'] ?? 'null' }})"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                            >
                                Track Bus
                            </button>
                        </div>
                    </div>
                @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

@if ($buses->isNotEmpty())
<script>
    function focusBusOnMap(lat, lng) {
        if (typeof window.fleetMapInstance === 'undefined' || !lat || !lng) return;
        window.fleetMapInstance.flyTo([lat, lng], 15, { duration: 0.8 });
    }
</script>
@endif
