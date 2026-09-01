@php
    /**
     * SIDEBAR CONFIG
     * ----------------------------------------------------------------
     * This is the ONLY place you need to touch to add/remove/reorder
     * sidebar items. Each group is a section (e.g. "MENU", "others").
     * Each item can optionally have a "dropdown" of sub-links.
     *
     * item keys:
     * key -> unique string, used for the Alpine `selected` state
     * label -> text shown to the user
     * route -> Laravel route name (used if no dropdown, or as the
     * link on the parent item itself)
     * active -> string|array of `page` value(s) that should mark
     * this item as active
     * icon -> Blade Heroicons component name (requires
     * "composer require blade-ui-kit/blade-heroicons")
     * e.g. 'heroicon-o-squares-2x2', 'heroicon-o-user'
     * Browse names at https://blade-ui-kit.com/blade-icons?set=1
     * dropdown -> optional array of ['label' => ..., 'route' => ..., 'page' => ...]
     */
    $superAdminMenu = [
        [
            'title' => 'Dashboard',
            'items' => [
                [
                    'key' => 'overview',
                    'label' => 'Dashboard',
                    'route' => 'dashboard',
                    'active' => ['overview', 'dashboard'],
                    'icon' => 'heroicon-o-home',
                    'permission' => null,
                    'dropdown' => null,
                ],
            ],
        ],
        [
            'title' => 'Management',
            'items' => [
                // "User Management" is Super Admin only. The `permission`
                // key is checked by the filter below via the `manage-users`
                // gate, so the item is hidden from every other role.
                [
                    'key' => 'user-management',
                    'label' => 'User ',
                    'route' => 'users.index',
                    'active' => 'user-management',
                    'icon' => 'heroicon-o-user-group',
                    'permission' => 'manage-users',
                    'dropdown' => null,
                ],
                [
                    'key' => 'school-management',
                    'label' => 'School',
                    'route' => 'schools.index',
                    'active' => 'school-management',
                    'icon' => 'heroicon-o-building-library',
                    'permission' => null,
                    'dropdown' => null,
                ],
                [
                    'key' => 'school-admin-management',
                    'label' => 'School Admin',
                    'route' => 'school-admins.index',
                    'active' => 'school-admin-management',
                    'icon' => 'heroicon-o-user-group',
                    'permission' => 'school-admin.view',
                    'dropdown' => null,
                ],
                [
                    'key' => 'parent-management',
                    'label' => 'Parent',
                    'route' => 'parents.index',
                    'active' => 'parent-management',
                    'icon' => 'heroicon-o-users',
                    'permission' => null,
                    'dropdown' => null,
                ],
                [
                    'key' => 'driver-management',
                    'label' => 'Driver',
                    'route' => 'drivers.index',
                    'active' => 'driver-management',
                    'icon' => 'heroicon-o-user-group',
                    'permission' => 'driver.view',
                    'dropdown' => null,
                ],
                [
                    'key' => 'route-management',
                    'label' => 'Route',
                    'route' => 'routes.index',
                    'active' => 'route-management',
                    'icon' => 'heroicon-o-map',
                    'permission' => null,
                    'dropdown' => null,
                ],
                [
                    'key' => 'buses',
                    'label' => 'Buses',
                    'route' => 'buses.index',
                    'active' => 'buses',
                    'icon' => 'heroicon-o-truck',
                    'permission' => 'bus.view',
                    'dropdown' => null,
                ],
                // ['key' => 'trip-management', 'label' => 'Trip', 'route' => 'dashboard', 'active' => 'trip-management', 'icon' => 'heroicon-o-clipboard-document-list', 'permission' => null, 'dropdown' => null],
            ],
        ],
        [
            'title' => 'Monitoring',
            'items' => [
                // ['key' => 'live-tracking', 'label' => 'Live Tracking', 'route' => 'bus_location', 'active' => 'live-tracking', 'icon' => 'heroicon-o-eye', 'permission' => null, 'dropdown' => null],
                [
                    'key' => 'vehicle-tracking',
                    'label' => 'Vehicle Tracking',
                    'route' => 'vehicle-tracking',
                    'active' => 'vehicle-tracking',
                    'icon' => 'heroicon-o-map-pin',
                    'permission' => null,
                    'dropdown' => null,
                ],
                [
                    'key' => 'attendance',
                    'label' => 'Attendance',
                    'route' => 'attendance.index',
                    'active' => 'attendance',
                    'icon' => 'heroicon-o-document-check',
                    'permission' => 'attendance.view',
                    'dropdown' => null,
                ],
                // ['key' => 'notifications', 'label' => 'Notifications', 'route' => 'dashboard', 'active' => 'notifications', 'icon' => 'heroicon-o-bell', 'permission' => null, 'dropdown' => null],
            ],
        ],
        [
            'title' => 'Administration',
            'items' => [
                [
                    'key' => 'roles-permissions',
                    'label' => 'Roles & Permissions',
                    'route' => 'roles.index',
                    'active' => 'roles-permissions',
                    'icon' => 'heroicon-o-shield-check',
                    'permission' => 'role.view',
                    'dropdown' => null,
                ],
                // ['key' => 'settings', 'label' => 'Settings', 'route' => 'dashboard', 'active' => 'settings', 'icon' => 'heroicon-o-cog-6-tooth', 'permission' => null, 'dropdown' => null],
            ],
        ],
        [
            'title' => 'Account',
            'items' => [
                [
                    'key' => 'profile',
                    'label' => 'Profile',
                    'route' => 'profile.edit',
                    'active' => 'profile',
                    'icon' => 'heroicon-o-user-circle',
                    'permission' => null,
                    'dropdown' => null,
                ],
            ],
        ],
    ];

    $principalMenu = [
        [
            'title' => 'Dashboard',
            'items' => [
                [
                    'key' => 'dashboard',
                    'label' => 'Dashboard',
                    'route' => 'principal.dashboard',
                    'active' => ['overview', 'dashboard'],
                    'icon' => 'heroicon-o-home',
                    'permission' => null,
                    'dropdown' => null,
                ],
            ],
        ],
        [
            'title' => 'Management',
            'items' => [
                [
                    'key' => 'students',
                    'label' => 'Students',
                    'route' => 'students.index',
                    'active' => 'students',
                    'icon' => 'heroicon-o-academic-cap',
                    'permission' => null,
                    'dropdown' => null,
                ],
                [
                    'key' => 'parents',
                    'label' => 'Parents',
                    'route' => 'parents.index',
                    'active' => 'parents',
                    'icon' => 'heroicon-o-users',
                    'permission' => null,
                    'dropdown' => null,
                ],
                [
                    'key' => 'drivers',
                    'label' => 'Drivers',
                    'route' => 'drivers.index',
                    'active' => 'drivers',
                    'icon' => 'heroicon-o-user-group',
                    'permission' => 'driver.view',
                    'dropdown' => null,
                ],
                [
                    'key' => 'buses',
                    'label' => 'Buses',
                    'route' => 'buses.index',
                    'active' => 'buses',
                    'icon' => 'heroicon-o-truck',
                    'permission' => 'bus.view',
                    'dropdown' => null,
                ],
                [
                    'key' => 'school-admin-management',
                    'label' => 'School Admin Management',
                    'route' => 'school-admins.index',
                    'active' => 'school-admin-management',
                    'icon' => 'heroicon-o-user-group',
                    'permission' => 'school-admin.view',
                    'dropdown' => null,
                ],
                [
                    'key' => 'route-management',
                    'label' => 'Routes',
                    'route' => 'routes.index',
                    'active' => 'route-management',
                    'icon' => 'heroicon-o-map',
                    'permission' => null,
                    'dropdown' => null,
                ],
            ],
        ],
        [
            'title' => 'Trip Management',
            'items' => [
                [
                    'key' => 'trips',
                    'label' => 'Trips',
                    'route' => 'principal.trips.index',
                    'active' => 'trips',
                    'icon' => 'heroicon-o-clipboard-document-list',
                    'permission' => 'trip.view',
                    'dropdown' => null,
                ],
                [
                    'key' => 'attendance',
                    'label' => 'Attendance',
                    'route' => 'attendance.index',
                    'active' => 'attendance',
                    'icon' => 'heroicon-o-document-check',
                    'permission' => 'attendance.view',
                    'dropdown' => null,
                ],
            ],
        ],
        [
            'title' => 'Monitoring',
            'items' => [
                // ['key' => 'live-tracking', 'label' => 'Live Tracking', 'route' => 'bus_location', 'active' => 'live-tracking', 'icon' => 'heroicon-o-eye', 'permission' => null, 'dropdown' => null],
                [
                    'key' => 'vehicle-tracking',
                    'label' => 'Vehicle Tracking',
                    'route' => 'principal.vehicle-tracking',
                    'active' => 'vehicle-tracking',
                    'icon' => 'heroicon-o-map-pin',
                    'permission' => null,
                    'dropdown' => null,
                ],
                // ['key' => 'notifications', 'label' => 'Notifications', 'route' => 'dashboard', 'active' => 'notifications', 'icon' => 'heroicon-o-bell', 'permission' => null, 'dropdown' => null],
            ],
        ],
        [
            'title' => 'Account',
            'items' => [
                [
                    'key' => 'profile',
                    'label' => 'Profile',
                    'route' => 'profile.edit',
                    'active' => 'profile',
                    'icon' => 'heroicon-o-user-circle',
                    'permission' => null,
                    'dropdown' => null,
                ],
            ],
        ],
    ];

    $driverMenu = [
        [
            'title' => 'Dashboard',
            'items' => [
                [
                    'key' => 'overview',
                    'label' => 'Dashboard',
                    'route' => 'driver.dashboard',
                    'active' => ['overview', 'dashboard'],
                    'icon' => 'heroicon-o-home',
                    'permission' => null,
                    'dropdown' => null,
                ],
            ],
        ],
        [
            'title' => 'Trip Management',
            'items' => [
                [
                    'key' => 'trips',
                    'label' => 'Trips',
                    'route' => 'driver.trips.index',
                    'active' => 'trips',
                    'icon' => 'heroicon-o-clipboard-document-list',
                    'permission' => 'trip.view',
                    'dropdown' => null,
                ],
                [
                    'key' => 'attendance',
                    'label' => 'Attendance',
                    'route' => 'attendance.index',
                    'active' => 'attendance',
                    'icon' => 'heroicon-o-document-check',
                    'permission' => 'attendance.view',
                    'dropdown' => null,
                ],
            ],
        ],
        [
            'title' => 'Monitoring',
            'items' => [
                [
                    'key' => 'live-tracking',
                    'label' => 'Live Tracking',
                    'route' => 'driver.live-tracking',
                    'active' => 'live-tracking',
                    'icon' => 'heroicon-o-eye',
                    'permission' => null,
                    'dropdown' => null,
                ],
                // ['key' => 'notifications', 'label' => 'Notifications', 'route' => 'driver.dashboard', 'active' => 'notifications', 'icon' => 'heroicon-o-bell', 'permission' => null, 'dropdown' => null],
            ],
        ],
        [
            'title' => 'Account',
            'items' => [
                [
                    'key' => 'profile',
                    'label' => 'Profile',
                    'route' => 'profile.edit',
                    'active' => 'profile',
                    'icon' => 'heroicon-o-user-circle',
                    'permission' => null,
                    'dropdown' => null,
                ],
            ],
        ],
    ];

    $parentMenu = [
        [
            'title' => 'Dashboard',
            'items' => [
                [
                    'key' => 'overview',
                    'label' => 'Dashboard',
                    'route' => 'parent.dashboard',
                    'active' => ['overview', 'dashboard'],
                    'icon' => 'heroicon-o-home',
                    'permission' => null,
                    'dropdown' => null,
                ],
                [
                    'key' => 'my-children',
                    'label' => 'My Students',
                    'route' => 'parent.children',
                    'active' => 'my-children',
                    'icon' => 'heroicon-o-users',
                    'permission' => null,
                    'dropdown' => null,
                ],
            ],
        ],
        [
            'title' => 'Live Tracking',
            'items' => [
                [
                    'key' => 'bus-location',
                    'label' => 'Bus Location',
                    'route' => 'bus_location',
                    'active' => 'bus-location',
                    'icon' => 'heroicon-o-map-pin',
                    'permission' => null,
                    'dropdown' => null,
                ],
            ],
        ],
        // [
        //     'title' => 'Attendance',
        //     'items' => [
        //         ['key' => 'boarding-history', 'label' => 'Boarding History', 'route' => 'parent.dashboard', 'active' => 'boarding-history', 'icon' => 'heroicon-o-arrow-right', 'permission' => null, 'dropdown' => null],
        //         ['key' => 'dropoff-history', 'label' => 'Drop-off History', 'route' => 'parent.dashboard', 'active' => 'dropoff-history', 'icon' => 'heroicon-o-arrow-left', 'permission' => null, 'dropdown' => null],
        //     ],
        // ],
        // [
        //     'title' => 'Notifications',
        //     'items' => [
        //         ['key' => 'notifications', 'label' => 'Notifications', 'route' => 'parent.dashboard', 'active' => 'notifications', 'icon' => 'heroicon-o-bell', 'permission' => null, 'dropdown' => null],
        //     ],
        // ],
        [
            'title' => 'Account',
            'items' => [
                [
                    'key' => 'profile',
                    'label' => 'Profile',
                    'route' => 'profile.edit',
                    'active' => 'profile',
                    'icon' => 'heroicon-o-user-circle',
                    'permission' => null,
                    'dropdown' => null,
                ],
            ],
        ],
    ];

    $studentMenu = [
        [
            'title' => 'Dashboard',
            'items' => [
                [
                    'key' => 'overview',
                    'label' => 'Dashboard',
                    'route' => 'dashboard',
                    'active' => ['overview', 'dashboard'],
                    'icon' => 'heroicon-o-home',
                    'permission' => null,
                    'dropdown' => null,
                ],
            ],
        ],
        [
            'title' => 'Tracking',
            'items' => [
                [
                    'key' => 'bus-tracking',
                    'label' => 'Bus Tracking',
                    'route' => 'dashboard',
                    'active' => 'bus-tracking',
                    'icon' => 'heroicon-o-map',
                    'permission' => null,
                    'dropdown' => null,
                ],
            ],
        ],
        [
            'title' => 'Attendance',
            'items' => [
                [
                    'key' => 'attendance',
                    'label' => 'Attendance',
                    'route' => 'dashboard',
                    'active' => 'attendance',
                    'icon' => 'heroicon-o-document-check',
                    'permission' => null,
                    'dropdown' => null,
                ],
            ],
        ],
        [
            'title' => 'Notifications',
            'items' => [
                // ['key' => 'notifications', 'label' => 'Notifications', 'route' => 'dashboard', 'active' => 'notifications', 'icon' => 'heroicon-o-bell', 'permission' => null, 'dropdown' => null],
            ],
        ],
        [
            'title' => 'Account',
            'items' => [
                [
                    'key' => 'profile',
                    'label' => 'Profile',
                    'route' => 'profile.edit',
                    'active' => 'profile',
                    'icon' => 'heroicon-o-user-circle',
                    'permission' => null,
                    'dropdown' => null,
                ],
            ],
        ],
    ];

    $menu = $superAdminMenu;
    $user = auth()->user();
    $roleNames = $user ? array_map('strtolower', $user->getRoleNames()->all()) : [];

    if ($user && (in_array('school admin', $roleNames, true) || in_array('principal', $roleNames, true))) {
        $menu = $principalMenu;
    } elseif ($user && in_array('driver', $roleNames, true)) {
        $menu = $driverMenu;
    } elseif ($user && in_array('parent', $roleNames, true)) {
        $menu = $parentMenu;
    } elseif ($user && in_array('student', $roleNames, true)) {
        $menu = $studentMenu;
    }

    /**
     * PERMISSION-AWARE FILTERING
     * ----------------------------------------------------------------
     * Each menu item can declare a `permission` key. Items (and dropdown
     * sub-items) the current user is not allowed to view are hidden.
     * Leave the key off for items that require no permission.
     */
    $can = function ($permission) {
        if (empty($permission)) {
            return true;
        }
        return auth()->check() && auth()->user()->can($permission);
    };

    $menu = array_values(
        array_filter(
            array_map(function ($group) use ($can) {
                $items = array_values(
                    array_filter($group['items'], function ($item) use ($can) {
                        if (!$can($item['permission'] ?? null)) {
                            return false;
                        }

                        if (!empty($item['dropdown'])) {
                            $item['dropdown'] = array_values(
                                array_filter($item['dropdown'], fn($sub) => $can($sub['permission'] ?? null)),
                            );

                            return count($item['dropdown']) > 0;
                        }

                        return true;
                    }),
                );

                $group['items'] = $items;

                return $group;
            }, $menu),
            fn($group) => count($group['items']) > 0,
        ),
    );

    // Fallback link for the logo
    $homeRoute = !auth()->check()
        ? route('login')
        : match (true) {
            auth()->user()->hasRole('Super Admin') => route('dashboard'),
            auth()->user()->hasRole('School Admin') => route('principal.dashboard'),
            auth()->user()->hasRole('Driver') => route('driver.dashboard'),
            auth()->user()->hasRole('Parent') => route('parent.dashboard'),
            default => auth()->user()->can('dashboard.view') ? route('dashboard') : route('profile.edit'),
        };

    // Helper: is this item "active" given the current $page variable?
    $isActive = function ($item) use ($page) {
        $active = $item['active'] ?? null;
        if (is_array($active)) {
            return in_array($page, $active, true);
        }
        return $active !== null && $page === $active;
    };
@endphp

<aside :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0">
    <!-- SIDEBAR HEADER -->
    <div :class="sidebarToggle ? 'justify-center' : 'justify-between'"
        class="sidebar-header flex items-center gap-2 pb-7 pt-8">
        <a href="{{ $homeRoute }}">
            @if ($headerSchool)
                <span class="logo school-logo flex items-center gap-2" :class="sidebarToggle ? 'hidden' : ''">
                    @if ($headerSchool->logo)
                        <img class="h-9 w-9 rounded-lg object-cover"
                            src="{{ \Illuminate\Support\Facades\Storage::url($headerSchool->logo) }}"
                            alt="{{ $headerSchool->name }} logo" />
                    @else
                        <img class="dark:hidden h-9 w-9 object-contain" src="/images/logo/schoollogo.png" alt="Logo" />
                        <img class="hidden dark:block h-9 w-9 object-contain" src="/images/logo/schoollogo.png"
                            alt="Logo" />
                    @endif
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $headerSchool->name }}</span>
                </span>
                @if ($headerSchool->logo)
                    <img class="logo-icon h-9 w-9 rounded-lg object-cover"
                        :class="sidebarToggle ? 'lg:block' : 'hidden'"
                        src="{{ \Illuminate\Support\Facades\Storage::url($headerSchool->logo) }}"
                        alt="{{ $headerSchool->name }} logo" />
                @else
                    <img class="logo-icon h-9 w-9 rounded-lg object-cover" :class="sidebarToggle ? 'lg:block' : 'hidden'"
                        src="/images/logo/schoollogo.png" alt="Logo" />
                @endif
            @else
                <span class="logo school-logo flex items-center gap-2" :class="sidebarToggle ? 'hidden' : ''">
                    <img class="h-9 w-9 rounded-lg object-cover dark:hidden" src="/images/logo/schoollogo.png" alt="Logo" />
                    <img class="h-9 w-9 rounded-lg object-cover hidden dark:block" src="/images/logo/schoollogo.png" alt="Logo" />
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">School Bus Tracker</span>
                </span>
                <img class="logo-icon" :class="sidebarToggle ? 'lg:block' : 'hidden'" src="/images/logo/schoollogo.png"
                    alt="Logo" />
            @endif
        </a>
    </div>
    <!-- SIDEBAR HEADER -->

    <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
        <nav x-data="{ selected: $persist('Dashboard') }">

            @foreach ($menu as $group)
                <div>
                    <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                            {{ $group['title'] }}
                        </span>

                        <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                            class="menu-group-icon mx-auto fill-current" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                                fill="" />
                        </svg>
                    </h3>

                    <ul class="mb-6 flex flex-col gap-4">
                        @foreach ($group['items'] as $item)
                            <li>
                                <a href="{{ !empty($item['dropdown']) ? '#' : route($item['route']) }}"
                                    @if (!empty($item['dropdown'])) @click.prevent="selected = (selected === '{{ $item['key'] }}' ? '' : '{{ $item['key'] }}')"
                                    @else @click="selected = (selected === '{{ $item['key'] }}' ? '' : '{{ $item['key'] }}')" @endif
                                    class="menu-item group"
                                    :class="(selected === '{{ $item['key'] }}') || {{ $isActive($item) ? 'true' : 'false' }}
                                        ?
                                        'menu-item-active' : 'menu-item-inactive'">
                                    <span
                                        :class="(selected === '{{ $item['key'] }}') ||
                                        {{ $isActive($item) ? 'true' : 'false' }} ? 'menu-item-icon-active' :
                                            'menu-item-icon-inactive'">
                                        <x-dynamic-component :component="$item['icon']" class="w-6 h-6 shrink-0" />
                                    </span>

                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        {{ $item['label'] }}
                                    </span>

                                    @if (!empty($item['dropdown']))
                                        <span
                                            :class="[(selected === '{{ $item['key'] }}') ? 'menu-item-arrow-active' :
                                                'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : ''
                                            ]">
                                            <x-heroicon-o-chevron-down class="menu-item-arrow w-4 h-4" />
                                        </span>
                                    @endif
                                </a>

                                @if (!empty($item['dropdown']))
                                    <div class="transform translate overflow-hidden"
                                        :class="(selected === '{{ $item['key'] }}') ? 'block' : 'hidden'">
                                        <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                            class="menu-dropdown mt-2 flex flex-col gap-1 pl-9">
                                            @foreach ($item['dropdown'] as $sub)
                                                <li>
                                                    <a href="{{ route($sub['route']) }}"
                                                        class="menu-dropdown-item group"
                                                        :class="{{ $page === $sub['page'] ? 'true' : 'false' }} ?
                                                            'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                                        {{ $sub['label'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

        </nav>

    </div>
</aside>
