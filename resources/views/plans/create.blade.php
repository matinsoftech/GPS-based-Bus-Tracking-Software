<x-app-layout page="plans">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create Plan</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Add a new subscription plan with limits and features.
                </p>
            </div>
            <a href="{{ route('plans.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                Back
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('plans.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                {{-- Basic Info --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Basic Information</h2>
                    <div class="mt-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Plan Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            placeholder="e.g. Basic, Standard, Premium"
                            class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div class="mt-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
                        </label>
                    </div>
                </div>

                {{-- Pricing --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pricing</h2>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="monthly_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monthly Price (₹)</label>
                            <input type="number" id="monthly_price" name="monthly_price" value="{{ old('monthly_price') }}" required
                                min="0" step="0.01" placeholder="0.00"
                                class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label for="yearly_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Yearly Price (₹)</label>
                            <input type="number" id="yearly_price" name="yearly_price" value="{{ old('yearly_price') }}" required
                                min="0" step="0.01" placeholder="0.00"
                                class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>
                </div>

                {{-- Limits --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Limits</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Leave blank for unlimited.
                    </p>
                    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="max_buses" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Buses</label>
                            <input type="number" id="max_buses" name="max_buses" value="{{ old('max_buses') }}"
                                min="0" placeholder="Unlimited"
                                class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label for="max_students" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Students</label>
                            <input type="number" id="max_students" name="max_students" value="{{ old('max_students') }}"
                                min="0" placeholder="Unlimited"
                                class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label for="max_parents" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Parents</label>
                            <input type="number" id="max_parents" name="max_parents" value="{{ old('max_parents') }}"
                                min="0" placeholder="Unlimited"
                                class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label for="max_drivers" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Drivers</label>
                            <input type="number" id="max_drivers" name="max_drivers" value="{{ old('max_drivers') }}"
                                min="0" placeholder="Unlimited"
                                class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label for="max_routes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Routes</label>
                            <input type="number" id="max_routes" name="max_routes" value="{{ old('max_routes') }}"
                                min="0" placeholder="Unlimited"
                                class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label for="max_devices" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Devices</label>
                            <input type="number" id="max_devices" name="max_devices" value="{{ old('max_devices') }}"
                                min="0" placeholder="Unlimited"
                                class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>
                </div>

                {{-- Features --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Features</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Select the features included in this plan.
                    </p>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-white/[0.03]">
                            <input type="checkbox" name="feature_live_tracking" value="1" {{ old('feature_live_tracking', '1') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Live Tracking</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-white/[0.03]">
                            <input type="checkbox" name="feature_parent_app" value="1" {{ old('feature_parent_app', '1') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Parent App</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-white/[0.03]">
                            <input type="checkbox" name="feature_notifications" value="1" {{ old('feature_notifications', '1') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Notifications</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-white/[0.03]">
                            <input type="checkbox" name="feature_attendance" value="1" {{ old('feature_attendance') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Attendance</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-white/[0.03]">
                            <input type="checkbox" name="feature_reports" value="1" {{ old('feature_reports') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Reports</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-white/[0.03]">
                            <input type="checkbox" name="feature_analytics" value="1" {{ old('feature_analytics') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Analytics</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                        Create Plan
                    </button>
                    <a href="{{ route('plans.index') }}"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
