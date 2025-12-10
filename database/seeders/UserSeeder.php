<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        // Create regular user
        User::updateOrCreate(
            ['email' => 'user@user.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('user123'),
                'role' => 'user',
            ]
        );

        $this->command->info('Default users created successfully!');
        $this->command->info('Admin: admin@admin.com / admin123');
        $this->command->info('User: user@user.com / user123');
    }
}
