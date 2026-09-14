<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\SchoolAdmin;
use App\Models\Subscription;
use App\Notifications\InvoiceNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    private const CURRENCY = 'NPR';

    private const DUE_DAYS = 7;

    /**
     * Generate an invoice for a subscription's current billing period.
     *
     * Invoices are only created for `active` subscriptions (no invoice for
     * trials) and never twice for the same billing period. Returns the
     * existing invoice when one already covers the period.
     */
    public function generateForSubscription(Subscription $subscription): ?Invoice
    {
        if ($subscription->status !== 'active') {
            return null;
        }

        $plan = $subscription->plan;

        if ($plan === null || ! $plan->is_active) {
            return null;
        }

        $existing = $this->findForPeriod($subscription);

        if ($existing !== null) {
            return $existing;
        }

        $issuedAt = now();

        $invoice = DB::transaction(function () use ($subscription, $plan, $issuedAt) {
            $periodStart = $subscription->starts_at ?? $issuedAt;
            $periodEnd = $subscription->ends_at;

            return Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'school_id' => $subscription->school_id,
                'subscription_id' => $subscription->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $subscription->billing_cycle,
                'amount' => $this->getPlanPrice($plan, $subscription->billing_cycle),
                'currency' => self::CURRENCY,
                'billing_period_start' => $periodStart,
                'billing_period_end' => $periodEnd,
                'issued_at' => $issuedAt,
                'due_at' => $issuedAt->copy()->addDays(self::DUE_DAYS),
                'status' => 'unpaid',
            ]);
        });

        $this->notifySchoolAdmins($invoice);

        return $invoice;
    }

    /**
     * Generate an invoice for the subscription's next billing period ahead of
     * expiry (e.g. two days before the current period ends).
     *
     * Invoices are only created for `active` subscriptions (no invoice for
     * trials) and never twice for the same period. Returns the existing
     * invoice when one already covers the period.
     */
    public function generateForNextPeriod(Subscription $subscription): ?Invoice
    {
        if ($subscription->status !== 'active') {
            return null;
        }

        $plan = $subscription->plan;

        if ($plan === null || ! $plan->is_active) {
            return null;
        }

        $existing = $this->findForNextPeriod($subscription);

        if ($existing !== null) {
            return $existing;
        }

        $issuedAt = now();

        $periodStart = $subscription->ends_at ?? now();
        $periodEnd = $subscription->billing_cycle === 'yearly'
            ? $periodStart->copy()->addYear()
            : $periodStart->copy()->addMonth();

        $invoice = DB::transaction(function () use ($subscription, $plan, $periodStart, $periodEnd, $issuedAt) {
            return Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'school_id' => $subscription->school_id,
                'subscription_id' => $subscription->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $subscription->billing_cycle,
                'amount' => $this->getPlanPrice($plan, $subscription->billing_cycle),
                'currency' => self::CURRENCY,
                'billing_period_start' => $periodStart,
                'billing_period_end' => $periodEnd,
                'issued_at' => $issuedAt,
                'due_at' => $issuedAt->copy()->addDays(self::DUE_DAYS),
                'status' => 'unpaid',
            ]);
        });

        $this->notifySchoolAdmins($invoice);

        return $invoice;
    }

    /**
     * Find the invoice already covering this subscription's billing period,
     * if any. Guards against duplicate invoices per period.
     */
    public function findForPeriod(Subscription $subscription): ?Invoice
    {
        return $this->findForPeriodBetween($subscription, $subscription->starts_at, $subscription->ends_at);
    }

    /**
     * Find the invoice already covering the subscription's next billing period,
     * if any. Guards against duplicate next-period invoices.
     */
    public function findForNextPeriod(Subscription $subscription): ?Invoice
    {
        $periodStart = $subscription->ends_at ?? now();
        $periodEnd = $subscription->billing_cycle === 'yearly'
            ? $periodStart->copy()->addYear()
            : $periodStart->copy()->addMonth();

        return $this->findForPeriodBetween($subscription, $periodStart, $periodEnd);
    }

    /**
     * Find the invoice covering a specific billing period for a subscription.
     */
    private function findForPeriodBetween(
        Subscription $subscription,
        $periodStart,
        $periodEnd
    ): ?Invoice {
        $query = Invoice::query()
            ->where('subscription_id', $subscription->id);

        if ($periodStart !== null && $periodEnd !== null) {
            $query->where('billing_period_start', $periodStart)
                ->where('billing_period_end', $periodEnd);
        } else {
            $query->whereNotNull('billing_period_start')
                ->whereNotNull('billing_period_end');
        }

        return $query->first();
    }

    /**
     * Mark an unpaid invoice as paid.
     */
    public function markAsPaid(Invoice $invoice): Invoice
    {
        if ($invoice->status === 'paid') {
            return $invoice;
        }

        if ($invoice->status === 'void') {
            throw ValidationException::withMessages([
                'invoice' => 'A voided invoice cannot be marked as paid.',
            ]);
        }

        DB::transaction(function () use ($invoice) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        });

        return $invoice->refresh();
    }

    /**
     * Void an unpaid invoice.
     */
    public function void(Invoice $invoice): Invoice
    {
        if ($invoice->status === 'paid') {
            throw ValidationException::withMessages([
                'invoice' => 'A paid invoice cannot be voided.',
            ]);
        }

        if ($invoice->status === 'void') {
            throw ValidationException::withMessages([
                'invoice' => 'The invoice is already voided.',
            ]);
        }

        DB::transaction(function () use ($invoice) {
            $invoice->update(['status' => 'void']);
        });

        return $invoice->refresh();
    }

    /**
     * Resolve the agreed price for a billing cycle from the plan.
     */
    public function getPlanPrice(Plan $plan, string $billingCycle): float
    {
        return match ($billingCycle) {
            'monthly' => (float) $plan->monthly_price,
            'yearly' => (float) $plan->yearly_price,
            default => throw ValidationException::withMessages([
                'billing_cycle' => 'Invalid billing cycle.',
            ]),
        };
    }

    /**
     * Next sequential invoice number (INV-000001, INV-000002, ...).
     *
     * The unique invoice_number column is the backstop under concurrent
     * writes; the auto-increment id keeps numbers gap-free after soft deletes.
     */
    public function generateInvoiceNumber(): string
    {
        $next = (int) Invoice::withTrashed()->max('id') + 1;

        return 'INV-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Notify the school's admins (including the principal) that an invoice has
     * been issued for their school.
     */
    private function notifySchoolAdmins(Invoice $invoice): void
    {
        $admins = SchoolAdmin::where('school_id', $invoice->school_id)
            ->with('user')
            ->get();

        foreach ($admins as $admin) {
            if ($admin->user) {
                $admin->user->notify(new InvoiceNotification($invoice));
            }
        }
    }
}
