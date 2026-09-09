<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('students', 'route_id')) {
            DB::table('route_student')->insertUsing(
                ['route_id', 'student_id', 'created_at', 'updated_at'],
                DB::table('students')
                    ->select('route_id', 'id')
                    ->selectRaw('? as created_at, ? as updated_at', [now(), now()])
                    ->whereNotNull('route_id')
            );
        }

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'route_id')) {
                $table->dropConstrainedForeignId('route_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'route_id')) {
                $table->foreignId('route_id')
                    ->nullable()
                    ->after('parent_id')
                    ->constrained()
                    ->nullOnDelete();
            }
        });
    }
};
