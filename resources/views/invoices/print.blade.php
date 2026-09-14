<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>{{ $invoice->invoice_number }} - {{ $settings?->platform_name ?? config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css'])

    <style>
        @@media print {
            @page {
                margin: 1.5cm;
            }

            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="no-print mx-auto flex max-w-3xl justify-end px-6 pt-6">
        <button type="button" onclick="window.print()"
            class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
            Print / Save as PDF
        </button>
    </div>

    <div class="mx-auto my-6 max-w-3xl bg-white p-8 shadow-sm sm:p-12">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-200 pb-6">
            <div>
                @if ($settings?->logo)
                    <img src="{{ Storage::url($settings->logo) }}" alt="Logo" class="mb-2 h-12 w-12 rounded-lg object-contain">
                @endif
                <h1 class="text-xl font-semibold text-gray-900">
                    {{ $settings?->platform_name ?? 'School Bus Tracker' }}
                </h1>
                <p class="text-sm text-gray-500">Subscription Invoice</p>
            </div>
            <div class="text-right">
                <p class="text-lg font-semibold text-gray-900">{{ $invoice->invoice_number }}</p>
                <p class="text-sm text-gray-500">Issued: {{ $invoice->issued_at->format('M d, Y') }}</p>
                <p class="text-sm text-gray-500">Due: {{ $invoice->due_at ? $invoice->due_at->format('M d, Y') : '—' }}</p>
                <p class="mt-1 inline-block rounded-full bg-gray-100 px-3 py-0.5 text-xs font-medium uppercase tracking-wide text-gray-600">
                    {{ $invoice->statusLabel() }}
                </p>
            </div>
        </div>

        {{-- Parties --}}
        <div class="mt-6 grid grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Billed To</p>
                <h2 class="mt-1 font-semibold text-gray-900">{{ $invoice->school->name }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $invoice->school->code }}</p>
                @if ($invoice->school->address)
                    <p class="text-sm text-gray-600">{{ $invoice->school->address }}</p>
                @endif
                @if ($invoice->school->phone)
                    <p class="text-sm text-gray-600">{{ $invoice->school->phone }}</p>
                @endif
                @if ($invoice->school->email)
                    <p class="text-sm text-gray-600">{{ $invoice->school->email }}</p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Billing Period</p>
                <p class="mt-1 text-sm text-gray-900">
                    @if ($invoice->billing_period_start)
                        {{ $invoice->billing_period_start->format('M d, Y') }} – {{ $invoice->billing_period_end ? $invoice->billing_period_end->format('M d, Y') : 'Open' }}
                    @else
                        —
                    @endif
                </p>
            </div>
        </div>

        {{-- Line items --}}
        <table class="mt-8 w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-gray-500">
                    <th class="pb-2 font-medium">Description</th>
                    <th class="pb-2 text-right font-medium">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-gray-100">
                    <td class="py-3">
                        <p class="font-medium text-gray-900">{{ $invoice->plan->name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ ucfirst($invoice->billing_cycle) }} subscription renewal
                            @if ($invoice->billing_period_start)
                                · {{ $invoice->billing_period_start->format('M d, Y') }}
                                @if ($invoice->billing_period_end)
                                    – {{ $invoice->billing_period_end->format('M d, Y') }}
                                @endif
                            @endif
                        </p>
                    </td>
                    <td class="py-3 text-right text-gray-900">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="py-4 font-medium text-gray-900">Total</td>
                    <td class="py-4 text-right text-lg font-semibold text-gray-900">
                        {{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <p class="mt-8 border-t border-gray-200 pt-6 text-center text-xs text-gray-400">
            This invoice was generated automatically for the {{ $invoice->school->name }} subscription (#{{ $invoice->subscription->id }}).
        </p>
    </div>
</body>

</html>