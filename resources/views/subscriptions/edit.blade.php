<x-app-layout page="subscriptions">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Subscription</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Update the subscription for {{ $subscription->school->name }}.
                </p>
            </div>
            <a href="{{ route('subscriptions.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                Back
            </a>
        </div>

        @if (session('error'))
            <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('subscriptions.update', $subscription) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                {{-- School & Plan --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Subscription Details</h2>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="school_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">School</label>
                            <select id="school_id" name="school_id" required
                                class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="">Select a school</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}" {{ old('school_id', $subscription->school_id) == $school->id ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="plan_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Plan</label>
                            <select id="plan_id" name="plan_id" required
                                class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="">Select a plan</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ old('plan_id', $subscription->plan_id) == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} (₹{{ number_format($plan->monthly_price) }}/mo · ₹{{ number_format($plan->yearly_price) }}/yr)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Billing Cycle --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Billing Cycle</h2>
                    <div class="mt-4 flex gap-4">
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 dark:border-gray-700 dark:bg-white/[0.03]">
                            <input type="radio" name="billing_cycle" value="monthly" {{ old('billing_cycle', $subscription->billing_cycle) === 'monthly' ? 'checked' : '' }}
                                class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Monthly</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 dark:border-gray-700 dark:bg-white/[0.03]">
                            <input type="radio" name="billing_cycle" value="yearly" {{ old('billing_cycle', $subscription->billing_cycle) === 'yearly' ? 'checked' : '' }}
                                class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Yearly</span>
                        </label>
                    </div>
                </div>

                {{-- Status --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Status</h2>
                    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-5">
                        @foreach (['trialing', 'active', 'past_due', 'cancelled', 'expired'] as $status)
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-white/[0.03]">
                                <input type="radio" name="status" value="{{ $status }}" {{ old('status', $subscription->status) === $status ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500">
                                <span class="text-sm font-medium capitalize text-gray-700 dark:text-gray-300">{{ str_replace('_', ' ', $status) }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                        Current amount: ₹{{ number_format($subscription->amount, 2) }} (recalculated when plan/cycle changes)
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                        Update Subscription
                    </button>
                    <a href="{{ route('subscriptions.index') }}"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
