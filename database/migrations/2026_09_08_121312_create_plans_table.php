<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('yearly_price', 10, 2)->default(0);

            $table->unsignedInteger('max_buses')->nullable();
            $table->unsignedInteger('max_students')->nullable();
            $table->unsignedInteger('max_parents')->nullable();
            $table->unsignedInteger('max_drivers')->nullable();
            $table->unsignedInteger('max_routes')->nullable();
            $table->unsignedInteger('max_devices')->nullable();

            $table->json('features')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};