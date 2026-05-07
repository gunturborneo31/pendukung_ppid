<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Kontributor PPID',
            'email' => 'contributor@ppid.local',
            'password' => Hash::make('password'),
            'role' => 'contributor',
        ]);

        User::create([
            'name' => 'Editor PPID',
            'email' => 'editor@ppid.local',
            'password' => Hash::make('password'),
            'role' => 'editor',
        ]);

        User::create([
            'name' => 'Pimpinan PPID',
            'email' => 'leader@ppid.local',
            'password' => Hash::make('password'),
            'role' => 'leader',
        ]);
    }
}
