<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bus_driver', function (Blueprint $table) {
            $table->foreignId('bus_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('driver_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->primary(['bus_id', 'driver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bus_driver');
    }
};
