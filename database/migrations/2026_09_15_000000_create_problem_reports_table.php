<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('problem_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('reporter_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('trip_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('bus_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->enum('category', [
                'breakdown',
                'accident',
                'routing',
                'safety',
                'driver',
                'other',
            ])->default('other');

            $table->enum('severity', [
                'low',
                'medium',
                'high',
            ])->default('medium');

            $table->enum('status', [
                'open',
                'in_progress',
                'resolved',
            ])->default('open');

            $table->text('description');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('problem_reports');
    }
};
