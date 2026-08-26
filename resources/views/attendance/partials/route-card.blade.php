@php
    $stats = $checkedIn[$route->id] ?? [
        'picked_up_home' => 0,
        'dropped_school' => 0,
        'picked_up_school' => 0,
        'dropped_home' => 0,
    ];

    $dayCompleted = $route->students_count > 0 && ($stats['dropped_home'] ?? 0) >= $route->students_count;
@endphp

<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-3 flex items-start justify-between gap-3">
        <div>
            <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $route->name }}</p>
            @if ($route->route_code)
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $route->route_code }}</p>
            @endif
        </div>
        @if ($route->is_active)
            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">Active</span>
        @else
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
        @endif
    </div>

    <dl class="mb-4 space-y-1.5 text-sm">
        <div class="flex justify-between gap-3">
            <dt class="text-gray-500 dark:text-gray-400">School</dt>
            <dd class="font-medium text-gray-900 dark:text-white">{{ $route->school->name ?? '—' }}</dd>
        </div>
        <div class="flex justify-between gap-3">
            <dt class="text-gray-500 dark:text-gray-400">Assigned Students</dt>
            <dd class="font-medium text-gray-900 dark:text-white">{{ $route->students_count }}</dd>
        </div>
        <div class="flex justify-between gap-3">
            <dt class="text-gray-500 dark:text-gray-400">Picked Up from Home ({{ $today }})</dt>
            <dd class="font-medium text-gray-900 dark:text-white">{{ $stats['picked_up_home'] ?? 0 }} / {{ $route->students_count }}</dd>
        </div>
        <div class="flex justify-between gap-3">
            <dt class="text-gray-500 dark:text-gray-400">Dropped at School ({{ $today }})</dt>
            <dd class="font-medium text-gray-900 dark:text-white">{{ $stats['dropped_school'] ?? 0 }} / {{ $route->students_count }}</dd>
        </div>
        <div class="flex justify-between gap-3">
            <dt class="text-gray-500 dark:text-gray-400">Picked Up from School ({{ $today }})</dt>
            <dd class="font-medium text-gray-900 dark:text-white">{{ $stats['picked_up_school'] ?? 0 }} / {{ $route->students_count }}</dd>
        </div>
        <div class="flex justify-between gap-3">
            <dt class="text-gray-500 dark:text-gray-400">Dropped at Home ({{ $today }})</dt>
            <dd class="font-medium text-gray-900 dark:text-white">
                {{ $stats['dropped_home'] ?? 0 }} / {{ $route->students_count }}
                @if ($dayCompleted)
                    <span class="ml-1 text-green-600 dark:text-green-400">✓</span>
                @endif
            </dd>
        </div>
    </dl>

    @if ($dayCompleted)
        <span
            class="block w-full rounded-lg bg-green-100 px-4 py-2 text-center text-sm font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400"
        >
            Attendance Completed
        </span>
    @else
        <a
            href="{{ route('attendance.routes.show', ['route' => $route, 'date' => $today]) }}"
            class="block w-full rounded-lg bg-brand-500 px-4 py-2 text-center text-sm font-medium text-white hover:bg-brand-600"
        >
            Add Today's Attendance
        </a>
    @endif
    <a
        href="{{ route('attendance.routes.show', ['route' => $route, 'date' => $today]) }}"
        class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
    >
        View Attendance
    </a>
</div>
