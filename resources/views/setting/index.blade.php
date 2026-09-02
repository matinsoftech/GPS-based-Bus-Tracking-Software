<x-app-layout page="settings">

    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6" x-data="settingsPage()">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Settings
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage your platform settings, branding and contact information.
                </p>
            </div>

        </div>


        {{-- Success Message --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
                class="mb-6 flex items-center gap-3 rounded-lg border border-green-100 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-400">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="flex-1">
                    {{ session('success') }}
                </span>

                <button type="button" @click="show = false"
                    class="ml-4 rounded p-1 text-lg font-medium leading-none hover:bg-green-100 dark:hover:bg-green-900/40">
                    &times;
                </button>
            </div>
        @endif


        {{-- Validation Errors --}}
        @if ($errors->any())

            <div
                class="mb-6 flex gap-3 rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-400">

                <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>

                <div>
                    <p class="mb-1 font-medium">Please fix the following:</p>
                    <ul class="list-disc space-y-0.5 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>

            </div>

        @endif


        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="pb-20">

            @csrf
            @method('PUT')


            {{-- Branding --}}
            <div
                class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                <div class="flex items-center gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 016.828 0L20 16m-2-2l1.586-1.586a2 2 0 011.414-.586M14 10h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Branding
                        </h2>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                            Manage the logo and favicon used throughout the application.
                        </p>
                    </div>
                </div>


                <div class="grid gap-6 p-5 lg:grid-cols-2">


                    {{-- Logo --}}
                    <div>

                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Platform Logo
                        </label>

                        <label @dragover.prevent="logoDrag = true" @dragleave.prevent="logoDrag = false"
                            @drop.prevent="logoDrag = false; onDrop($event, 'logo')"
                            :class="logoDrag ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-500/5' :
                                'border-gray-200 dark:border-gray-700'"
                            class="group relative flex h-48 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed bg-gray-50 transition-colors hover:border-brand-400 dark:bg-gray-900">

                            <template x-if="logoPreview">
                                <img :src="logoPreview" alt="Logo preview"
                                    class="max-h-36 max-w-[80%] object-contain">
                            </template>

                            <template x-if="!logoPreview">
                                <div class="pointer-events-none text-center">
                                    @if ($settings->logo)
                                        <img src="{{ asset('storage/' . $settings->logo) }}"
                                            alt="{{ $settings->platform_name }}"
                                            class="mx-auto max-h-28 max-w-[70%] object-contain">
                                    @else
                                        <div
                                            class="mx-auto mb-2 flex h-14 w-14 items-center justify-center rounded-xl bg-gray-200 text-gray-400 group-hover:bg-brand-50 group-hover:text-brand-400 dark:bg-gray-700">
                                            <svg class="h-7 w-7" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            No logo uploaded
                                        </p>
                                    @endif
                                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                        Click or drag a file here to replace
                                    </p>
                                </div>
                            </template>

                            <input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="hidden"
                                @change="onSelect($event, 'logo')">
                        </label>

                        <div class="mt-2 flex items-center justify-between">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                PNG, JPG, JPEG or WEBP · Max 2MB
                            </p>

                            @if ($settings->logo)
                                <button type="button" onclick="deleteLogo()"
                                    class="inline-flex items-center gap-1 text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete Logo
                                </button>
                            @endif
                        </div>

                    </div>


                    {{-- Favicon --}}
                    <div>

                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Favicon
                        </label>

                        <label @dragover.prevent="faviconDrag = true" @dragleave.prevent="faviconDrag = false"
                            @drop.prevent="faviconDrag = false; onDrop($event, 'favicon')"
                            :class="faviconDrag ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-500/5' :
                                'border-gray-200 dark:border-gray-700'"
                            class="group relative flex h-48 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed bg-gray-50 transition-colors hover:border-brand-400 dark:bg-gray-900">

                            <template x-if="faviconPreview">
                                <img :src="faviconPreview" alt="Favicon preview" class="h-24 w-24 object-contain">
                            </template>

                            <template x-if="!faviconPreview">
                                <div class="pointer-events-none text-center">
                                    @if ($settings->favicon)
                                        <img src="{{ asset('storage/' . $settings->favicon) }}" alt="Favicon"
                                            class="mx-auto h-20 w-20 object-contain">
                                    @else
                                        <div
                                            class="mx-auto mb-2 flex h-14 w-14 items-center justify-center rounded-xl bg-gray-200 text-gray-400 group-hover:bg-brand-50 group-hover:text-brand-400 dark:bg-gray-700">
                                            <svg class="h-7 w-7" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            No favicon uploaded
                                        </p>
                                    @endif
                                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                        Click or drag a file here to replace
                                    </p>
                                </div>
                            </template>

                            <input type="file" name="favicon"
                                accept="image/png,image/jpeg,image/webp,image/x-icon,.ico" class="hidden"
                                @change="onSelect($event, 'favicon')">
                        </label>

                        <div class="mt-2 flex items-center justify-between">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                PNG, JPG, JPEG, WEBP or ICO · Max 1MB
                            </p>

                            @if ($settings->favicon)
                                <button type="button" onclick="deleteFavicon()"
                                    class="inline-flex items-center gap-1 text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete Favicon
                                </button>
                            @endif
                        </div>

                    </div>

                </div>

            </div>


            {{-- Platform Information --}}
            <div
                class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                <div class="flex items-center gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </span>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Platform Information
                    </h2>
                </div>


                <div class="p-5">

                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Platform Name
                    </label>

                    <input type="text" name="platform_name"
                        value="{{ old('platform_name', $settings->platform_name) }}" placeholder="Enter platform name"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition-colors focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white">

                    @error('platform_name')
                        <p class="mt-1 text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </div>


            {{-- Contact Information --}}
            <div
                class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                <div class="flex items-center gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2 3.5A1.5 1.5 0 013.5 2h1.148a1.5 1.5 0 011.465 1.175l.716 3.223a1.5 1.5 0 01-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 006.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 011.767-1.052l3.223.716A1.5 1.5 0 0118 15.352V16.5a1.5 1.5 0 01-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 012.43 8.326 13.019 13.019 0 012 5V3.5z" />
                        </svg>
                    </span>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Contact Information
                    </h2>
                </div>


                <div class="grid gap-5 p-5 md:grid-cols-2">


                    {{-- Email --}}
                    <div>

                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Support Email
                        </label>

                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <input type="email" name="support_email"
                                value="{{ old('support_email', $settings->support_email) }}"
                                placeholder="support@example.com"
                                class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 transition-colors focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>

                    </div>


                    {{-- Phone --}}
                    <div>

                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Support Phone
                        </label>

                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <input type="text" name="support_phone"
                                value="{{ old('support_phone', $settings->support_phone) }}" placeholder="98XXXXXXXX"
                                class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 transition-colors focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>

                    </div>


                    {{-- Address --}}
                    <div class="md:col-span-2">

                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Address
                        </label>

                        <textarea name="address" rows="3" placeholder="Enter address"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-colors focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white">{{ old('address', $settings->address) }}</textarea>

                    </div>

                </div>

            </div>


            {{-- Social Links --}}
            <div
                class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                <div class="flex items-center gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                        </svg>
                    </span>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Social Links
                    </h2>
                </div>


                <div class="grid gap-5 p-5 md:grid-cols-3">

                    <div>

                        <label
                            class="mb-2 flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM3.6 9h16.8M3.6 15h16.8M11.5 3a17 17 0 000 18M12.5 3a17 17 0 010 18" />
                            </svg>
                            Website
                        </label>

                        <input type="url" name="website" value="{{ old('website', $settings->website) }}"
                            placeholder="https://example.com"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-colors focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white">

                    </div>


                    <div>

                        <label
                            class="mb-2 flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M22 12.06C22 6.505 17.523 2 12 2S2 6.505 2 12.06c0 5.02 3.657 9.184 8.438 9.94v-7.03H7.898v-2.91h2.54V9.845c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.459h-1.26c-1.242 0-1.63.771-1.63 1.562v1.878h2.773l-.443 2.91h-2.33V22c4.78-.756 8.437-4.92 8.437-9.94z" />
                            </svg>
                            Facebook
                        </label>

                        <input type="url" name="facebook" value="{{ old('facebook', $settings->facebook) }}"
                            placeholder="https://facebook.com/..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-colors focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white">

                    </div>


                    <div>

                        <label
                            class="mb-2 flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="18" rx="5" stroke-width="2" />
                                <circle cx="12" cy="12" r="4" stroke-width="2" />
                                <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
                            </svg>
                            Instagram
                        </label>

                        <input type="url" name="instagram" value="{{ old('instagram', $settings->instagram) }}"
                            placeholder="https://instagram.com/..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-colors focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white">

                    </div>

                </div>

            </div>


            {{-- <div
                class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                <div class="p-5">

                    <label class="flex cursor-pointer items-center gap-3">

                        <input type="checkbox" name="maintenance_mode" value="1" @checked($settings->maintenance_mode)
                            class="h-5 w-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500">

                        <div>

                            <span class="block text-sm font-medium text-gray-900 dark:text-white">
                                Maintenance Mode
                            </span>

                            <span class="block text-xs text-gray-500 dark:text-gray-400">
                                Temporarily disable access to the platform.
                            </span>

                        </div>

                    </label>

                </div>

            </div> --}}


            {{-- Sticky Save Bar --}}
            <div
                class="fixed inset-x-0 bottom-0 z-20 border-t border-gray-200 bg-white/90 px-4 py-3 backdrop-blur md:px-6 dark:border-gray-800 dark:bg-gray-900/90">
                <div class="mx-auto flex max-w-(--breakpoint-2xl) items-center justify-end gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Save Settings
                    </button>
                </div>
            </div>

        </form>


        {{-- Delete Logo --}}
        @if ($settings->logo)
            <form id="delete-logo-form" action="{{ route('settings.logo.delete') }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif


        {{-- Delete Favicon --}}
        @if ($settings->favicon)
            <form id="delete-favicon-form" action="{{ route('settings.favicon.delete') }}" method="POST"
                class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif

    </div>


    @push('scripts')
        <script>
            function deleteLogo() {
                if (confirm('Are you sure you want to delete the current logo?')) {
                    document.getElementById('delete-logo-form').submit();
                }
            }

            function deleteFavicon() {
                if (confirm('Are you sure you want to delete the current favicon?')) {
                    document.getElementById('delete-favicon-form').submit();
                }
            }

            function settingsPage() {
                return {
                    logoDrag: false,
                    faviconDrag: false,
                    logoPreview: null,
                    faviconPreview: null,

                    onSelect(event, field) {
                        const file = event.target.files[0];
                        this.preview(file, field);
                    },

                    onDrop(event, field) {
                        const file = event.dataTransfer.files[0];
                        if (!file) return;

                        // Assign the dropped file to the matching hidden input
                        const input = event.currentTarget.querySelector('input[type="file"]');
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        input.files = dt.files;

                        this.preview(file, field);
                    },

                    preview(file, field) {
                        if (!file) return;
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            if (field === 'logo') {
                                this.logoPreview = e.target.result;
                            } else {
                                this.faviconPreview = e.target.result;
                            }
                        };
                        reader.readAsDataURL(file);
                    },
                }
            }
        </script>
    @endpush

</x-app-layout>
