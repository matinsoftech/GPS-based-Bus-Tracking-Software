<x-app-layout page="my-children">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">My Children</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $children->count() }} child{{ $children->count() === 1 ? '' : 'ren' }} linked to your account.
                </p>
            </div>
        </div>

        @if ($children->isEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No children are linked to your account yet. Please contact your school.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($children as $child)
                    @php
                        $route = $child->route;
                        $activeTrip = $route?->activeTrip;
                    @endphp
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-lg font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                {{ strtoupper(substr($child->first_name ?? $child->full_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $child->full_name }}</p>
                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                    {{ trim($child->grade.' '.$child->section) }} · {{ $child->admission_no }}
                                </p>
                            </div>
                        </div>

                        <dl class="mt-4 space-y-2 border-t border-gray-100 pt-4 text-sm dark:border-gray-800">
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">School</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $child->school?->name ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Assigned Route</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $route?->name ?? 'Not assigned' }}@if ($route?->route_code) ({{ $route->route_code }})@endif</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Driver</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $activeTrip?->driver?->full_name ?? 'No bus running' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Pickup</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $child->pickup_location ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Drop-off</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $child->drop_location ?? '—' }}</dd>
                            </div>
                        </dl>

                        <div class="mt-4 grid grid-cols-1 gap-2">
                            @if ($route)
                                <a
                                    href="{{ route('bus_location') }}"
                                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-theme-sm font-medium text-white hover:bg-brand-600"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Track This Bus
                                </a>
                            @endif
                            <a
                                href="{{ route('parent.student.attendance', $child) }}"
                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-gray-300 px-4 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v2m8-2v2M3 9h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
                                View Attendance
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
