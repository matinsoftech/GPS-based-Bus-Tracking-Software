<x-app-layout page="subscription-inactive">
    <div class="mx-auto flex max-w-xl flex-col items-center px-4 py-16 text-center md:py-24">
        <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-500">
            <x-heroicon-o-exclamation-triangle class="h-8 w-8" />
        </div>

        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Service Paused
        </h1>

        <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">
            The school's subscription is not active, so bus tracking features are temporarily suspended.
            Please contact your school administrator to renew the subscription.
        </p>

        @php
            $isSchoolAdmin = auth()->user()?->hasAnyRole(['School Admin', 'Principal']) ?? false;
        @endphp

        @if ($isSchoolAdmin)
            <div class="mt-8">
                <a href="{{ route('principal.subscription') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    <x-heroicon-o-receipt-percent class="h-4 w-4" />
                    View Subscription
                </a>
            </div>
        @endif
    </div>
</x-app-layout>