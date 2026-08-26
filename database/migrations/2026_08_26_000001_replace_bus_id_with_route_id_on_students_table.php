<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'route_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->foreignId('route_id')
                    ->nullable()
                    ->after('parent_id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        // Migrate existing data: derive route_id from the student's bus via bus_route pivot
        DB::table('students')
            ->whereNotNull('bus_id')
            ->where('route_id', null)
            ->orderBy('id')
            ->each(function ($student) {
                $routeId = DB::table('bus_route')
                    ->where('bus_id', $student->bus_id)
                    ->value('route_id');

                if ($routeId) {
                    DB::table('students')
                        ->where('id', $student->id)
                        ->update(['route_id' => $routeId]);
                }
            });

        if (Schema::hasColumn('students', 'bus_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropConstrainedForeignId('bus_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('students', 'bus_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->foreignId('bus_id')
                    ->nullable()
                    ->after('parent_id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        DB::table('students')
            ->whereNotNull('route_id')
            ->where('bus_id', null)
            ->orderBy('id')
            ->each(function ($student) {
                $busId = DB::table('bus_route')
                    ->where('route_id', $student->route_id)
                    ->value('bus_id');

                if ($busId) {
                    DB::table('students')
                        ->where('id', $student->id)
                        ->update(['bus_id' => $busId]);
                }
            });

        if (Schema::hasColumn('students', 'route_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropConstrainedForeignId('route_id');
            });
        }
    }
};
