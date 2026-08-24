<?php

namespace Database\Seeders;

use App\Models\ParentProfile;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SchoolSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Sample data: schools with their principal, parents and students.
     */
    private const SCHOOLS = [
        [
            'name' => 'Green Valley High School',
            'code' => 'GVHS',
            'email' => 'info@greenvalley.edu',
            'phone' => '+1-555-0100',
            'address' => '42 Education Lane, Springfield',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'principal' => [
                'name' => 'Dr. Jane Principal',
                'email' => 'principal.gvhs@greenvalley.edu',
            ],
            'school_admins' => [
                [
                    'name' => 'Alice Johnson',
                    'email' => 'admin.gvhs1@greenvalley.edu',
                    'phone' => '+1-555-0110',
                    'designation' => 'Vice Principal',
                    'address' => '42 Education Lane, Springfield',
                ],
                [
                    'name' => 'Mark Davis',
                    'email' => 'admin.gvhs2@greenvalley.edu',
                    'phone' => '+1-555-0111',
                    'designation' => 'Office Manager',
                    'address' => '42 Education Lane, Springfield',
                ],
            ],
            'parents' => [
                [
                    'user' => ['name' => 'Michael Johnson', 'email' => 'michael.johnson@example.com'],
                    'phone' => '+1-555-0101',
                    'alternate_phone' => '+1-555-0102',
                    'address' => '10 Oak Street, Springfield',
                    'occupation' => 'Engineer',
                    'students' => [
                        [
                            'admission_no' => 'GVHS-2024-001',
                            'first_name' => 'Emma',
                            'last_name' => 'Johnson',
                            'date_of_birth' => '2012-03-15',
                            'gender' => 'Female',
                            'grade' => '7',
                            'section' => 'A',
                            'roll_no' => '01',
                            'pickup_location' => 'Maple Avenue Junction',
                            'drop_location' => 'Green Valley High School',
                        ],
                        [
                            'admission_no' => 'GVHS-2024-002',
                            'first_name' => 'Liam',
                            'last_name' => 'Johnson',
                            'date_of_birth' => '2014-07-22',
                            'gender' => 'Male',
                            'grade' => '5',
                            'section' => 'B',
                            'roll_no' => '02',
                            'pickup_location' => 'Maple Avenue Junction',
                            'drop_location' => 'Green Valley High School',
                        ],
                    ],
                ],
                [
                    'user' => ['name' => 'Robert Smith', 'email' => 'robert.smith@example.com'],
                    'phone' => '+1-555-0103',
                    'alternate_phone' => null,
                    'address' => '5 Pine Road, Springfield',
                    'occupation' => 'Teacher',
                    'students' => [
                        [
                            'admission_no' => 'GVHS-2024-003',
                            'first_name' => 'Noah',
                            'last_name' => 'Smith',
                            'date_of_birth' => '2011-01-10',
                            'gender' => 'Male',
                            'grade' => '8',
                            'section' => 'A',
                            'roll_no' => '03',
                            'pickup_location' => 'Pine Road Corner',
                            'drop_location' => 'Green Valley High School',
                        ],
                        [
                            'admission_no' => 'GVHS-2024-006',
                            'first_name' => 'Ava',
                            'last_name' => 'Smith',
                            'date_of_birth' => '2014-05-30',
                            'gender' => 'Female',
                            'grade' => '5',
                            'section' => 'A',
                            'roll_no' => '06',
                            'pickup_location' => 'Pine Road Corner',
                            'drop_location' => 'Green Valley High School',
                        ],
                    ],
                ],
                [
                    'user' => ['name' => 'David Brown', 'email' => 'david.brown@example.com'],
                    'phone' => '+1-555-0104',
                    'alternate_phone' => '+1-555-0105',
                    'address' => '22 Cedar Street, Springfield',
                    'occupation' => 'Doctor',
                    'students' => [
                        [
                            'admission_no' => 'GVHS-2024-004',
                            'first_name' => 'Olivia',
                            'last_name' => 'Brown',
                            'date_of_birth' => '2013-09-05',
                            'gender' => 'Female',
                            'grade' => '6',
                            'section' => 'B',
                            'roll_no' => '04',
                            'pickup_location' => 'Cedar Street Stop',
                            'drop_location' => 'Green Valley High School',
                        ],
                        [
                            'admission_no' => 'GVHS-2024-005',
                            'first_name' => 'James',
                            'last_name' => 'Brown',
                            'date_of_birth' => '2010-12-18',
                            'gender' => 'Male',
                            'grade' => '9',
                            'section' => 'A',
                            'roll_no' => '05',
                            'pickup_location' => 'Cedar Street Stop',
                            'drop_location' => 'Green Valley High School',
                        ],
                    ],
                ],
            ],
        ],
        [
            'name' => 'Sunrise Academy',
            'code' => 'SA',
            'email' => 'info@sunriseacademy.edu',
            'phone' => '+1-555-0200',
            'address' => '100 Bright Way, Riverside',
            'latitude' => 37.7749,
            'longitude' => -122.4194,
            'principal' => [
                'name' => 'Dr. Maria Principal',
                'email' => 'principal.sa@sunriseacademy.edu',
            ],
            'school_admins' => [
                [
                    'name' => 'Emily Turner',
                    'email' => 'admin.sa1@sunriseacademy.edu',
                    'phone' => '+1-555-0210',
                    'designation' => 'Administrator',
                    'address' => '100 Bright Way, Riverside',
                ],
                [
                    'name' => 'John Miller',
                    'email' => 'admin.sa2@sunriseacademy.edu',
                    'phone' => '+1-555-0211',
                    'designation' => 'Office Manager',
                    'address' => '100 Bright Way, Riverside',
                ],
            ],
            'parents' => [
                [
                    'user' => ['name' => 'James Wilson', 'email' => 'james.wilson@example.com'],
                    'phone' => '+1-555-0201',
                    'alternate_phone' => null,
                    'address' => '3 Sunrise Avenue, Riverside',
                    'occupation' => 'Lawyer',
                    'students' => [
                        [
                            'admission_no' => 'SA-2024-001',
                            'first_name' => 'Sophia',
                            'last_name' => 'Wilson',
                            'date_of_birth' => '2013-04-20',
                            'gender' => 'Female',
                            'grade' => '6',
                            'section' => 'A',
                            'roll_no' => '01',
                            'pickup_location' => 'Sunrise Avenue Gate',
                            'drop_location' => 'Sunrise Academy',
                        ],
                        [
                            'admission_no' => 'SA-2024-002',
                            'first_name' => 'Benjamin',
                            'last_name' => 'Wilson',
                            'date_of_birth' => '2015-08-11',
                            'gender' => 'Male',
                            'grade' => '4',
                            'section' => 'B',
                            'roll_no' => '02',
                            'pickup_location' => 'Sunrise Avenue Gate',
                            'drop_location' => 'Sunrise Academy',
                        ],
                    ],
                ],
                [
                    'user' => ['name' => 'Maria Garcia', 'email' => 'maria.garcia@example.com'],
                    'phone' => '+1-555-0202',
                    'alternate_phone' => '+1-555-0203',
                    'address' => '18 Moon Crescent, Riverside',
                    'occupation' => 'Nurse',
                    'students' => [
                        [
                            'admission_no' => 'SA-2024-003',
                            'first_name' => 'Isabella',
                            'last_name' => 'Garcia',
                            'date_of_birth' => '2012-11-02',
                            'gender' => 'Female',
                            'grade' => '7',
                            'section' => 'B',
                            'roll_no' => '03',
                            'pickup_location' => 'Moon Crescent Stop',
                            'drop_location' => 'Sunrise Academy',
                        ],
                        [
                            'admission_no' => 'SA-2024-004',
                            'first_name' => 'Daniel',
                            'last_name' => 'Garcia',
                            'date_of_birth' => '2016-02-14',
                            'gender' => 'Male',
                            'grade' => '3',
                            'section' => 'A',
                            'roll_no' => '04',
                            'pickup_location' => 'Moon Crescent Stop',
                            'drop_location' => 'Sunrise Academy',
                        ],
                    ],
                ],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolAdminRole = Role::where('name', 'School Admin')->first();
        $parentRole = Role::where('name', 'Parent')->first();

        DB::transaction(function () use ($schoolAdminRole, $parentRole) {
            foreach (self::SCHOOLS as $schoolData) {
                $school = $this->createSchool($schoolData);

                $this->createPrincipal($school, $schoolData['principal'], $schoolAdminRole);

                foreach ($schoolData['school_admins'] as $adminData) {
                    $this->createSchoolAdmin($school, $adminData, $schoolAdminRole);
                }

                foreach ($schoolData['parents'] as $parentData) {
                    $parentProfile = $this->createParent($school, $parentData, $parentRole);

                    foreach ($parentData['students'] as $studentData) {
                        $this->createStudent($school, $parentProfile, $studentData);
                    }
                }
            }
        });
    }

    /**
     * Create or update a school.
     */
    private function createSchool(array $data): School
    {
        return School::updateOrCreate(
            ['code' => $data['code']],
            [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'status' => 'active',
            ]
        );
    }

    /**
     * Create the principal user (School Admin role) and link them to the school.
     */
    private function createPrincipal(School $school, array $data, ?Role $role): void
    {
        $principal = User::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make('password'),
                'school_id' => $school->id,
            ]
        );

        if ($role) {
            $principal->syncRoles([$role]);
        }

        $school->update(['principal_name' => $principal->name]);
    }

    /**
     * Create or update a school admin user and their profile.
     */
    private function createSchoolAdmin(School $school, array $data, ?Role $role): void
    {
        $user = User::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make('password'),
                'school_id' => $school->id,
            ]
        );

        if ($role) {
            $user->syncRoles([$role]);
        }

        SchoolAdmin::updateOrCreate(
            ['user_id' => $user->id],
            [
                'school_id' => $school->id,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'designation' => $data['designation'] ?? null,
                'address' => $data['address'] ?? null,
            ]
        );
    }

    /**
     * Create or update a parent user and their profile.
     */
    private function createParent(School $school, array $data, ?Role $role): ParentProfile
    {
        $user = User::updateOrCreate(
            ['email' => $data['user']['email']],
            [
                'name' => $data['user']['name'],
                'password' => Hash::make('password'),
                'school_id' => $school->id,
            ]
        );

        if ($role) {
            $user->syncRoles([$role]);
        }

        return ParentProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'school_id' => $school->id,
                'name' => $data['user']['name'],
                'phone' => $data['phone'],
                'alternate_phone' => $data['alternate_phone'],
                'address' => $data['address'],
                'occupation' => $data['occupation'],
            ]
        );
    }

    /**
     * Create or update a student linked to a parent.
     */
    private function createStudent(School $school, ParentProfile $parent, array $data): void
    {
        Student::updateOrCreate(
            ['admission_no' => $data['admission_no']],
            [
                'school_id' => $school->id,
                'parent_id' => $parent->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'grade' => $data['grade'],
                'section' => $data['section'],
                'roll_no' => $data['roll_no'],
                'pickup_location' => $data['pickup_location'],
                'drop_location' => $data['drop_location'],
                'is_active' => true,
            ]
        );
    }
}
