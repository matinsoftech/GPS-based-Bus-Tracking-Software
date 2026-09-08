<x-app-layout page="plans">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Plans</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage subscription plans and their limits.
                </p>
            </div>
            <a href="{{ route('plans.create') }}"
                class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                Create Plan
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('plans.index') }}" method="GET" class="mb-4">
            <div class="flex flex-col gap-2 sm:flex-row">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search plans..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 sm:flex-none">
                        Search
                    </button>
                    <a href="{{ route('plans.index') }}"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 sm:flex-none">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        {{-- Desktop / tablet: table view --}}
        <div class="hidden overflow-hidden rounded-2xl border border-gray-200 bg-white md:block dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-800">
                        <tr class="text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3 font-medium">Plan</th>
                            <th class="px-5 py-3 font-medium">Monthly Price</th>
                            <th class="px-5 py-3 font-medium">Yearly Price</th>
                            <th class="px-5 py-3 font-medium">Limits</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($plans as $plan)
                            <tr class="text-gray-700 dark:text-gray-200">
                                <td class="px-5 py-3">
                                    <span class="font-medium">{{ $plan->name }}</span>
                                </td>
                                <td class="px-5 py-3">₹{{ number_format($plan->monthly_price, 2) }}</td>
                                <td class="px-5 py-3">₹{{ number_format($plan->yearly_price, 2) }}</td>
                                <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $plan->max_buses ?? '∞' }} buses,
                                    {{ $plan->max_students ?? '∞' }} students
                                </td>
                                <td class="px-5 py-3">
                                    @if ($plan->is_active)
                                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600 dark:bg-green-500/15 dark:text-green-500">
                                            Active
                                        </span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-500/15 dark:text-gray-400">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('plans.show', $plan) }}"
                                            class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                            View
                                        </a>
                                        <a href="{{ route('plans.edit', $plan) }}"
                                            class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">
                                            Edit
                                        </a>
                                        <button type="button" x-data
                                            @click="$dispatch('open-delete-modal', { id: {{ $plan->id }}, name: '{{ addslashes($plan->name) }}' })"
                                            class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-600">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No plans found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile: stacked card view --}}
        <div class="space-y-3 md:hidden">
            @forelse ($plans as $plan)
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $plan->name }}</span>
                        @if ($plan->is_active)
                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600 dark:bg-green-500/15 dark:text-green-500">
                                Active
                            </span>
                        @else
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-500/15 dark:text-gray-400">
                                Inactive
                            </span>
                        @endif
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-x-3 gap-y-1.5 text-xs">
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">Monthly</dt>
                            <dd class="text-gray-700 dark:text-gray-200">₹{{ number_format($plan->monthly_price, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">Yearly</dt>
                            <dd class="text-gray-700 dark:text-gray-200">₹{{ number_format($plan->yearly_price, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">Buses</dt>
                            <dd class="text-gray-700 dark:text-gray-200">{{ $plan->max_buses ?? '∞' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500">Students</dt>
                            <dd class="text-gray-700 dark:text-gray-200">{{ $plan->max_students ?? '∞' }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex items-center gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                        <a href="{{ route('plans.show', $plan) }}"
                            class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-center text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            View
                        </a>
                        <a href="{{ route('plans.edit', $plan) }}"
                            class="flex-1 rounded-lg bg-brand-500 px-3 py-1.5 text-center text-xs font-medium text-white hover:bg-brand-600">
                            Edit
                        </a>
                        <button type="button" x-data
                            @click="$dispatch('open-delete-modal', { id: {{ $plan->id }}, name: '{{ addslashes($plan->name) }}' })"
                            class="flex-1 rounded-lg bg-red-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-600">
                            Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                    No plans found.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $plans->links() }}
        </div>

        {{-- Delete Confirmation Modal --}}
        <div x-data="{ open: false, planId: null, planName: '' }"
            x-on:open-delete-modal.window="open = true; planId = $event.detail.id; planName = $event.detail.name"
            x-on:keydown.escape.window="open = false"
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="relative z-50"
            style="display: none;">
            <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75" x-on:click="open = false"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="open"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Plan</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Are you sure you want to delete <span class="font-medium text-gray-700 dark:text-gray-200" x-text="planName"></span>? This action cannot be undone.
                        </p>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" x-on:click="open = false"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                Cancel
                            </button>
                            <form :action="'{{ route('plans.index') }}/' + planId" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="rounded-lg bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
