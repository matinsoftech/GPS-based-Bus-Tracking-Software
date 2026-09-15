<x-app-layout page="invoices">
    <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Issued {{ $invoice->issued_at->format('M d, Y h:i A') }} · {{ $invoice->school->name }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('invoices.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    Back
                </a>
                <a href="{{ route('invoices.print', $invoice) }}" target="_blank"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    Print
                </a>
                @if (auth()->user()->hasRole('Super Admin') &&
                        ($invoice->statusLabel() === 'unpaid' || $invoice->statusLabel() === 'overdue'))
                    <form action="{{ route('invoices.mark-paid', $invoice) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                            Mark as Paid
                        </button>
                    </form>
                    <form action="{{ route('invoices.void', $invoice) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="rounded-lg bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">
                            Void
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div
                class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                {{ session('error') }}
            </div>
        @endif

        @php
            $statusColors = [
                'unpaid' => 'bg-yellow-50 text-yellow-600 dark:bg-yellow-500/15 dark:text-yellow-500',
                'paid' => 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500',
                'void' => 'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
                'overdue' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-500',
            ];
        @endphp

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-5 dark:border-gray-800">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Billed To</p>
                    <h2 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $invoice->school->name }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $invoice->school->code }}@if ($invoice->school->address)
                            · {{ $invoice->school->address }}
                        @endif
                    </p>
                    @if ($invoice->school->phone || $invoice->school->email)
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                            {{ $invoice->school->phone }}@if ($invoice->school->email)
                                · {{ $invoice->school->email }}
                            @endif
                        </p>
                    @endif
                </div>
                <span
                    class="rounded-full px-3 py-1 text-xs font-medium {{ $statusColors[$invoice->statusLabel()] ?? $statusColors['void'] }}">
                    {{ ucfirst($invoice->statusLabel()) }}
                </span>
            </div>

            <dl class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">Plan</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $invoice->plan->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">Billing Cycle</dt>
                    <dd class="mt-1 text-sm font-medium capitalize text-gray-900 dark:text-white">
                        {{ $invoice->billing_cycle }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">Amount</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        {{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">Billing Period</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                        @if ($invoice->billing_period_start)
                            {{ $invoice->billing_period_start->format('M d, Y') }}
                            @if ($invoice->billing_period_end)
                                – {{ $invoice->billing_period_end->format('M d, Y') }}
                            @endif
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">Due Date</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                        {{ $invoice->due_at ? $invoice->due_at->format('M d, Y') : '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">Paid On</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                        {{ $invoice->paid_at ? $invoice->paid_at->format('M d, Y h:i A') : '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">Issued Date</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                        {{ $invoice->issued_at->format('M d, Y h:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">Subscription</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">#{{ $invoice->subscription->id }}</dd>
                </div>
            </dl>

            <div class="mt-6 rounded-xl bg-gray-50 p-4 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Total Due</span>
                    <span class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
