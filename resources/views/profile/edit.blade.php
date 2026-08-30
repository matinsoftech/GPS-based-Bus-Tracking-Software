<x-app-layout page="profile">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Profile</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your account settings and preferences.</p>
        </div>

        <div class="max-w-4xl space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                @include('profile.partials.update-password-form')
            </div>

            {{-- <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                @include('profile.partials.delete-user-form')
            </div> --}}
        </div>
    </div>
</x-app-layout>
