<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\School;
use App\Models\Subscription;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Plan $plan;

    private InvoiceService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(InvoiceService::class);

        $this->school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-INV-SERVICE',
            'email' => 'inv-service@brightfuture.com',
            'phone' => '9800000400',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->plan = $this->makePlan([
            'name' => 'Standard Plan',
            'monthly_price' => 1999,
            'yearly_price' => 19999,
            'is_active' => true,
        ]);
    }

    /** 1. Invoice for active monthly subscription */
    public function test_generate_for_active_monthly_subscription_creates_unpaid_invoice(): void
    {
        $subscription = $this->subscribe('active');

        $invoice = $this->service->generateForSubscription($subscription);

        $this->assertNotNull($invoice);
        $this->assertSame('unpaid', $invoice->status);
        $this->assertSame('INV-000001', $invoice->invoice_number);
        $this->assertSame('monthly', $invoice->billing_cycle);
        $this->assertSame('NPR', $invoice->currency);
        $this->assertSame(1999.0, (float) $invoice->amount);
        $this->assertSame($this->school->id, $invoice->school_id);
        $this->assertSame($subscription->id, $invoice->subscription_id);
        $this->assertTrue($subscription->starts_at->eq($invoice->billing_period_start));
        $this->assertTrue($subscription->ends_at->eq($invoice->billing_period_end));
        $this->assertTrue($invoice->issued_at->isToday());
        $this->assertTrue($invoice->due_at->eq($invoice->issued_at->copy()->addDays(7)));
        $this->assertNull($invoice->paid_at);
    }

    public function test_generate_for_active_yearly_subscription_uses_yearly_price(): void
    {
        $subscription = $this->subscribe('active', ['billing_cycle' => 'yearly']);

        $invoice = $this->service->generateForSubscription($subscription);

        $this->assertNotNull($invoice);
        $this->assertSame('yearly', $invoice->billing_cycle);
        $this->assertSame(19999.0, (float) $invoice->amount);
    }

    public function test_invoice_number_is_sequential(): void
    {
        $first = $this->service->generateForSubscription($this->subscribe('active'));

        $second = $this->service->generateForSubscription(
            $this->subscribe('active', [
                'billing_cycle' => 'yearly',
                'starts_at' => now()->subMonths(2),
                'ends_at' => now()->addMonths(10),
            ])
        );

        $this->assertSame('INV-000001', $first->invoice_number);
        $this->assertSame('INV-000002', $second->invoice_number);
    }

    /** 2. No invoice while trialing */
    public function test_generate_for_trialing_subscription_returns_null(): void
    {
        $subscription = $this->subscribe('trialing');

        $this->assertNull($this->service->generateForSubscription($subscription));
        $this->assertSame(0, Invoice::count());
    }

    /** 3. Expiration is not invoicing */
    public function test_generate_for_expired_and_cancelled_subscriptions_returns_null(): void
    {
        $this->assertNull($this->service->generateForSubscription($this->subscribe('expired')));
        $this->assertNull($this->service->generateForSubscription($this->subscribe('cancelled')));

        $this->assertSame(0, Invoice::count());
    }

    public function test_generate_for_active_subscription_without_active_plan_returns_null(): void
    {
        $this->plan->update(['is_active' => false]);

        $this->assertNull($this->service->generateForSubscription($this->subscribe('active')));
        $this->assertSame(0, Invoice::count());
    }

    /** 4. Duplicate protection per billing period */
    public function test_generate_twice_for_same_period_does_not_duplicate(): void
    {
        $subscription = $this->subscribe('active');

        $first = $this->service->generateForSubscription($subscription);
        $second = $this->service->generateForSubscription($subscription->fresh());

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Invoice::count());
    }

    /** 5. New billing period produces a new invoice */
    public function test_generate_for_new_billing_period_creates_next_invoice(): void
    {
        $subscription = $this->subscribe('active');
        $this->service->generateForSubscription($subscription);

        $subscription->update([
            'starts_at' => $subscription->ends_at,
            'ends_at' => $subscription->ends_at->copy()->addMonth(),
        ]);

        $next = $this->service->generateForSubscription($subscription->fresh());

        $this->assertNotNull($next);
        $this->assertSame('INV-000002', $next->invoice_number);
        $this->assertSame(2, Invoice::count());
    }

    /** 6. Mark as paid */
    public function test_mark_as_paid_sets_status_and_paid_at(): void
    {
        $invoice = $this->service->generateForSubscription($this->subscribe('active'));

        $updated = $this->service->markAsPaid($invoice);

        $this->assertSame('paid', $updated->status);
        $this->assertNotNull($updated->paid_at);
        $this->assertTrue($updated->paid_at->isToday());
    }

    public function test_mark_as_paid_is_idempotent(): void
    {
        $invoice = $this->service->generateForSubscription($this->subscribe('active'));
        $paidAt = now();

        $this->service->markAsPaid($invoice);
        $invoice->refresh();

        $paidAtValue = $invoice->paid_at->copy();

        $this->service->markAsPaid($invoice);
        $invoice->refresh();

        $this->assertSame('paid', $invoice->status);
        $this->assertTrue($invoice->paid_at->eq($paidAtValue));
    }

    public function test_mark_as_paid_rejects_voided_invoice(): void
    {
        $invoice = $this->service->generateForSubscription($this->subscribe('active'));
        $this->service->void($invoice);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A voided invoice cannot be marked as paid.');

        $this->service->markAsPaid($invoice);
    }

    /** 7. Void */
    public function test_void_sets_status_void(): void
    {
        $invoice = $this->service->generateForSubscription($this->subscribe('active'));

        $updated = $this->service->void($invoice);

        $this->assertSame('void', $updated->status);
        $this->assertNull($updated->paid_at);
    }

    public function test_void_rejects_paid_invoice(): void
    {
        $invoice = $this->service->generateForSubscription($this->subscribe('active'));
        $this->service->markAsPaid($invoice);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A paid invoice cannot be voided.');

        $this->service->void($invoice);
    }

    public function test_void_rejects_already_voided_invoice(): void
    {
        $invoice = $this->service->generateForSubscription($this->subscribe('active'));
        $this->service->void($invoice);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The invoice is already voided.');

        $this->service->void($invoice);
    }

    /** 8. Overdue is derived read-time */
    public function test_status_label_returns_overdue_for_unpaid_past_due_invoice(): void
    {
        $invoice = $this->service->generateForSubscription($this->subscribe('active'));
        $invoice->update(['due_at' => now()->subDay()]);

        $this->assertSame('overdue', $invoice->statusLabel());

        $this->service->markAsPaid($invoice);

        $this->assertSame('paid', $invoice->statusLabel());
    }

    /** 9. Invalid billing cycle price */
    public function test_get_plan_price_rejects_invalid_cycle(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid billing cycle.');

        $this->service->getPlanPrice($this->plan, 'weekly');
    }

    /**
     * Create a plan with sensible defaults.
     */
    private function makePlan(array $overrides = []): Plan
    {
        return Plan::create(array_merge([
            'name' => 'Plan '.uniqid(),
            'monthly_price' => 100,
            'yearly_price' => 1000,
            'max_buses' => null,
            'max_students' => null,
            'max_parents' => null,
            'max_drivers' => null,
            'max_routes' => null,
            'max_devices' => null,
            'features' => ['live_tracking' => true],
            'is_active' => true,
        ], $overrides));
    }

    /**
     * Create a subscription for the test school.
     */
    private function subscribe(
        string $status,
        array $subscriptionOverrides = []
    ): Subscription {
        return Subscription::create(array_merge([
            'school_id' => $this->school->id,
            'plan_id' => $this->plan->id,
            'billing_cycle' => 'monthly',
            'amount' => $this->plan->monthly_price,
            'status' => $status,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonth(),
            'trial_ends_at' => null,
        ], $subscriptionOverrides));
    }
}
