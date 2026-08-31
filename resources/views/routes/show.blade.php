<x-app-layout page="route-management">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6 space-y-6">
        
        <!-- Header Bar: Route Name, Edit, Back -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-5 dark:border-gray-800">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $route->name }}</h1>
                    <span class="rounded-md bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 font-mono">
                        {{ $route->route_code }}
                    </span>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Route details, ordered stops management, and visual route preview map.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a
                    href="{{ route('routes.edit', $route) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Route
                </a>
                <a
                    href="{{ route('routes.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Routes
                </a>
            </div>
        </div>

        <!-- Success Alert -->
        @if (session('success'))
            <div class="flex items-center gap-3 rounded-xl bg-green-50 p-4 text-sm font-medium text-green-800 dark:bg-green-500/10 dark:text-green-400 border border-green-200 dark:border-green-800/40">
                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Validation Errors Alert -->
        @if ($errors->any())
            <div class="rounded-xl bg-red-50 p-4 text-sm text-red-800 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-800/40">
                <div class="flex items-center gap-2 font-semibold mb-1">
                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Please fix the following errors:</span>
                </div>
                <ul class="list-inside list-disc ml-6 text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card 1: Route Information -->
        @include('routes.partials.route-info-card')

        <!-- Card 2: Route Stops with [+ Add Stop] button & Table -->
        @include('routes.partials.route-stops-card')

        <!-- Card 3: Live Route Journey Timeline & Summary -->
        {{-- @include('routes.partials.live-journey-section') --}}

        <!-- Card 4: Route Preview (Map) -->
        {{-- @include('routes.partials.route-map-preview') --}}

    </div>

    <!-- Add & Edit Stop Modal -->
    @include('routes.partials.stop-modal')
</x-app-layout>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
