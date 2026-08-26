<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('attendances', 'route_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->foreignId('route_id')
                    ->nullable()
                    ->after('student_id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        // Migrate existing data: derive route_id from the attendance's bus via bus_route pivot
        DB::table('attendances')
            ->whereNotNull('bus_id')
            ->where('route_id', null)
            ->orderBy('id')
            ->each(function ($attendance) {
                $routeId = DB::table('bus_route')
                    ->where('bus_id', $attendance->bus_id)
                    ->value('route_id');

                if ($routeId) {
                    DB::table('attendances')
                        ->where('id', $attendance->id)
                        ->update(['route_id' => $routeId]);
                }
            });

        if (Schema::hasColumn('attendances', 'bus_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropConstrainedForeignId('bus_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('attendances', 'bus_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->foreignId('bus_id')
                    ->nullable()
                    ->after('student_id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        DB::table('attendances')
            ->whereNotNull('route_id')
            ->where('bus_id', null)
            ->orderBy('id')
            ->each(function ($attendance) {
                $busId = DB::table('bus_route')
                    ->where('route_id', $attendance->route_id)
                    ->value('bus_id');

                if ($busId) {
                    DB::table('attendances')
                        ->where('id', $attendance->id)
                        ->update(['bus_id' => $busId]);
                }
            });

        if (Schema::hasColumn('attendances', 'route_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropConstrainedForeignId('route_id');
            });
        }
    }
};
