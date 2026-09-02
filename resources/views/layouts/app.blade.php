<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>
        {{ $settings?->platform_name ?? config('app.name', 'Laravel') }}

        @isset($title)
            - {{ $title }}
        @endisset
    </title>

    {{-- Dynamic Favicon --}}
    @if ($settings?->favicon)
        <link rel="icon" type="image/png" href="{{ Storage::url($settings->favicon) }}">
    @else
        {{-- Default Favicon --}}
        <link rel="icon" type="image/png" href="{{ asset('images/logo/schoollogo.png') }}">
    @endif


    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body x-data="{ page: '{{ $page ?? 'dashboard' }}', loaded: true, darkMode: false, sidebarToggle: false }" x-init="darkMode = JSON.parse(localStorage.getItem('darkMode'));
$watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))" :class="{ 'dark bg-gray-900': darkMode === true }">
    @include('partials.preloader')

    <div class="flex h-screen overflow-hidden">
        @include('partials.sidebar')

        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
            @include('partials.overlay')
            @include('partials.header')

            <main>
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
