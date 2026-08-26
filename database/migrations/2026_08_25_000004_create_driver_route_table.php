<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_route', function (Blueprint $table) {
            $table->foreignId('driver_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('route_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->primary(['driver_id', 'route_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_route');
    }
};
