<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Subscription;
use App\Models\User;
use App\Services\InvoiceService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $schoolAdmin;

    private School $school;

    private Plan $plan;

    private InvoiceService $invoices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->schoolAdmin = User::factory()->create();
        $this->schoolAdmin->assignRole('School Admin');

        $this->invoices = app(InvoiceService::class);

        $this->school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-INV-CTRL',
            'email' => 'inv-ctrl@brightfuture.com',
            'phone' => '9800000500',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        // Link School Admin to the school for SchoolContextService resolution
        SchoolAdmin::create([
            'user_id' => $this->schoolAdmin->id,
            'school_id' => $this->school->id,
            'name' => 'Test School Admin',
            'phone' => '9800000501',
            'designation' => 'Principal',
            'address' => 'Kathmandu',
        ]);

        $this->plan = Plan::create([
            'name' => 'Standard Plan',
            'monthly_price' => 1999,
            'yearly_price' => 19999,
            'max_buses' => 20,
            'max_students' => 500,
            'max_parents' => 500,
            'max_drivers' => 25,
            'max_routes' => 20,
            'max_devices' => 20,
            'features' => ['live_tracking' => true],
            'is_active' => true,
        ]);
    }

    /** 1. Super Admin can view the invoice list */
    public function test_super_admin_can_view_invoices_index(): void
    {
        $invoice = $this->makeInvoice();

        $response = $this->actingAs($this->admin)->get(route('invoices.index'));

        $response->assertOk();
        $response->assertSee($invoice->invoice_number);
        $response->assertSee($this->school->name);
    }

    /** 2. Filters work on the invoice list */
    public function test_invoices_index_filters_by_status(): void
    {
        $paid = $this->makeInvoice('PAID');
        $unpaid = $this->makeInvoice();

        $response = $this->actingAs($this->admin)->get(route('invoices.index', ['status' => 'paid']));

        $response->assertOk();
        $response->assertSee($paid->invoice_number);
        $response->assertDontSee($unpaid->invoice_number);
    }

    public function test_invoices_index_filters_by_billing_cycle(): void
    {
        $monthly = $this->makeInvoice();
        $yearly = $this->makeInvoice('YEARLY');

        $response = $this->actingAs($this->admin)->get(route('invoices.index', ['billing_cycle' => 'yearly']));

        $response->assertOk();
        $response->assertSee($yearly->invoice_number);
        $response->assertDontSee($monthly->invoice_number);
    }

    public function test_invoices_index_filters_by_school_name(): void
    {
        $otherSchool = School::create([
            'name' => 'Himalaya Public School',
            'code' => 'SCH-INV-CTRL-2',
            'email' => 'himalaya@brightfuture.com',
            'phone' => '9800000600',
            'address' => 'Pokhara',
            'status' => 'active',
        ]);

        $invoice = $this->makeInvoice();

        $otherPlan = Plan::create([
            'name' => 'Other Plan',
            'monthly_price' => 100,
            'yearly_price' => 1000,
            'features' => ['live_tracking' => true],
            'is_active' => true,
        ]);

        $otherSubscription = Subscription::create([
            'school_id' => $otherSchool->id,
            'plan_id' => $otherPlan->id,
            'billing_cycle' => 'monthly',
            'amount' => 100,
            'status' => 'active',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonth(),
        ]);

        $otherInvoice = $this->invoices->generateForSubscription($otherSubscription);

        $response = $this->actingAs($this->admin)->get(route('invoices.index', ['school' => 'Himalaya']));

        $response->assertOk();
        $response->assertSee($otherInvoice->invoice_number);
        $response->assertDontSee($invoice->invoice_number);
    }

    public function test_invoices_index_filters_overdue(): void
    {
        $overdue = $this->makeInvoice();
        $overdue->update(['due_at' => now()->subDay()]);

        $current = $this->makeInvoice('YEARLY');

        $response = $this->actingAs($this->admin)->get(route('invoices.index', ['status' => 'overdue']));

        $response->assertOk();
        $response->assertSee($overdue->invoice_number);
        $response->assertDontSee($current->invoice_number);
    }

    public function test_invoices_index_filters_by_date_range(): void
    {
        $today = $this->makeInvoice();

        $yesterday = $this->makeInvoice('YEARLY');
        $yesterday->update(['issued_at' => now()->subDay()]);

        $response = $this->actingAs($this->admin)->get(route('invoices.index', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSee($today->invoice_number);
        $response->assertDontSee($yesterday->invoice_number);
    }

    /** 3. School Admin can view but not mutate */
    public function test_school_admin_can_view_but_not_mutate_invoices(): void
    {
        $invoice = $this->makeInvoice();

        // View access - should work now
        $this->actingAs($this->schoolAdmin)
            ->get(route('invoices.index'))
            ->assertOk();

        $this->actingAs($this->schoolAdmin)
            ->get(route('invoices.show', $invoice))
            ->assertOk();

        $this->actingAs($this->schoolAdmin)
            ->get(route('invoices.print', $invoice))
            ->assertOk();

        // Mutating actions - still forbidden
        $this->actingAs($this->schoolAdmin)
            ->post(route('invoices.store', $invoice->subscription))
            ->assertForbidden();

        $this->actingAs($this->schoolAdmin)
            ->post(route('invoices.mark-paid', $invoice))
            ->assertForbidden();

        $this->actingAs($this->schoolAdmin)
            ->post(route('invoices.void', $invoice))
            ->assertForbidden();
    }

    /** 4. Active non-trial creation generates an invoice */
    public function test_active_subscription_creation_generates_invoice(): void
    {
        $this->actingAs($this->admin)->post(route('subscriptions.store'), [
            'school_id' => $this->school->id,
            'plan_id' => $this->plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ])->assertRedirect(route('subscriptions.index'));

        $subscription = Subscription::where('school_id', $this->school->id)->firstOrFail();

        $this->assertSame('active', $subscription->status);
        $this->assertSame(1, $subscription->invoices()->count());
        $this->assertSame('unpaid', $subscription->invoices()->first()->status);
        $this->assertSame(1999, (int) $subscription->invoices()->first()->amount);
        $this->assertSame('NPR', $subscription->invoices()->first()->currency);
    }

    /** 5. Trial creation has no invoice; becoming active generates one */
    public function test_trial_to_active_transition_generates_invoice(): void
    {
        $this->actingAs($this->admin)->post(route('subscriptions.store'), [
            'school_id' => $this->school->id,
            'plan_id' => $this->plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'trialing',
        ]);

        $subscription = Subscription::where('school_id', $this->school->id)->firstOrFail();

        $this->assertSame(0, Invoice::count());

        $this->actingAs($this->admin)->put(route('subscriptions.update', $subscription), [
            'school_id' => $this->school->id,
            'plan_id' => $this->plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ])->assertRedirect(route('subscriptions.index'));

        $subscription->refresh();

        $this->assertSame('active', $subscription->status);
        $this->assertSame(1, $subscription->invoices()->count());
    }

    /** 6. Subscription history is preserved with new invoices */
    public function test_new_subscription_after_cancel_creates_new_invoice_and_preserves_history(): void
    {
        $this->actingAs($this->admin)->post(route('subscriptions.store'), [
            'school_id' => $this->school->id,
            'plan_id' => $this->plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        $first = Subscription::where('school_id', $this->school->id)->firstOrFail();
        $first->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        $this->actingAs($this->admin)->post(route('subscriptions.store'), [
            'school_id' => $this->school->id,
            'plan_id' => $this->plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        $this->assertSame(2, Subscription::where('school_id', $this->school->id)->count());
        $this->assertSame(2, Invoice::count());

        $second = Subscription::where('school_id', $this->school->id)
            ->where('id', '!=', $first->id)
            ->firstOrFail();

        $this->assertSame(1, $first->invoices()->count());
        $this->assertSame(1, $second->invoices()->count());
        $this->assertNotSame($first->invoices()->first()->id, $second->invoices()->first()->id);
    }

    /** 7. Manual generate endpoint */
    public function test_manual_generate_creates_invoice_and_is_idempotent(): void
    {
        $subscription = Subscription::create([
            'school_id' => $this->school->id,
            'plan_id' => $this->plan->id,
            'billing_cycle' => 'monthly',
            'amount' => 1999,
            'status' => 'active',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonth(),
            'trial_ends_at' => null,
        ]);

        $this->actingAs($this->admin)->post(route('invoices.store', $subscription))
            ->assertRedirect();

        $this->assertSame(1, Invoice::count());

        $this->actingAs($this->admin)->post(route('invoices.store', $subscription))
            ->assertRedirect();

        $this->assertSame(1, Invoice::count());
    }

    public function test_manual_generate_for_trialing_subscription_errors(): void
    {
        $subscription = Subscription::create([
            'school_id' => $this->school->id,
            'plan_id' => $this->plan->id,
            'billing_cycle' => 'monthly',
            'amount' => 1999,
            'status' => 'trialing',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(13),
            'trial_ends_at' => now()->addDays(13),
        ]);

        $this->actingAs($this->admin)->from(route('subscriptions.edit', $subscription))
            ->post(route('invoices.store', $subscription))
            ->assertRedirect(route('subscriptions.edit', $subscription))
            ->assertSessionHas('error');

        $this->assertSame(0, Invoice::count());
    }

    /** 8. Mark as paid endpoint */
    public function test_mark_as_paid_endpoint_marks_invoice_paid(): void
    {
        $invoice = $this->makeInvoice();

        $this->actingAs($this->admin)->from(route('invoices.show', $invoice))
            ->post(route('invoices.mark-paid', $invoice))
            ->assertRedirect(route('invoices.show', $invoice))
            ->assertSessionHas('success');

        $invoice->refresh();

        $this->assertSame('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);
    }

    /** 9. Void endpoint */
    public function test_void_endpoint_voids_invoice(): void
    {
        $invoice = $this->makeInvoice();

        $this->actingAs($this->admin)->from(route('invoices.show', $invoice))
            ->post(route('invoices.void', $invoice))
            ->assertRedirect(route('invoices.show', $invoice))
            ->assertSessionHas('success');

        $invoice->refresh();

        $this->assertSame('void', $invoice->status);
    }

    public function test_void_endpoint_rejects_paid_invoice(): void
    {
        $invoice = $this->makeInvoice();
        $this->invoices->markAsPaid($invoice);

        $this->actingAs($this->admin)->from(route('invoices.show', $invoice))
            ->post(route('invoices.void', $invoice))
            ->assertRedirect(route('invoices.show', $invoice))
            ->assertSessionHas('error');

        $invoice->refresh();

        $this->assertSame('paid', $invoice->status);
    }

    /** 10. Detail and print views */
    public function test_super_admin_can_view_invoice_detail(): void
    {
        $invoice = $this->makeInvoice();

        $response = $this->actingAs($this->admin)->get(route('invoices.show', $invoice));

        $response->assertOk();
        $response->assertSee($invoice->invoice_number);
        $response->assertSee($this->school->name);
        $response->assertSee($this->plan->name);
        $response->assertSee('NPR');
    }

    public function test_super_admin_can_print_invoice(): void
    {
        $invoice = $this->makeInvoice();

        $this->actingAs($this->admin)->get(route('invoices.print', $invoice))
            ->assertOk()
            ->assertSee($invoice->invoice_number);
    }

    /**
     * Create an invoice for the test school on an active subscription.
     */
    private function makeInvoice(string $kind = 'MONTHLY'): Invoice
    {
        $period = match ($kind) {
            'YEARLY' => [
                'billing_cycle' => 'yearly',
                'start' => now()->subMonth(),
                'end' => now()->addMonths(11),
            ],
            'PAID' => [
                'billing_cycle' => 'monthly',
                'start' => now()->subMonths(2),
                'end' => now()->addMonths(10),
            ],
            default => [
                'billing_cycle' => 'monthly',
                'start' => now()->subMonth(),
                'end' => now()->addMonth(),
            ],
        };

        $subscription = Subscription::create([
            'school_id' => $this->school->id,
            'plan_id' => $this->plan->id,
            'billing_cycle' => $period['billing_cycle'],
            'amount' => $period['billing_cycle'] === 'yearly'
                ? $this->plan->yearly_price
                : $this->plan->monthly_price,
            'status' => 'active',
            'starts_at' => $period['start'],
            'ends_at' => $period['end'],
            'trial_ends_at' => null,
        ]);

        $invoice = $this->invoices->generateForSubscription($subscription);

        if ($kind === 'YEARLY' || $kind === 'PAID') {
            $this->invoices->markAsPaid($invoice);
        }

        return $invoice->refresh();
    }
}
