<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="BusTrack — real-time GPS bus tracking software for schools, cities and fleet operators. Live maps, smart ETAs and arrival alerts for parents, drivers and administrators.">

        <title>BusTrack — GPS Bus Tracking Software</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-outfit relative bg-white text-gray-900 antialiased">
        {{-- ============ NAVBAR ============ --}}
        <header class="sticky top-0 z-50 border-b border-gray-200/70 bg-white/80 backdrop-blur-xl">
            <nav x-data="{ open: false }" class="mx-auto flex h-18 w-full max-w-7xl items-center justify-between px-6 lg:px-8">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-brand-500 shadow-theme-md">
                        <svg class="size-5 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M6 16.5v-6a6 6 0 0 1 12 0v6a2.25 2.25 0 0 1-2.25 2.25h-1.5v1.5h-1.5v-1.5h-3v1.5h-1.5v-1.5H8.25A2.25 2.25 0 0 1 6 16.5Zm2.25-5.25a.75.75 0 0 0 0 1.5h7.5a.75.75 0 0 0 0-1.5h-7.5Zm0 3a.75.75 0 0 0 0 1.5h7.5a.75.75 0 0 0 0-1.5h-7.5Z"/>
                        </svg>
                    </span>
                    <span class="text-xl font-semibold tracking-tight">Bus<span class="text-brand-500">Track</span></span>
                </a>

                <div class="hidden items-center gap-8 lg:flex">
                    <a href="#features" class="text-theme-sm font-medium text-gray-600 transition hover:text-gray-900">Features</a>
                    <a href="#how-it-works" class="text-theme-sm font-medium text-gray-600 transition hover:text-gray-900">How it works</a>
                    <a href="#for-who" class="text-theme-sm font-medium text-gray-600 transition hover:text-gray-900">Who it's for</a>
                    <a href="#testimonials" class="text-theme-sm font-medium text-gray-600 transition hover:text-gray-900">Customers</a>
                    <a href="#faq" class="text-theme-sm font-medium text-gray-600 transition hover:text-gray-900">FAQ</a>
                </div>

                <div class="hidden items-center gap-3 lg:flex">
                    @if (Route::has('login'))
                        @auth
                            @php
                                $dashboardRoute = auth()->user()->hasRole('Super Admin')
                                    ? route('dashboard')
                                    : (auth()->user()->hasRole('School Admin')
                                        ? route('principal.dashboard')
                                        : (auth()->user()->hasRole('Driver')
                                            ? route('driver.dashboard')
                                            : (auth()->user()->hasRole('Parent')
                                                ? route('parent.dashboard')
                                                : route('profile.edit'))));
                            @endphp
                            <a href="{{ $dashboardRoute }}" class="inline-flex items-center justify-center rounded-full bg-brand-500 px-5 py-2.5 text-theme-sm font-semibold text-white shadow-theme-md transition hover:bg-brand-600">
                                Go to dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-full bg-brand-500 px-5 py-2.5 text-theme-sm font-semibold text-white shadow-theme-md transition hover:bg-brand-600">Log in</a>
                            {{-- @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full bg-brand-500 px-5 py-2.5 text-theme-sm font-semibold text-white shadow-theme-md transition hover:bg-brand-600">
                                    Get started free
                                </a>
                            @endif --}}
                        @endauth
                    @endif
                </div>

                <button type="button" @click="open = !open" class="inline-flex size-10 items-center justify-center rounded-xl border border-gray-200 text-gray-700 lg:hidden" aria-label="Toggle menu">
                    <svg x-show="!open" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <svg x-show="open" x-cloak class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </nav>

            <div x-show="open" x-cloak class="border-t border-gray-100 bg-white px-6 py-4 lg:hidden">
                <div class="flex flex-col gap-1">
                    <a href="#features" @click="open = false" class="rounded-lg px-3 py-2.5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50">Features</a>
                    <a href="#how-it-works" @click="open = false" class="rounded-lg px-3 py-2.5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50">How it works</a>
                    <a href="#for-who" @click="open = false" class="rounded-lg px-3 py-2.5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50">Who it's for</a>
                    <a href="#testimonials" @click="open = false" class="rounded-lg px-3 py-2.5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50">Customers</a>
                    <a href="#faq" @click="open = false" class="rounded-lg px-3 py-2.5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50">FAQ</a>
                    <div class="mt-3 flex gap-3 border-t border-gray-100 pt-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ $dashboardRoute }}" class="flex-1 rounded-full bg-brand-500 px-5 py-2.5 text-center text-theme-sm font-semibold text-white">Go to dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="flex-1 rounded-full border border-gray-200 px-5 py-2.5 text-center text-theme-sm font-semibold text-gray-700">Log in</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="flex-1 rounded-full bg-brand-500 px-5 py-2.5 text-center text-theme-sm font-semibold text-white">Get started</a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <main>
            {{-- ============ HERO ============ --}}
            <section class="relative overflow-hidden">
                <div class="pointer-events-none absolute inset-0 -z-10">
                    <div class="absolute -top-40 left-1/2 h-[520px] w-[820px] -translate-x-1/2 rounded-full bg-brand-50 blur-3xl"></div>
                    <div class="absolute top-40 -left-32 size-80 rounded-full bg-orange-50 blur-3xl"></div>
                    <div class="absolute top-72 -right-32 size-96 rounded-full bg-brand-50 blur-3xl"></div>
                    <svg class="absolute inset-0 h-full w-full opacity-[0.4]" aria-hidden="true">
                        <defs>
                            <pattern id="grid" width="48" height="48" patternUnits="userSpaceOnUse">
                                <path d="M48 0H0V48" fill="none" stroke="#EEF2FF" stroke-width="1"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid)"/>
                    </svg>
                </div>

                <div class="mx-auto w-full max-w-7xl px-6 pt-16 pb-20 lg:px-8 lg:pt-24 lg:pb-28">
                    <div class="grid items-center gap-16 lg:grid-cols-2">
                        {{-- Left copy --}}
                        <div>
                            <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-brand-100 bg-brand-25 px-4 py-1.5">
                                <span class="relative flex size-2">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-success-400 opacity-75"></span>
                                    <span class="relative inline-flex size-2 rounded-full bg-success-500"></span>
                                </span>
                                <span class="text-theme-xs font-semibold tracking-wide text-brand-700">Live GPS fleet tracking</span>
                            </div>

                            <h1 class="text-title-lg font-semibold leading-[1.08] tracking-tight text-gray-900 sm:text-title-xl lg:text-title-2xl">
                                Every bus.<br>
                                Every stop.<br>
                                <span class="bg-gradient-to-r from-brand-600 to-brand-400 bg-clip-text text-transparent">Real-time tracking.</span>
                            </h1>

                            <p class="mt-6 max-w-xl text-theme-xl leading-relaxed text-gray-500">
                                Track your school or city buses on a live GPS dashboard. Know exactly where each bus is, when it reaches the next stop, and get instant arrival alerts — for parents, drivers and administrators.
                            </p>

                            <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="group inline-flex items-center justify-center gap-2 rounded-full bg-brand-500 px-7 py-3.5 text-base font-semibold text-white shadow-theme-lg transition hover:bg-brand-600">
                                        Get started free
                                        <svg class="size-5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                                    </a>
                                @endif
                                <a href="#features" class="inline-flex items-center justify-center gap-2 rounded-full border border-gray-200 bg-white px-7 py-3.5 text-base font-semibold text-gray-700 shadow-theme-sm transition hover:border-gray-300 hover:text-gray-900">
                                    <svg class="size-5 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/></svg>
                                    Explore features
                                </a>
                            </div>

                            <div class="mt-10 flex items-center gap-4">
                                <div class="flex -space-x-2.5">
                                    @foreach (['#2563EB', '#FB6514', '#12B76A', '#7A5AF8'] as $i => $color)
                                        <span class="flex size-10 items-center justify-center rounded-full border-2 border-white text-xs font-semibold text-white shadow-theme-sm" style="background: {{ $color }}">{{ ['SA','JB','MK','TC'][$i] }}</span>
                                    @endforeach
                                </div>
                                <div>
                                    <div class="flex items-center gap-1 text-warning-500">
                                        @for ($i = 0; $i < 5; $i++)
                                            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.9L10 15l-5.2 2.7 1-5.9L1.5 7.7l5.9-.9L10 1.5z"/></svg>
                                        @endfor
                                    </div>
                                    <p class="mt-0.5 text-theme-sm text-gray-500"><span class="font-semibold text-gray-800">40+ schools</span> trust BusTrack every day</p>
                                </div>
                            </div>
                        </div>

                        {{-- Right: live map mockup --}}
                        <div class="relative">
                            <div class="relative mx-auto max-w-xl lg:max-w-none">
                                {{-- Browser chrome --}}
                                <div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-theme-xl">
                                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50/80 px-5 py-3.5">
                                        <span class="size-3 rounded-full bg-error-400"></span>
                                        <span class="size-3 rounded-full bg-warning-400"></span>
                                        <span class="size-3 rounded-full bg-success-400"></span>
                                        <span class="ml-4 flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-white px-3 py-1 text-theme-xs text-gray-400 ring-1 ring-gray-100">
                                            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                                            app.bustrack.io/live
                                        </span>
                                        <svg class="size-4 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
                                    </div>

                                    <div class="relative">
                                        {{-- Map --}}
                                        <svg viewBox="0 0 800 520" class="block h-auto w-full" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <rect width="800" height="520" fill="#F0F5FC"/>
                                            <g stroke="#E3EAF6">
                                                <path d="M0 90H800M0 190H800M0 290H800M0 390H800M0 490H800M90 0V520M190 0V520M290 0V520M390 0V520M490 0V520M590 0V520M690 0V520"/>
                                            </g>

                                            {{-- parks --}}
                                            <path d="M610 60c30 18 46 46 40 78-6 30-34 46-64 40-28-6-44-34-38-62 7-30 32-48 62-56z" fill="#DFF2E6"/>
                                            <path d="M60 390c28 12 44 38 40 66-5 28-32 42-58 36-26-5-40-30-36-56 6-28 28-40 54-46z" fill="#DFF2E6"/>

                                            {{-- river --}}
                                            <path d="M120 -10c60 70 40 130-10 200s-70 130-40 210c30 78 90 110 160 130" stroke="#C9E0F7" stroke-width="34" stroke-linecap="round" fill="none" opacity=".55"/>

                                            {{-- roads --}}
                                            <g stroke="#FFFFFF" stroke-linecap="round">
                                                <path d="M-20 130C160 200 620 300 820 380" stroke-width="26"/>
                                                <path d="M120 -30C260 180 180 360 330 540" stroke-width="16"/>
                                                <path d="M-20 340C220 300 420 260 820 190" stroke-width="12"/>
                                                <path d="M-30 210C160 190 220 320 430 300C620 282 680 420 820 470" stroke-width="10"/>
                                                <path d="M520 100C500 240 620 300 820 240" stroke-width="10"/>
                                            </g>

                                            {{-- route --}}
                                            <path d="M70 425 L190 350 L300 235 L420 200 L520 250 L600 175 L705 115" stroke="#465FFF" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" opacity=".12"/>
                                            <path d="M70 425 L190 350 L300 235 L420 200 L520 250 L600 175 L705 115" stroke="#465FFF" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" class="route-anim"/>

                                            {{-- stops --}}
                                            @foreach ([['x' => 70, 'y' => 425], ['x' => 190, 'y' => 350], ['x' => 300, 'y' => 235], ['x' => 420, 'y' => 200], ['x' => 520, 'y' => 250], ['x' => 600, 'y' => 175], ['x' => 705, 'y' => 115]] as $s)
                                                <g>
                                                    <circle cx="{{ $s['x'] }}" cy="{{ $s['y'] }}" r="12" fill="#FFFFFF" stroke="#465FFF" stroke-width="3"/>
                                                    <circle cx="{{ $s['x'] }}" cy="{{ $s['y'] }}" r="3.5" fill="#465FFF"/>
                                                </g>
                                            @endforeach

                                            {{-- other buses --}}
                                            <g transform="translate(238 285)">
                                                <circle class="bus-halo" r="20" fill="none" stroke="#FB6514" stroke-width="3"/>
                                                <rect x="-15" y="-16" width="30" height="32" rx="9" fill="#FB6514" stroke="#FFFFFF" stroke-width="2"/>
                                                <path d="M-7 -6l4 6h2l1-6zM6 -6l-1 6h-2l-4-6z" fill="#FFFFFF"/>
                                            </g>
                                            <g transform="translate(612 168)">
                                                <rect x="-15" y="-16" width="30" height="32" rx="9" fill="#12B76A" stroke="#FFFFFF" stroke-width="2"/>
                                                <path d="M-7 -6l4 6h2l1-6zM6 -6l-1 6h-2l-4-6z" fill="#FFFFFF"/>
                                            </g>

                                            {{-- selected bus --}}
                                            <g transform="translate(420 200)">
                                                <circle class="bus-halo" r="23" fill="none" stroke="#465FFF" stroke-width="3"/>
                                                <rect x="-16" y="-17" width="32" height="34" rx="10" fill="#465FFF" stroke="#FFFFFF" stroke-width="2.5"/>
                                                <path d="M-7 -7l4 6h2l1-6zM7 -7l-1 6H4l-4-6z" fill="#FFFFFF"/>
                                            </g>

                                            {{-- label --}}
                                            <g transform="translate(448 170)">
                                                <rect x="-8" y="-8" width="104" height="34" rx="17" fill="#FFFFFF" stroke="#E3E8F5"/>
                                                <text x="44" y="12" font-family="Outfit, sans-serif" font-size="13" font-weight="600" fill="#101828" text-anchor="middle">Bus #12 · 42 km/h</text>
                                            </g>
                                        </svg>

                                        {{-- top-left live chip --}}
                                        <div class="animate-float absolute top-5 left-5 flex items-center gap-2 rounded-full bg-white/95 px-4 py-2 shadow-theme-md ring-1 ring-gray-100 backdrop-blur">
                                            <span class="relative flex size-2">
                                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-success-400 opacity-75"></span>
                                                <span class="relative inline-flex size-2 rounded-full bg-success-500"></span>
                                            </span>
                                            <span class="text-theme-xs font-semibold text-gray-800">2,400+ buses live</span>
                                        </div>

                                        {{-- ETA card bottom-left --}}
                                        <div class="animate-float-delay absolute bottom-5 left-5 w-56 rounded-2xl bg-white/95 p-4 shadow-theme-lg ring-1 ring-gray-100 backdrop-blur">
                                            <div class="flex items-start gap-3">
                                                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-50">
                                                    <svg class="size-5 text-brand-500" viewBox="0 0 24 24" fill="currentColor"><path d="M6 16.5v-6a6 6 0 0 1 12 0v6a2.25 2.25 0 0 1-2.25 2.25h-1.5v1.5h-1.5v-1.5h-3v1.5h-1.5v-1.5H8.25A2.25 2.25 0 0 1 6 16.5Zm2.25-5.25a.75.75 0 0 0 0 1.5h7.5a.75.75 0 0 0 0-1.5h-7.5Zm0 3a.75.75 0 0 0 0 1.5h7.5a.75.75 0 0 0 0-1.5h-7.5Z"/></svg>
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="truncate text-theme-sm font-semibold text-gray-900">Route 12 · City Center</p>
                                                    <p class="text-theme-xs text-gray-500">Green Valley → Main St</p>
                                                </div>
                                            </div>
                                            <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3">
                                                <span class="rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-700">On time · 96%</span>
                                                <span class="text-theme-sm font-bold text-brand-600">Arriving in 4 min</span>
                                            </div>
                                        </div>

                                        {{-- top-right performance card --}}
                                        <div class="animate-float absolute top-5 right-5 rounded-2xl bg-white/95 px-4 py-3 shadow-theme-md ring-1 ring-gray-100 backdrop-blur">
                                            <p class="text-theme-xs text-gray-500">Today's fleet</p>
                                            <div class="mt-1 flex items-end gap-2">
                                                <span class="text-2xl font-bold tracking-tight text-gray-900">128</span>
                                                <span class="mb-1 text-theme-xs font-semibold text-success-600">+12%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- glow behind mockup --}}
                                <div class="pointer-events-none absolute -inset-8 -z-10 rounded-[3rem] bg-gradient-to-tr from-brand-100 via-brand-25 to-orange-50 opacity-70 blur-2xl"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============ TRUST / STATS ============ --}}
            <section class="border-y border-gray-100 bg-gray-50/60">
                <div class="mx-auto w-full max-w-7xl px-6 py-14 lg:px-8">
                    <p class="text-center text-theme-sm font-medium tracking-wide text-gray-400 uppercase">Trusted by transport teams across the region</p>
                    <div class="relative mt-8 overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_10%,black_90%,transparent)]">
                        <div class="animate-marquee flex w-max items-center gap-14 pr-14">
                            @foreach ([
                                ['Green Valley Schools', 'M1'],
                                ['Metro City Transit', 'M2'],
                                ['Sunrise Academy', 'M3'],
                                ['Hillside Public Schools', 'M4'],
                                ['Campus Connect', 'M5'],
                                ['RapidMove Logistics', 'M6'],
                                ['Northfield District', 'M7'],
                                ['Urban Shuttle Co.', 'M8'],
                            ] as $i => [$name, $logo])
                                <div class="flex items-center gap-2.5 opacity-60">
                                    <span class="flex size-8 items-center justify-center rounded-lg bg-brand-500 text-theme-xs font-bold text-white">{{ $logo }}</span>
                                    <span class="whitespace-nowrap text-lg font-semibold text-gray-700">{{ $name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-14 grid grid-cols-2 gap-8 lg:grid-cols-4">
                        @foreach ([
                            ['2,400+', 'Buses tracked daily'],
                            ['98.7%', 'Arrival accuracy'],
                            ['120+', 'Routes managed'],
                            ['<5s', 'Location updates'],
                        ] as [$value, $label])
                            <div class="text-center">
                                <p class="bg-gradient-to-r from-brand-600 to-brand-400 bg-clip-text text-4xl font-bold tracking-tight text-transparent lg:text-5xl">{{ $value }}</p>
                                <p class="mt-2 text-theme-sm font-medium text-gray-500">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ============ FEATURES ============ --}}
            <section id="features" class="scroll-mt-24 bg-white">
                <div class="mx-auto w-full max-w-7xl px-6 py-20 lg:px-8 lg:py-28">
                    <div class="mx-auto max-w-2xl text-center">
                        <span class="text-theme-sm font-semibold tracking-widest text-brand-600 uppercase">Features</span>
                        <h2 class="mt-3 text-title-md font-semibold tracking-tight text-gray-900 lg:text-title-lg">Everything you need to run a <span class="text-brand-500">punctual fleet</span></h2>
                        <p class="mt-4 text-theme-xl leading-relaxed text-gray-500">One platform that keeps parents informed, drivers on route, and administrators in control.</p>
                    </div>

                    <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ([
                            [
                                'Live GPS tracking',
                                'See every bus on a real-time interactive map with live position, speed, and direction.',
                                'M20.213 19.787A9.5 9.5 0 1 1 4.213 9.787a9.5 9.5 0 0 1 16-0.005zM12 7v5l3 2',
                                'bg-brand-50 text-brand-500',
                            ],
                            [
                                'Smart ETAs & alerts',
                                'Parents and admins get automatic notifications when a bus approaches a stop, departs, or is delayed.',
                                'M12 3v3m0 12v3M3 12h3m12 0h3M5.6 5.6l2.1 2.1m8.6 8.6 2.1 2.1M5.6 18.4l2.1-2.1m8.6-8.6 2.1-2.1',
                                'bg-warning-50 text-warning-600',
                            ],
                            [
                                'Route management',
                                'Design routes, assign stops and schedules, and let the system optimize the driving order for you.',
                                'M4 6h16M4 12h16M4 18h16M7 3v6m10 6v6',
                                'bg-orange-50 text-orange-600',
                            ],
                            [
                                'Parent portal',
                                'A simple, mobile-friendly view where parents track the bus live and check on their child\'s journey.',
                                'M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2m5-14a4 4 0 1 0 0 8 4 4 0 0 0 0-8zm9 2v6m-3-3h6',
                                'bg-success-50 text-success-600',
                            ],
                            [
                                'Driver companion',
                                'Turn-by-turn guidance, stop confirmations, daily trip logs, and incident reporting on the go.',
                                'M3 11l18-6-6 18-3-7-9-5z',
                                'bg-theme-purple-500/10 text-theme-purple-500',
                            ],
                            [
                                'Fleet dashboard & reports',
                                'Administrators get full oversight — occupancy, on-time performance, driver logs and exportable reports.',
                                'M4 4v16h16M8 16V9m4 7V5m4 11v-6m4 6v-3',
                                'bg-error-50 text-error-500',
                            ],
                        ] as [$title, $desc, $icon, $iconClasses])
                            <div class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-7 shadow-theme-sm transition duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-theme-lg">
                                <span class="mb-5 inline-flex size-12 items-center justify-center rounded-xl {{ $iconClasses }} transition-transform duration-300 group-hover:scale-110">
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                                </span>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                                <p class="mt-2 text-theme-sm leading-relaxed text-gray-500">{{ $desc }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ============ HOW IT WORKS ============ --}}
            <section id="how-it-works" class="scroll-mt-24 bg-gray-50/60">
                <div class="mx-auto w-full max-w-7xl px-6 py-20 lg:px-8 lg:py-28">
                    <div class="mx-auto max-w-2xl text-center">
                        <span class="text-theme-sm font-semibold tracking-widest text-brand-600 uppercase">How it works</span>
                        <h2 class="mt-3 text-title-md font-semibold tracking-tight text-gray-900 lg:text-title-lg">Live in three simple steps</h2>
                        <p class="mt-4 text-theme-xl leading-relaxed text-gray-500">From device to dashboard in under an hour — no complex setup required.</p>
                    </div>

                    <div class="mt-16 grid gap-6 lg:grid-cols-3">
                        @foreach ([
                            [
                                '01',
                                'Install the tracker',
                                'Mount a compact GPS device on each bus, or simply use the driver\'s smartphone app to broadcast location.',
                                'M12 4v16m-7-7h14',
                            ],
                            [
                                '02',
                                'Monitor in real time',
                                'Buses appear instantly on your live map with position, speed, route, and estimated arrival at every stop.',
                                'M3 12a9 9 0 1 1 18 0 9 9 0 0 1-18 0zm6 0 2 2 4-4',
                            ],
                            [
                                '03',
                                'Stay notified',
                                'Parents and staff receive automatic alerts when a bus departs, arrives, or runs behind schedule.',
                                'M22 17l-2-1V10a8 8 0 1 0-16 0v6l-2 1v1h20v-1zM12 21a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2z',
                            ],
                        ] as [$num, $title, $desc, $icon])
                            <div class="relative rounded-2xl border border-gray-200 bg-white p-8 shadow-theme-sm">
                                <span class="text-5xl font-bold tracking-tight text-brand-100">{{ $num }}</span>
                                <span class="mt-4 mb-5 inline-flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-500">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                                </span>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                                <p class="mt-2 text-theme-sm leading-relaxed text-gray-500">{{ $desc }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ============ WHO IT'S FOR ============ --}}
            <section id="for-who" class="scroll-mt-24 bg-white">
                <div class="mx-auto w-full max-w-7xl px-6 py-20 lg:px-8 lg:py-28">
                    <div class="mx-auto max-w-2xl text-center">
                        <span class="text-theme-sm font-semibold tracking-widest text-brand-600 uppercase">Who it's for</span>
                        <h2 class="mt-3 text-title-md font-semibold tracking-tight text-gray-900 lg:text-title-lg">Built for everyone on the road</h2>
                        <p class="mt-4 text-theme-xl leading-relaxed text-gray-500">Role-based dashboards give each person exactly what they need — nothing more.</p>
                    </div>

                    <div class="mt-16 grid gap-6 lg:grid-cols-3">
                        <div class="rounded-2xl bg-brand-50/70 p-8 ring-1 ring-brand-100">
                            <span class="mb-5 inline-flex size-12 items-center justify-center rounded-xl bg-brand-500 text-white">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2m5-14a4 4 0 1 0 0 8 4 4 0 0 0 0-8zm9 2v6m-3-3h6"/></svg>
                            </span>
                            <h3 class="text-xl font-semibold text-gray-900">For Parents</h3>
                            <p class="mt-2 text-theme-sm leading-relaxed text-gray-600">Peace of mind, every morning.</p>
                            <ul class="mt-6 space-y-3 text-theme-sm text-gray-700">
                                @foreach (['Live bus location & ETA', 'Arrival and departure alerts', 'Child onboard status', 'Delay notifications'] as $item)
                                    <li class="flex items-start gap-3">
                                        <svg class="mt-0.5 size-4 shrink-0 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-10"/></svg>
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="rounded-2xl bg-gray-50 p-8 ring-1 ring-gray-100">
                            <span class="mb-5 inline-flex size-12 items-center justify-center rounded-xl bg-gray-900 text-white">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20m-17-9 8 9-8 9m9-18 8 9-8 9"/></svg>
                            </span>
                            <h3 class="text-xl font-semibold text-gray-900">For Drivers</h3>
                            <p class="mt-2 text-theme-sm leading-relaxed text-gray-600">Stay on route, stay on time.</p>
                            <ul class="mt-6 space-y-3 text-theme-sm text-gray-700">
                                @foreach (['Turn-by-turn navigation', 'One-tap stop confirmations', 'Daily trip & incident logs', 'Emergency assistance'] as $item)
                                    <li class="flex items-start gap-3">
                                        <svg class="mt-0.5 size-4 shrink-0 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-10"/></svg>
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="rounded-2xl bg-orange-50/70 p-8 ring-1 ring-orange-100">
                            <span class="mb-5 inline-flex size-12 items-center justify-center rounded-xl bg-orange-500 text-white">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4v16h16M8 16V9m4 7V5m4 11v-6m4 6v-3"/></svg>
                            </span>
                            <h3 class="text-xl font-semibold text-gray-900">For Administrators</h3>
                            <p class="mt-2 text-theme-sm leading-relaxed text-gray-600">Total fleet control from one screen.</p>
                            <ul class="mt-6 space-y-3 text-theme-sm text-gray-700">
                                @foreach (['Full fleet map & live board', 'Route & schedule builder', 'On-time performance reports', 'Role-based access control'] as $item)
                                    <li class="flex items-start gap-3">
                                        <svg class="mt-0.5 size-4 shrink-0 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-10"/></svg>
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============ TESTIMONIALS ============ --}}
            <section id="testimonials" class="scroll-mt-24 bg-gray-50/60">
                <div class="mx-auto w-full max-w-7xl px-6 py-20 lg:px-8 lg:py-28">
                    <div class="mx-auto max-w-2xl text-center">
                        <span class="text-theme-sm font-semibold tracking-widest text-brand-600 uppercase">Customers</span>
                        <h2 class="mt-3 text-title-md font-semibold tracking-tight text-gray-900 lg:text-title-lg">Loved by transport teams</h2>
                        <p class="mt-4 text-theme-xl leading-relaxed text-gray-500">Hear from the operators who moved their fleets to BusTrack.</p>
                    </div>

                    <div class="mt-16 grid gap-6 lg:grid-cols-3">
                        @foreach ([
                            ['Sarah Mitchell', 'Transport Director, Green Valley Schools', 'Our mornings used to start with a dozen phone calls. Now parents watch the bus live and we resolve delays before anyone notices. It transformed how we operate.', 'SM', '#2563EB'],
                            ['Rajesh Verma', 'Operations Head, Metro City Transit', 'The route manager cut our planning time by half, and the on-time reports give our board numbers we can actually act on.', 'RV', '#FB6514'],
                            ['Emily Carter', 'Parent of two riders', 'I know the moment my kids step on the bus. The arrival alerts alone are worth it — no more waiting in the rain.', 'EC', '#12B76A'],
                        ] as [$name, $role, $quote, $initials, $color])
                            <figure class="flex flex-col rounded-2xl border border-gray-200 bg-white p-8 shadow-theme-sm">
                                <div class="flex items-center gap-1 text-warning-500">
                                    @for ($i = 0; $i < 5; $i++)
                                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.9L10 15l-5.2 2.7 1-5.9L1.5 7.7l5.9-.9L10 1.5z"/></svg>
                                    @endfor
                                </div>
                                <blockquote class="mt-5 flex-1 text-theme-xl leading-relaxed text-gray-700">"{{ $quote }}"</blockquote>
                                <figcaption class="mt-7 flex items-center gap-3">
                                    <span class="flex size-11 items-center justify-center rounded-full text-theme-sm font-semibold text-white" style="background: {{ $color }}">{{ $initials }}</span>
                                    <div>
                                        <p class="text-theme-sm font-semibold text-gray-900">{{ $name }}</p>
                                        <p class="text-theme-xs text-gray-500">{{ $role }}</p>
                                    </div>
                                </figcaption>
                            </figure>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ============ FAQ ============ --}}
            <section id="faq" class="scroll-mt-24 bg-white">
                <div class="mx-auto w-full max-w-3xl px-6 py-20 lg:px-8 lg:py-28">
                    <div class="mx-auto max-w-2xl text-center">
                        <span class="text-theme-sm font-semibold tracking-widest text-brand-600 uppercase">FAQ</span>
                        <h2 class="mt-3 text-title-md font-semibold tracking-tight text-gray-900 lg:text-title-lg">Frequently asked questions</h2>
                    </div>

                    <div class="mt-12 space-y-4">
                        @foreach ([
                            ['Do I need to buy GPS hardware?', 'No. While we support dedicated GPS devices for the highest accuracy, your drivers can simply install the companion app and broadcast location from their smartphones — zero hardware cost to start.'],
                            ['How often is the location updated?', 'Bus positions stream to the dashboard every few seconds. Parents and admins see movement, speed, and ETAs that stay fresh throughout the trip.'],
                            ['Can parents see more than one bus or child?', 'Yes. Parent accounts can follow multiple children and buses. You choose exactly which stops and buses appear on your personalized view.'],
                            ['Is my fleet data secure?', 'Absolutely. All accounts are protected with role-based permissions, and sensitive fleet data is never shared outside your organization.'],
                            ['How long does setup take?', 'Most schools go live in under an hour: create your account, add buses and routes, then invite drivers and parents by email or SMS.'],
                        ] as [$q, $a])
                            <div x-data="{ open: false }" class="overflow-hidden rounded-2xl border border-gray-200 bg-white transition hover:border-brand-200">
                                <button type="button" @click="open = !open" class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left">
                                    <span class="text-base font-semibold text-gray-900">{{ $q }}</span>
                                    <svg class="size-5 shrink-0 text-gray-400 transition-transform duration-300" :class="open ? 'rotate-45' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                                </button>
                                <div x-show="open" x-cloak x-transition.opacity.duration.200ms>
                                    <p class="px-6 pb-6 text-theme-sm leading-relaxed text-gray-500">{{ $a }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ============ CTA ============ --}}
            <section class="bg-white pb-20 lg:pb-28">
                <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
                    <div class="relative overflow-hidden rounded-3xl bg-gray-950 px-8 py-16 text-center lg:px-16 lg:py-20">
                        <div class="pointer-events-none absolute inset-0">
                            <div class="absolute -top-32 left-1/4 size-72 rounded-full bg-brand-500/30 blur-3xl"></div>
                            <div class="absolute -bottom-32 right-1/4 size-72 rounded-full bg-orange-500/20 blur-3xl"></div>
                            <svg class="absolute inset-0 h-full w-full opacity-20" aria-hidden="true">
                                <defs>
                                    <pattern id="grid-dark" width="40" height="40" patternUnits="userSpaceOnUse">
                                        <path d="M40 0H0V40" fill="none" stroke="#ffffff" stroke-width="1"/>
                                    </pattern>
                                </defs>
                                <rect width="100%" height="100%" fill="url(#grid-dark)"/>
                            </svg>
                        </div>
                        <div class="relative">
                            <h2 class="mx-auto max-w-2xl text-title-md font-semibold tracking-tight text-white lg:text-title-lg">
                                Start tracking your fleet today
                            </h2>
                            <p class="mx-auto mt-4 max-w-xl text-theme-xl leading-relaxed text-gray-300">
                                Join the schools and cities that never wonder where their buses are. Free to get started, no credit card required.
                            </p>
                            <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-7 py-3.5 text-base font-semibold text-gray-950 shadow-theme-lg transition hover:bg-brand-50">
                                        Get started free
                                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                                    </a>
                                @endif
                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-white/25 px-7 py-3.5 text-base font-semibold text-white transition hover:bg-white/10">
                                        Log in to your fleet
                                    </a>
                                @endif
                            </div>
                            <p class="mt-6 text-theme-sm text-gray-400">Free 30-day trial · No credit card · Cancel anytime</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        {{-- ============ FOOTER ============ --}}
        <footer class="border-t border-gray-100 bg-gray-50/60">
            <div class="mx-auto w-full max-w-7xl px-6 py-14 lg:px-8">
                <div class="grid gap-10 lg:grid-cols-5">
                    <div class="lg:col-span-2">
                        <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                            <span class="flex size-9 items-center justify-center rounded-xl bg-brand-500">
                                <svg class="size-5 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M6 16.5v-6a6 6 0 0 1 12 0v6a2.25 2.25 0 0 1-2.25 2.25h-1.5v1.5h-1.5v-1.5h-3v1.5h-1.5v-1.5H8.25A2.25 2.25 0 0 1 6 16.5Zm2.25-5.25a.75.75 0 0 0 0 1.5h7.5a.75.75 0 0 0 0-1.5h-7.5Zm0 3a.75.75 0 0 0 0 1.5h7.5a.75.75 0 0 0 0-1.5h-7.5Z"/>
                                </svg>
                            </span>
                            <span class="text-xl font-semibold tracking-tight text-gray-900">Bus<span class="text-brand-500">Track</span></span>
                        </a>
                        <p class="mt-4 max-w-sm text-theme-sm leading-relaxed text-gray-500">
                            Real-time GPS bus tracking software for schools, cities, and fleet operators. Built for punctual, transparent transport.
                        </p>
                        <div class="mt-6 flex gap-3">
                            @foreach ([['M9 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8zm0 10H6a6 6 0 0 1 0-12h3a6 6 0 0 1 0 12zm9-9h.01', 'twitter'], ['M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 4.6a5.4 5.4 0 1 1 0 10.8 5.4 5.4 0 0 1 0-10.8zm0 8.7a3.3 3.3 0 1 1 0-6.6 3.3 3.3 0 0 1 0 6.6zm5.2-9a1.2 1.2 0 1 0 0-2.4 1.2 1.2 0 0 0 0 2.4z', 'instagram'], ['M20.6 4.9A14.7 14.7 0 0 0 16.6 3l-.4 1a13 13 0 0 1 3.4 1.7c-5.6-3-13-2.6-18 .3-.5-.7-1-1.4-1.4-2.2C4.8 5.3 2 7.7.6 11.4c1.5-.7 3-1.2 4.6-1.5A8.7 8.7 0 0 0 3.5 12c3 2.4 7.5 3.4 11.3 1.8a9 9 0 0 0 3.6-3.7c1-2.3 1.4-4.6 1-6.8l1.2-.4z', 'linkedin']] as [$icon, $label])
                                <a href="#" class="flex size-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition hover:border-brand-200 hover:text-brand-500" aria-label="{{ $label }}">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="currentColor"><path d="{{ $icon }}"/></svg>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h4 class="text-theme-sm font-semibold text-gray-900">Product</h4>
                        <ul class="mt-4 space-y-3 text-theme-sm text-gray-500">
                            <li><a href="#features" class="transition hover:text-gray-900">Features</a></li>
                            <li><a href="#how-it-works" class="transition hover:text-gray-900">How it works</a></li>
                            <li><a href="#for-who" class="transition hover:text-gray-900">For parents</a></li>
                            <li><a href="#for-who" class="transition hover:text-gray-900">For drivers</a></li>
                            <li><a href="#faq" class="transition hover:text-gray-900">FAQ</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-theme-sm font-semibold text-gray-900">Company</h4>
                        <ul class="mt-4 space-y-3 text-theme-sm text-gray-500">
                            <li><a href="#" class="transition hover:text-gray-900">About</a></li>
                            <li><a href="#" class="transition hover:text-gray-900">Careers</a></li>
                            <li><a href="#" class="transition hover:text-gray-900">Press kit</a></li>
                            <li><a href="#" class="transition hover:text-gray-900">Contact</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-theme-sm font-semibold text-gray-900">Legal</h4>
                        <ul class="mt-4 space-y-3 text-theme-sm text-gray-500">
                            <li><a href="#" class="transition hover:text-gray-900">Privacy policy</a></li>
                            <li><a href="#" class="transition hover:text-gray-900">Terms of service</a></li>
                            <li><a href="#" class="transition hover:text-gray-900">Security</a></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-gray-200 pt-8 sm:flex-row">
                    <p class="text-theme-xs text-gray-400">© <span id="year"></span> BusTrack. All rights reserved.</p>
                    <p class="flex items-center gap-1.5 text-theme-xs text-gray-400">
                        <svg class="size-4 text-success-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l7 4v6c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-4z"/><path d="M9 12l2 2 4-4"/></svg>
                        All systems operational
                    </p>
                </div>
            </div>
        </footer>
    </body>
</html>
