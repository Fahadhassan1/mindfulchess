<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $teacherRole = Role::create(['name' => 'teacher']);
        $studentRole = Role::create(['name' => 'student']);
        
        // Create permissions
        $permissions = [
            'view dashboard',
            'manage users',
            'create lessons',
            'edit lessons',
            'view lessons',
            'submit assignments',
            'grade assignments',
        ];
        
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        
        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());
        $teacherRole->givePermissionTo([
            'view dashboard',
            'create lessons',
            'edit lessons',
            'view lessons',
            'grade assignments',
        ]);
        $studentRole->givePermissionTo([
            'view dashboard',
            'view lessons',
            'submit assignments',
        ]);
        
        // Create one user for each role
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@mindfulchess.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        
        $teacher = User::create([
            'name' => 'Teacher User',
            'email' => 'teacher@mindfulchess.com',
            'password' => Hash::make('password'),
        ]);
        $teacher->assignRole('teacher');
        
        $student = User::create([
            'name' => 'Student User',
            'email' => 'student@mindfulchess.com',
            'password' => Hash::make('password'),
        ]);
        $student->assignRole('student');
    }
}
