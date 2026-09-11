<x-app-layout page="profile">
    @php
        $initial = strtoupper(substr($user->name, 0, 1));

        $fields = [];
        if ($profile) {
            if ($roleLabel === 'Driver') {
                $fields = [
                    'Phone' => $profile->phone,
                    'Gender' => $profile->gender ? ucfirst($profile->gender) : null,
                    'Date of Birth' => $profile->date_of_birth?->format('M d, Y'),
                    'Address' => $profile->address,
                    'License No.' => $profile->license_number,
                    'Experience' => $profile->experience_years ? $profile->experience_years.' yrs' : null,
                    'Joining Date' => $profile->joining_date?->format('M d, Y'),
                ];
            } elseif ($roleLabel === 'Parent') {
                $fields = [
                    'Phone' => $profile->phone,
                    'Alternate Phone' => $profile->alternate_phone,
                    'Address' => $profile->address,
                    'Occupation' => $profile->occupation,
                ];
            } elseif ($roleLabel === 'School Admin') {
                $fields = [
                    'Phone' => $profile->phone,
                    'Designation' => $profile->designation,
                    'Address' => $profile->address,
                ];
            }
        }

        $fields = array_filter($fields, fn ($value) => ! is_null($value) && $value !== '');
    @endphp

    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Profile</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your account settings and preferences.</p>
        </div>

        <div class="max-w-4xl space-y-6">
            <div x-data="{ editOpen: false, passwordOpen: false }">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                        @if ($user->profile_photo)
                            <img
                                src="{{ asset('storage/' . $user->profile_photo) }}"
                                alt="{{ $user->name }}"
                                class="h-20 w-20 rounded-full object-cover ring-4 ring-gray-100 dark:ring-gray-800 sm:h-24 sm:w-24"
                            >
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 text-3xl font-semibold text-gray-500 ring-4 ring-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-800 sm:h-24 sm:w-24">
                                {{ $initial }}
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                                <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-400">
                                    {{ $roleLabel }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                            @if ($user->school)
                                <p class="mt-1 flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-building-library class="h-4 w-4 shrink-0" />
                                    {{ $user->school->name }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if (count($fields) > 0)
                        <div class="mt-6 border-t border-gray-100 pt-6 dark:border-gray-800">
                            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                                @foreach ($fields as $label => $value)
                                    <div>
                                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                            {{ $label }}
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $value }}
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        @click="editOpen = !editOpen"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <x-heroicon-o-pencil class="h-4 w-4 shrink-0" />
                        Edit Profile
                    </button>

                    <button
                        type="button"
                        @click="passwordOpen = !passwordOpen"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <x-heroicon-o-lock-closed class="h-4 w-4 shrink-0" />
                        Change Password
                    </button>
                </div>

                <div x-show="editOpen" x-cloak class="mt-6">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div x-show="passwordOpen" x-cloak class="mt-6">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>