<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'trialing',
                'active',
                'past_due',
                'cancelled',
                'expired',
            ])->default('trialing')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('status', [
                'trialing',
                'active',
                'past_due',
                'cancelled',
                'expired',
            ])->default('trialing')->change();
        });
    }
};
