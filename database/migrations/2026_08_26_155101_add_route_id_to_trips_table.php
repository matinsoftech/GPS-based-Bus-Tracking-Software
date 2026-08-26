<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('route_id')
                ->nullable()
                ->after('driver_id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['route_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['route_id']);
            $table->dropIndex(['route_id', 'status']);
            $table->dropColumn('route_id');
        });
    }
};
