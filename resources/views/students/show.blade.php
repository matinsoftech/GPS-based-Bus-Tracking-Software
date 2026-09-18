<x-app-layout page="student-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $student->full_name }}</h1>
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('students.edit', $student) }}"
                    class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"
                >
                    Edit
                </a>
                <a
                    href="{{ route('students.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    Back to Students
                </a>
            </div>
        </div>

        @php
            $studentTabs = [
                ['key' => 'overview', 'label' => 'Overview', 'url' => route('students.show', $student), 'icon' => 'user-circle'],
                ['key' => 'routes', 'label' => 'Routes', 'url' => route('students.show', [$student, 'tab' => 'routes']), 'icon' => 'map', 'count' => $routeCount],
                ['key' => 'attendance', 'label' => 'Attendance', 'url' => route('students.show', [$student, 'tab' => 'attendance']), 'icon' => 'document-check', 'count' => $attendanceCount],
            ];
        @endphp

        <x-pill-tabs :tabs="$studentTabs" :active="$tab" />

        @if ($tab === 'overview')
        <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-6 flex items-center gap-4">
                @if ($student->photo)
                    <img
                        src="{{ asset('storage/' . $student->photo) }}"
                        alt="{{ $student->full_name }}"
                        class="h-16 w-16 rounded-full object-cover"
                    >
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-200 text-xl font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $student->admission_no }}</p>
                    @if ($student->is_active)
                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
                    @else
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                    @endif
                </div>
            </div>

            <h2 class="mb-4 border-b border-gray-200 pb-3 text-lg font-semibold text-gray-900 dark:border-gray-800 dark:text-white">
                Student Details
            </h2>

            <dl class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->full_name }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Admission No</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->admission_no }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date of Birth</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->date_of_birth->format('M d, Y') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Gender</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->gender }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Grade</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->grade }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Section</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->section ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Roll No</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->roll_no ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">School</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->school->name ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Parent</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                        @if ($student->parent)
                            <a href="{{ route('parents.show', $student->parent) }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">{{ $student->parent->user->name }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>

            <dl class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->created_at->format('M d, Y H:i') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->updated_at->format('M d, Y H:i') }}</dd>
                </div>
            </dl>
        </div>
        @elseif ($tab === 'routes')
        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Assigned Routes & Stops</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Routes assigned to {{ $student->first_name }} with their stops</p>
            </div>

            @if ($student->routes->isNotEmpty())
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($student->routes->sortBy('name') as $route)
                        <div class="px-5 py-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('routes.show', $route) }}" class="font-semibold text-gray-900 hover:text-brand-600 dark:text-white dark:hover:text-brand-400">
                                    {{ $route->name }}
                                </a>
                                <span class="rounded-full border border-gray-300 px-2 py-0.5 text-xs font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300">
                                    {{ $route->route_code }}
                                </span>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $route->route_type_color_classes }}">
                                    {{ $route->route_type_label }}
                                </span>
                            </div>

                            @php $assignedStops = $student->stops->where('route_id', $route->id)->sortBy('stop_order'); @endphp

                            @if ($assignedStops->isNotEmpty())
                                <ol class="mt-3 list-inside list-decimal space-y-1 text-sm text-gray-600 dark:text-gray-300">
                                    @foreach ($assignedStops as $stop)
                                        <li>
                                            {{ $stop->name }}
                                            @if ($stop->pickup_time)
                                                <span class="text-gray-400 dark:text-gray-500">({{ $stop->pickup_time }})</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ol>
                            @else
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No stops assigned to this student on this route.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    No routes assigned to this student yet.
                </div>
            @endif
        </div>
        @elseif ($tab === 'attendance')
        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Attendance History</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $totalRecords }} record{{ $totalRecords === 1 ? '' : 's' }}</span>
            </div>

            <form action="{{ route('students.show', [$student, 'tab' => 'attendance']) }}" method="GET"
                class="border-b border-gray-200 p-5 dark:border-gray-800">
                <input type="hidden" name="tab" value="attendance">
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="period" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Period</label>
                        <select
                            id="period"
                            name="period"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="" @selected($period === '')>Any Period</option>
                            <option value="today" @selected($period === 'today')>Today</option>
                            <option value="yesterday" @selected($period === 'yesterday')>Yesterday</option>
                            <option value="this_week" @selected($period === 'this_week')>This Week</option>
                            <option value="this_month" @selected($period === 'this_month')>This Month</option>
                            <option value="all" @selected($period === 'all')>All Time</option>
                        </select>
                    </div>
                    <div>
                        <label for="date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                        <input
                            type="date"
                            id="date"
                            name="date"
                            value="{{ $singleDate?->toDateString() ?? '' }}"
                            onclick="if (this.showPicker) { try { this.showPicker(); } catch (e) {} }"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                    </div>
                    <div>
                        <label for="from" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">From</label>
                        <input
                            type="date"
                            id="from"
                            name="from"
                            value="{{ $from?->toDateString() ?? '' }}"
                            onclick="if (this.showPicker) { try { this.showPicker(); } catch (e) {} }"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                    </div>
                    <div>
                        <label for="to" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">To</label>
                        <input
                            type="date"
                            id="to"
                            name="to"
                            value="{{ $to?->toDateString() ?? '' }}"
                            onclick="if (this.showPicker) { try { this.showPicker(); } catch (e) {} }"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                    </div>
                    <div>
                        <label for="route_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Route</label>
                        <select
                            id="route_id"
                            name="route_id"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="" @selected(!$routeId)>All Routes</option>
                            @foreach ($routes as $filterRoute)
                                <option value="{{ $filterRoute->id }}" @selected((int) $routeId === (int) $filterRoute->id)>
                                    {{ $filterRoute->name }} — {{ $filterRoute->route_type_label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button
                        type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600"
                    >
                        Apply
                    </button>
                    <a
                        href="{{ route('students.show', [$student, 'tab' => 'attendance']) }}"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        Clear
                    </a>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-800">
                        <tr class="text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3 font-medium">Date</th>
                            <th class="px-5 py-3 font-medium">Check In</th>
                            <th class="px-5 py-3 font-medium">Check Out</th>
                            <th class="px-5 py-3 font-medium">Route</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Marked By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($records as $record)
                            <tr class="text-gray-700 dark:text-gray-200">
                                <td class="px-5 py-3 whitespace-nowrap">{{ $record->date->format('M d, Y') }}</td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    @if ($record->check_in_at)
                                        <span class="text-xs font-medium text-green-700 dark:text-green-400">
                                            {{ $record->check_in_at->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    @if ($record->check_out_at)
                                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                            {{ $record->check_out_at->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $record->route?->name ?? '—' }}</span>
                                    @if ($record->route)
                                        <span class="ml-1 text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                            ({{ $record->route->route_type_label }})
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if ($record->isCheckedOut())
                                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Completed</span>
                                    @elseif ($record->isCheckedIn())
                                        <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">Checked In</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Not Checked In</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-gray-900 dark:text-white">
                                    {{ $record->markedBy?->name ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No attendance records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
