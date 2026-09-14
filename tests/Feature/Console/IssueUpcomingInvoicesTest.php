<?php

namespace Tests\Feature\Console;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\School;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueUpcomingInvoicesTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name' => 'Invoice Issue Test School',
            'code' => 'SCH-INV-UP',
            'email' => 'invoice-up@example.com',
            'phone' => '9800000202',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->plan = Plan::create([
            'name' => 'Upcoming Invoice Plan',
            'monthly_price' => 1500,
            'yearly_price' => 15000,
            'max_buses' => null,
            'max_students' => null,
            'max_parents' => null,
            'max_drivers' => null,
            'max_routes' => null,
            'max_devices' => null,
            'features' => [
                'live_tracking' => true,
                'parent_app' => true,
                'notifications' => true,
                'attendance' => true,
                'reports' => false,
                'analytics' => false,
            ],
            'is_active' => true,
        ]);
    }

    private function subscribe(string $status, array $overrides = []): Subscription
    {
        return Subscription::create(array_merge([
            'school_id' => $this->school->id,
            'plan_id' => $this->plan->id,
            'billing_cycle' => 'monthly',
            'amount' => 1500,
            'status' => $status,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addDays(2)->subSeconds(5),
            'trial_ends_at' => null,
        ], $overrides));
    }

    public function test_issues_unpaid_next_month_invoice_for_subscription_expiring_within_two_days(): void
    {
        $subscription = $this->subscribe('active');

        $this->artisan('invoices:issue-upcoming')->assertSuccessful();

        $invoice = Invoice::first();

        $this->assertNotNull($invoice);
        $this->assertSame('unpaid', $invoice->status);
        $this->assertSame($subscription->id, $invoice->subscription_id);
        $this->assertSame('INV-000001', $invoice->invoice_number);
        $this->assertSame(1500.0, (float) $invoice->amount);
        $this->assertTrue($subscription->ends_at->eq($invoice->billing_period_start));
        $this->assertTrue($subscription->ends_at->copy()->addMonth()->eq($invoice->billing_period_end));
        $this->assertTrue($invoice->issued_at->isToday());
        $this->assertTrue($invoice->due_at->eq($invoice->issued_at->copy()->addDays(7)));
    }

    public function test_subscription_expiring_later_than_two_days_is_not_invoiced(): void
    {
        $this->subscribe('active', ['ends_at' => now()->addDays(3)]);

        $this->artisan('invoices:issue-upcoming')->assertSuccessful();

        $this->assertSame(0, Invoice::count());
    }

    public function test_non_active_subscriptions_are_not_invoiced(): void
    {
        $this->subscribe('trialing');
        $this->subscribe('expired');
        $this->subscribe('cancelled');
        $this->subscribe('pending');

        $this->artisan('invoices:issue-upcoming')->assertSuccessful();

        $this->assertSame(0, Invoice::count());
    }

    public function test_active_subscription_with_inactive_plan_is_not_invoiced(): void
    {
        $this->plan->update(['is_active' => false]);
        $this->subscribe('active');

        $this->artisan('invoices:issue-upcoming')->assertSuccessful();

        $this->assertSame(0, Invoice::count());
    }

    public function test_command_does_not_duplicate_invoices_across_runs(): void
    {
        $this->subscribe('active');

        $this->artisan('invoices:issue-upcoming')->assertSuccessful();
        $this->artisan('invoices:issue-upcoming')->assertSuccessful();

        $this->assertSame(1, Invoice::count());
    }

    public function test_yearly_subscription_is_invoiced_for_next_year(): void
    {
        $subscription = $this->subscribe('active', ['billing_cycle' => 'yearly']);

        $this->artisan('invoices:issue-upcoming')->assertSuccessful();

        $invoice = Invoice::first();

        $this->assertNotNull($invoice);
        $this->assertSame('yearly', $invoice->billing_cycle);
        $this->assertSame(15000.0, (float) $invoice->amount);
        $this->assertTrue($subscription->ends_at->copy()->addYear()->eq($invoice->billing_period_end));
    }
}
