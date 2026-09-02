<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_stop_arrivals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('route_stop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bus_id')->constrained()->cascadeOnDelete();

            $table->enum('status', ['arrived', 'departed'])->default('arrived');

            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('departed_at')->nullable();

            $table->time('schedule_at')->nullable();

            $table->timestamps();

            $table->unique(['trip_id', 'route_stop_id']);
            $table->index(['trip_id', 'route_stop_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_stop_arrivals');
    }
};
