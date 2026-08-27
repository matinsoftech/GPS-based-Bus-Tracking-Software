<?php

namespace Database\Seeders;

use App\Models\Route;
use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Assign seeded students to their school's routes.
     */
    public function run(): void
    {
        $schools = School::orderBy('id')->get();

        if ($schools->isEmpty()) {
            $this->command->error('No school found. Run SchoolSeeder before StudentSeeder.');

            return;
        }

        DB::transaction(function () use ($schools) {
            foreach ($schools as $school) {
                $routes = Route::where('school_id', $school->id)
                    ->orderBy('id')
                    ->get();

                if ($routes->isEmpty()) {
                    continue;
                }

                $students = Student::where('school_id', $school->id)
                    ->orderBy('id')
                    ->get();

                foreach ($students as $index => $student) {
                    $student->update([
                        'route_id' => $routes->get($index % $routes->count())->id,
                    ]);
                }
            }
        });

        $this->command->info('Students assigned to routes successfully.');
    }
}
