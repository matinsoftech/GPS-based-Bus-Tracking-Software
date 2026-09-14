<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\InvoiceNotification;
use App\Services\InvoiceService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InvoiceNotificationTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Plan $plan;

    private User $principal;

    private InvoiceService $invoices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->invoices = app(InvoiceService::class);

        $this->school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-INV-NOTIFY',
            'email' => 'notify@brightfuture.com',
            'phone' => '9800000700',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->principal = User::factory()->create(['school_id' => $this->school->id]);
        $this->principal->assignRole('School Admin');

        SchoolAdmin::create([
            'user_id' => $this->principal->id,
            'school_id' => $this->school->id,
            'name' => 'Principal A',
            'phone' => '9800000701',
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

    private function subscribe(array $overrides = []): Subscription
    {
        return Subscription::create(array_merge([
            'school_id' => $this->school->id,
            'plan_id' => $this->plan->id,
            'billing_cycle' => 'monthly',
            'amount' => $this->plan->monthly_price,
            'status' => 'active',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonth(),
            'trial_ends_at' => null,
        ], $overrides));
    }

    public function test_invoice_generation_notifies_all_school_admins(): void
    {
        Notification::fake();

        $secondAdmin = User::factory()->create(['school_id' => $this->school->id]);
        $secondAdmin->assignRole('School Admin');

        SchoolAdmin::create([
            'user_id' => $secondAdmin->id,
            'school_id' => $this->school->id,
            'name' => 'Admin B',
            'phone' => '9800000702',
            'designation' => 'Admin',
            'address' => 'Kathmandu',
        ]);

        $this->invoices->generateForSubscription($this->subscribe());

        Notification::assertSentTo($this->principal, InvoiceNotification::class);
        Notification::assertSentTo($secondAdmin, InvoiceNotification::class);
    }

    public function test_scheduled_command_notifies_principal(): void
    {
        Notification::fake();

        $subscription = $this->subscribe(['ends_at' => now()->addDays(2)->subSeconds(5)]);

        $this->artisan('invoices:issue-upcoming')->assertSuccessful();

        Notification::assertSentTo($this->principal, InvoiceNotification::class, function (InvoiceNotification $notification) use ($subscription) {
            return $notification->invoice->subscription_id === $subscription->id
                && $notification->invoice->status === 'unpaid';
        });
    }

    public function test_existing_invoice_does_not_notify_twice(): void
    {
        Notification::fake();

        $subscription = $this->subscribe();

        $this->invoices->generateForSubscription($subscription);
        $this->invoices->generateForSubscription($subscription);

        Notification::assertSentTo($this->principal, InvoiceNotification::class, 1);
    }

    public function test_notification_payload_contains_invoice_details(): void
    {
        $subscription = $this->subscribe();

        $this->invoices->generateForSubscription($subscription);

        $notification = $this->principal->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame('invoice_issued', $notification->data['type']);
        $this->assertSame('New Invoice Issued', $notification->data['title']);
        $this->assertSame($subscription->invoices()->first()->id, $notification->data['invoice_id']);
        $this->assertSame($subscription->invoices()->first()->invoice_number, $notification->data['invoice_number']);
        $this->assertSame($this->school->id, $notification->data['school_id']);
        $this->assertSame((float) $this->plan->monthly_price, (float) $notification->data['amount']);
        $this->assertSame('unpaid', $notification->data['status']);
    }
}
