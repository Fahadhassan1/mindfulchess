<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TeacherProfile;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole($adminRole);

        // Create teachers
        $teachers = [
            [
                'name' => 'John Smith',
                'email' => 'john@example.com',
                'profile' => [
                    'qualification' => 'FIDE Master',
                    'teaching_type' => 'adult',
                    'stripe_account_id' => 'acct_12345',
                    'bio' => 'Professional chess coach with 15 years of experience.',
                    'experience_years' => 15,
                    'specialties' => ['Opening Strategy', 'Endgame Tactics', 'Tournament Preparation'],
                ]
            ],
            [
                'name' => 'Emma Johnson',
                'email' => 'emma@example.com',
                'profile' => [
                    'qualification' => 'International Master',
                    'teaching_type' => 'kids',
                    'stripe_account_id' => 'acct_67890',
                    'bio' => 'Specializes in teaching chess to children. Makes learning fun and engaging.',
                    'experience_years' => 8,
                    'specialties' => ['Child Education', 'Beginners Introduction', 'Puzzle Solving'],
                ]
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael@example.com',
                'profile' => [
                    'qualification' => 'Grandmaster',
                    'teaching_type' => 'all',
                    'stripe_account_id' => 'acct_abcde',
                    'bio' => 'Grandmaster with experience teaching all levels from beginners to advanced players.',
                    'experience_years' => 20,
                    'specialties' => ['Advanced Strategy', 'Competition Preparation', 'Opening Repertoire Development'],
                ]
            ]
        ];

        foreach ($teachers as $teacherData) {
            $teacher = User::firstOrCreate(
                ['email' => $teacherData['email']],
                [
                    'name' => $teacherData['name'],
                    'email' => $teacherData['email'],
                    'password' => Hash::make('password'),
                ]
            );
            $teacher->assignRole($teacherRole);

            // Create teacher profile
            TeacherProfile::updateOrCreate(
                ['user_id' => $teacher->id],
                $teacherData['profile']
            );
        }

        // Create students
        $students = [
            [
                'name' => 'Alex Wilson',
                'email' => 'alex@example.com',
                'profile' => [
                    'age' => 12,
                    'level' => 'beginner',
                    'parent_name' => 'Sarah Wilson',
                    'parent_email' => 'sarah@example.com',
                    'parent_phone' => '555-123-4567',
                    'school' => 'Lincoln Elementary',
                    'learning_goals' => 'Wants to learn the basics and play in school tournaments.',
                ]
            ],
            [
                'name' => 'Sophia Garcia',
                'email' => 'sophia@example.com',
                'profile' => [
                    'age' => 16,
                    'level' => 'intermediate',
                    'parent_name' => 'Maria Garcia',
                    'parent_email' => 'maria@example.com',
                    'parent_phone' => '555-987-6543',
                    'school' => 'Washington High School',
                    'learning_goals' => 'Preparing for regional competitions and improving tactical skills.',
                ]
            ],
            [
                'name' => 'David Lee',
                'email' => 'david@example.com',
                'profile' => [
                    'age' => 25,
                    'level' => 'advanced',
                    'school' => 'State University',
                    'learning_goals' => 'Focusing on advanced endgame techniques and opening preparation.',
                ]
            ]
        ];

        foreach ($students as $studentData) {
            $student = User::firstOrCreate(
                ['email' => $studentData['email']],
                [
                    'name' => $studentData['name'],
                    'email' => $studentData['email'],
                    'password' => Hash::make('password'),
                ]
            );
            $student->assignRole($studentRole);

            // Create student profile
            StudentProfile::updateOrCreate(
                ['user_id' => $student->id],
                $studentData['profile']
            );
        }

        $this->command->info('Demo users created successfully');
    }
}
