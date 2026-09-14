@props(['notification', 'size' => 'md'])

@php
    $type = $notification->data['type'] ?? 'info';

    $map = [
        'bus' => [
            'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
            'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z',
        ],
        'route' => [
            'bg-purple-50 text-purple-600 dark:bg-purple-500/15 dark:text-purple-400',
            'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
        ],
        'arrival' => [
            'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
            'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'stop' => [
            'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
            'M12 14l9-5-9-5-9 5 9 5zM12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
        ],
        'departure' => [
            'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
            'M5 12h14M13 5l7 7-7 7',
        ],
        'delay' => [
            'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
            'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        ],
        'warning' => [
            'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400',
            'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        ],
        'alert' => [
            'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400',
            'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        ],
        'invoice' => [
            'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
            'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        ],
        'info' => [
            'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
            'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    ];

    [$iconClasses, $iconPath] = $map[$type] ?? $map['info'];

    $boxSizes = ['sm' => 'h-8 w-8', 'md' => 'h-10 w-10', 'lg' => 'h-12 w-12'];
    $svgSizes = ['sm' => 'h-4 w-4', 'md' => 'h-5 w-5', 'lg' => 'h-6 w-6'];
@endphp

<span
    class="inline-flex shrink-0 items-center justify-center rounded-xl {{ $boxSizes[$size] ?? $boxSizes['md'] }} {{ $iconClasses }}">
    <svg class="{{ $svgSizes[$size] ?? $svgSizes['md'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="{{ $iconPath }}" />
    </svg>
</span>
