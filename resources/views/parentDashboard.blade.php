<x-app-layout page="overview">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Welcome back, {{ $user->name }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Here is the transport status for your children.
                </p>
            </div>
            <span class="text-theme-sm inline-flex w-fit items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <span class="h-2 w-2 rounded-full bg-success-500"></span>
                {{ now()->format('l, F j, Y') }}
            </span>
        </div>

        @if (! $parent || $children->isEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No children are linked to your account yet. Please contact your school.
                </p>
            </div>
        @else
            @php
                $usedRouteIds = $children->pluck('route_id')->filter()->unique();
                $onlineCount = $locationsByBus->filter(fn ($l) => $l->recorded_at?->gt(now()->subMinutes(10)))->count();
                $pickedUp = $attendanceByStudent->filter(fn ($records) => $records->contains(
                    fn ($a) => $a->trip === \App\Models\Attendance::TRIP_HOME_TO_SCHOOL && $a->check_in_at
                ))->count();
            @endphp

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4">
                <div class="flex min-h-[150px] flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/15">
                        <svg class="fill-brand-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.94499 4.41572C10.7734 3.06667 12.7258 3.07634 13.5408 4.43298L21.2763 16.7068C22.0518 18 21.3943 19.75 19.849 20.5 18.5986 20.5H5.40343C4.15309 20.5 2.60483 19.7527 3.38358 18.4652L9.94499 4.41572ZM12.0031 4.9375C11.8346 4.9375 11.6787 5.02678 11.5904 5.17413L5.03025 19.2215C4.94023 19.3716 5.04583 19.537 5.21229 19.537H18.789C18.9555 19.537 19.0611 19.3716 18.971 19.2215L12.411 5.17413C12.3214 5.0248 12.1575 4.9375 12.0031 4.9375ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13.5C12.75 13.9142 12.4142 14.25 12 14.25C11.5858 14.25 11.25 13.9142 11.25 13.5V9C11.25 8.58579 11.5858 8.25 12 8.25ZM11.25 17C11.25 16.5858 11.5858 16.25 12 16.25H12.01C12.4242 16.25 12.76 16.5858 12.76 17C12.76 17.4142 12.4242 17.75 12.01 17.75H12C11.5858 17.75 11.25 17.4142 11.25 17Z" fill=""/>
                        </svg>
                    </div>
                    <div class="mt-5 flex items-end justify-between">
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">My Children</span>
                            <h4 class="mt-2 text-3xl font-bold text-gray-800 dark:text-white/90">{{ $children->count() }}</h4>
                        </div>
                        <a href="{{ route('parent.children') }}" class="text-theme-xs font-medium text-brand-500 hover:text-brand-600">View all</a>
                    </div>
                </div>

                <div class="flex min-h-[150px] flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-success-50 dark:bg-success-500/15">
                        <svg class="fill-success-600 dark:fill-success-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.25C6.06294 1.25 1.25 6.06294 1.25 12C1.25 17.9371 6.06294 22.75 12 22.75C17.9371 22.75 22.75 17.9371 22.75 12C22.75 6.06294 17.9371 1.25 12 1.25ZM3.75 12C3.75 7.44365 7.44365 3.75 12 3.75C16.5563 3.75 20.25 7.44365 20.25 12C20.25 16.5563 16.5563 20.25 12 20.25C7.44365 20.25 3.75 16.5563 3.75 12ZM12.75 8C12.75 7.58579 12.4142 7.25 12 7.25C11.5858 7.25 11.25 7.58579 11.25 8V12.75H8C7.58579 12.75 7.25 13.0858 7.25 13.5C7.25 13.9142 7.58579 14.25 8 14.25H11.25V18C11.25 18.4142 11.5858 18.75 12 18.75C12.4142 18.75 12.75 18.4142 12.75 18V14.25H16C16.4142 14.25 16.75 13.9142 16.75 13.5C16.75 13.0858 16.4142 12.75 16 12.75H12.75V8Z" fill=""/>
                        </svg>
                    </div>
                    <div class="mt-5 flex items-end justify-between">
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Routes In Use</span>
                            <h4 class="mt-2 text-3xl font-bold text-gray-800 dark:text-white/90">{{ $usedRouteIds->count() }}</h4>
                        </div>
                    </div>
                </div>

                <div class="flex min-h-[150px] flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-warning-50 dark:bg-warning-500/15">
                        <svg class="fill-warning-600 dark:fill-warning-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 4.25C7.71979 4.25 4.25 7.71979 4.25 12C4.25 16.2802 7.71979 19.75 12 19.75C16.2802 19.75 19.75 16.2802 19.75 12C19.75 7.71979 16.2802 4.25 12 4.25ZM2.75 12C2.75 6.89201 6.89201 2.75 12 2.75C17.108 2.75 21.25 6.89201 21.25 12C21.25 17.108 17.108 21.25 12 21.25C6.89201 21.25 2.75 17.108 2.75 12ZM11.25 8C11.25 7.58579 11.5858 7.25 12 7.25C12.4142 7.25 12.75 7.58579 12.75 8V12C12.75 12.4142 12.4142 12.75 12 12.75C11.5858 12.75 11.25 12.4142 11.25 12V8ZM12 16.75C12.4142 16.75 12.75 16.4142 12.75 16C12.75 15.5858 12.4142 15.25 12 15.25C11.5858 15.25 11.25 15.5858 11.25 16C11.25 16.4142 11.5858 16.75 12 16.75Z" fill=""/>
                        </svg>
                    </div>
                    <div class="mt-5 flex items-end justify-between">
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Buses Online</span>
                            <h4 class="mt-2 text-3xl font-bold text-gray-800 dark:text-white/90">{{ $onlineCount }}</h4>
                        </div>
                        <a href="{{ route('bus_location') }}" class="text-theme-xs font-medium text-brand-500 hover:text-brand-600">Live map</a>
                    </div>
                </div>

                <div class="flex min-h-[150px] flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                        <svg class="fill-gray-800 dark:fill-white/90" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M2.25 12C2.25 6.61522 6.61522 2.25 12 2.25C17.3848 2.25 21.75 6.61522 21.75 12C21.75 17.3848 17.3848 21.75 12 21.75C6.61522 21.75 2.25 17.3848 2.25 12ZM12 3.75C7.44365 3.75 3.75 7.44365 3.75 12C3.75 16.5563 7.44365 20.25 12 20.25C16.5563 20.25 20.25 16.5563 20.25 12C20.25 7.44365 16.5563 3.75 12 3.75ZM15.5303 9.71967C15.8232 10.0126 15.8232 10.4874 15.5303 10.7803L11.0303 15.2803C10.7374 15.5732 10.2626 15.5732 9.96967 15.2803L8.46967 13.7803C8.17678 13.4874 8.17678 13.0126 8.46967 12.7197C8.76256 12.4268 9.23744 12.4268 9.53033 12.7197L10.5 13.6893L14.4697 9.71967C14.7626 9.42678 15.2374 9.42678 15.5303 9.71967Z" fill=""/>
                        </svg>
                    </div>
                    <div class="mt-5 flex items-end justify-between">
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Picked Up Today</span>
                            <h4 class="mt-2 text-3xl font-bold text-gray-800 dark:text-white/90">{{ $pickedUp }}</h4>
                        </div>
                        <span class="text-theme-xs text-gray-400 dark:text-gray-500">of {{ $children->count() }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-12 gap-4 md:gap-6">
                <div class="col-span-12 xl:col-span-7">
                    <div class="flex h-full min-h-[460px] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">My Children</h3>
                                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                                    Assigned buses and live status
                                </p>
                            </div>
                            <a href="{{ route('parent.children') }}" class="text-theme-sm font-medium text-brand-500 hover:text-brand-600">View all</a>
                        </div>

                        <div class="mt-5 min-h-0 flex-1 space-y-4 overflow-y-auto custom-scrollbar pr-1">
                            @forelse ($children as $child)
                                @php
                                    $route = $child->route;
                                    $activeTrip = $child->getAttribute('activeTrip');
                                    $bus = $activeTrip?->bus;
                                    $location = $bus ? $locationsByBus->get($bus->id) : null;
                                    $online = $location && $location->recorded_at?->gt(now()->subMinutes(10));
                                    $records = $attendanceByStudent->get($child->id, collect());
                                    $pickup = $records->firstWhere('trip', \App\Models\Attendance::TRIP_HOME_TO_SCHOOL);
                                    $drop = $records->firstWhere('trip', \App\Models\Attendance::TRIP_SCHOOL_TO_HOME);
                                @endphp
                                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                                {{ strtoupper(substr($child->first_name ?? $child->full_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800 dark:text-white/90">{{ $child->full_name }}</p>
                                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                                    {{ trim($child->grade.' '.$child->section) }} · {{ $child->admission_no }}
                                                </p>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $online ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $online ? 'bg-success-500 animate-pulse' : 'bg-gray-400' }}"></span>
                                            {{ $online ? 'On the move' : 'No live signal' }}
                                        </span>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/[0.03]">
                                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">Assigned Route</p>
                                            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $route?->name ?? 'Not assigned' }}@if ($route?->route_code) ({{ $route->route_code }})@endif</p>
                                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $route?->school->name ?? '—' }}</p>
                                        </div>
                                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/[0.03]">
                                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">Driver</p>
                                            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $activeTrip?->driver?->full_name ?? 'No bus running' }}</p>
                                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">Speed: {{ $location?->speed ?? '—' }} km/h</p>
                                        </div>
                                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/[0.03]">
                                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">Pickup (Home → School)</p>
                                            @if ($pickup)
                                                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                                                    {{ $pickup->check_in_at ? 'Picked '.$pickup->check_in_at->format('H:i') : 'Not picked up' }}
                                                </p>
                                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                                    {{ $pickup->check_out_at ? 'At school '.$pickup->check_out_at->format('H:i') : '—' }}
                                                </p>
                                            @else
                                                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">No record yet</p>
                                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">—</p>
                                            @endif
                                        </div>
                                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/[0.03]">
                                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">Drop (School → Home)</p>
                                            @if ($drop)
                                                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                                                    {{ $drop->check_in_at ? 'Left '.$drop->check_in_at->format('H:i') : 'Not yet' }}
                                                </p>
                                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                                    {{ $drop->check_out_at ? 'Dropped home '.$drop->check_out_at->format('H:i') : '—' }}
                                                </p>
                                            @else
                                                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">No record yet</p>
                                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">—</p>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($route)
                                        <div class="mt-3 flex flex-wrap items-center gap-2">
                                            <a href="{{ route('bus_location') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-theme-xs font-medium text-white hover:bg-brand-600">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                Track Bus
                                            </a>
                                            <a href="{{ route('parent.student.attendance', $child) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-1.5 text-theme-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v2m8-2v2M3 9h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
                                                View Attendance
                                            </a>
                                            @if ($location)
                                                <span class="text-theme-xs text-gray-400 dark:text-gray-500">
                                                    Last update: {{ $location->recorded_at?->format('M d, H:i:s') ?? '—' }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="mt-3">
                                            <a href="{{ route('parent.student.attendance', $child) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-1.5 text-theme-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v2m8-2v2M3 9h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
                                                View Attendance
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">No children linked yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-span-12 xl:col-span-5">
                    <div class="flex h-full min-h-[460px] flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Today's Boarding</h3>
                            <span class="rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                                {{ $pickedUp }}/{{ $children->count() }} picked up
                            </span>
                        </div>

                        <div class="mt-5 min-h-0 flex-1 overflow-y-auto custom-scrollbar pr-1">
                            <div class="space-y-3">
                                @forelse ($children as $child)
                                    @php
                                        $records = $attendanceByStudent->get($child->id, collect());
                                        $pickup = $records->firstWhere('trip', \App\Models\Attendance::TRIP_HOME_TO_SCHOOL);
                                        $drop = $records->firstWhere('trip', \App\Models\Attendance::TRIP_SCHOOL_TO_HOME);
                                    @endphp
                                    <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                                        <div>
                                            <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $child->full_name }}</p>
                                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $child->route?->name ?? 'No route' }}</p>
                                        </div>
                                        <div class="text-right">
                                            @if ($pickup?->check_in_at && $drop?->check_out_at)
                                                <p class="text-theme-sm font-medium text-success-600 dark:text-success-500">Completed</p>
                                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">P {{ $pickup->check_in_at->format('H:i') }} · D {{ $drop->check_out_at->format('H:i') }}</p>
                                            @elseif ($pickup?->check_in_at)
                                                <p class="text-theme-sm font-medium text-success-600 dark:text-success-500">Picked up</p>
                                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $pickup->check_in_at->format('H:i') }} · drop —</p>
                                            @else
                                                <p class="text-theme-sm font-medium text-gray-400 dark:text-gray-500">Not yet</p>
                                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">—</p>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">No children linked yet.</p>
                                @endforelse
                            </div>

                            <div class="mt-5 rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                                <h4 class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">Quick Actions</h4>
                                <div class="mt-3 grid grid-cols-1 gap-3">
                                    <a href="{{ route('bus_location') }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-center text-theme-sm font-medium text-white hover:bg-brand-600">
                                        View Live Bus Location
                                    </a>
                                    <a href="{{ route('parent.children') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-center text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                        My Children
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
