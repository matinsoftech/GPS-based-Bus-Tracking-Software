<x-app-layout page="invoices">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Invoices</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Review and manage invoices generated for school subscriptions.
                </p>
            </div>
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

        <form action="{{ route('invoices.index') }}" method="GET" class="mb-4 space-y-3">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <input type="text" name="school" value="{{ $filters['school'] ?? '' }}" placeholder="Search by school..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                <select name="status"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">All statuses</option>
                    @foreach (['unpaid', 'paid', 'void', 'overdue'] as $status)
                        <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
                <select name="billing_cycle"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">All cycles</option>
                    @foreach (['monthly', 'yearly'] as $cycle)
                        <option value="{{ $cycle }}" {{ ($filters['billing_cycle'] ?? '') === $cycle ? 'selected' : '' }}>
                            {{ ucfirst($cycle) }}
                        </option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    Filter
                </button>
                <a href="{{ route('invoices.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    Reset
                </a>
            </div>
        </form>

        @php
            $statusColors = [
                'unpaid' => 'bg-yellow-50 text-yellow-600 dark:bg-yellow-500/15 dark:text-yellow-500',
                'paid' => 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500',
                'void' => 'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
                'overdue' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-500',
            ];
        @endphp

        {{-- Desktop / tablet: table view --}}
        <div class="hidden overflow-hidden rounded-2xl border border-gray-200 bg-white md:block dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-800">
                        <tr class="text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3 font-medium">Invoice</th>
                            <th class="px-5 py-3 font-medium">School</th>
                            <th class="px-5 py-3 font-medium">Plan</th>
                            <th class="px-5 py-3 font-medium">Cycle</th>
                            <th class="px-5 py-3 font-medium">Amount</th>
                            <th class="px-5 py-3 font-medium">Issued</th>
                            <th class="px-5 py-3 font-medium">Due</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($invoices as $invoice)
                            <tr class="text-gray-700 dark:text-gray-200">
                                <td class="px-5 py-3 font-medium">{{ $invoice->invoice_number }}</td>
                                <td class="px-5 py-3">
                                    <span class="font-medium">{{ $invoice->school->name }}</span>
                                </td>
                                <td class="px-5 py-3">{{ $invoice->plan->name }}</td>
                                <td class="px-5 py-3 capitalize">{{ $invoice->billing_cycle }}</td>
                                <td class="px-5 py-3">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                                <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $invoice->issued_at->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $invoice->due_at ? $invoice->due_at->format('M d, Y') : '—' }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColors[$invoice->statusLabel()] ?? $statusColors['void'] }}">
                                        {{ ucfirst($invoice->statusLabel()) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('invoices.show', $invoice) }}"
                                            class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No invoices found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile: stacked card view --}}
        <div class="space-y-3 md:hidden">
            @forelse ($invoices as $invoice)
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColors[$invoice->statusLabel()] ?? $statusColors['void'] }}">
                            {{ ucfirst($invoice->statusLabel()) }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $invoice->school->name }} · {{ $invoice->plan->name }} · {{ ucfirst($invoice->billing_cycle) }}
                    </p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-200">
                        {{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        Issued: {{ $invoice->issued_at->format('M d, Y') }} · Due: {{ $invoice->due_at ? $invoice->due_at->format('M d, Y') : '—' }}
                    </p>
                    <div class="mt-4 flex items-center gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                        <a href="{{ route('invoices.show', $invoice) }}"
                            class="flex-1 rounded-lg bg-brand-500 px-3 py-1.5 text-center text-xs font-medium text-white hover:bg-brand-600">
                            View
                        </a>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                    No invoices found.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $invoices->links() }}
        </div>
    </div>
</x-app-layout>