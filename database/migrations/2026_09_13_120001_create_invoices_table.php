<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_number')->unique();

            $table->foreignId('school_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('subscription_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('plan_id')
                ->constrained()
                ->restrictOnDelete();

            $table->enum('billing_cycle', [
                'monthly',
                'yearly',
            ])->default('monthly');

            $table->decimal('amount', 10, 2);

            $table->string('currency')->default('NPR');

            $table->timestamp('billing_period_start')->nullable();
            $table->timestamp('billing_period_end')->nullable();

            $table->timestamp('issued_at');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->enum('status', [
                'unpaid',
                'paid',
                'void',
                'overdue',
            ])->default('unpaid');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status'], 'invoices_school_status_index');
            $table->index(['subscription_id', 'billing_period_start', 'billing_period_end'], 'invoices_sub_period_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
