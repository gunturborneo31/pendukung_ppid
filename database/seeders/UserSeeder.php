<?php

namespace Database\Seeders;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin PPID',
            'email' => 'superadmin@ppid.local',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);

        // Editor & leader punya akses lintas OPD, sehingga opd_id boleh kosong (tidak terikat satu OPD saja).
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

        // Kontributor wajib terikat pada satu OPD dan hanya bisa mengakses artikel OPD-nya sendiri.
        $diskominfo = Opd::where('code', 'DISKOMINFO')->first();
        $disdik = Opd::where('code', 'DISDIK')->first();

        User::create([
            'name' => 'Kontributor Diskominfo',
            'email' => 'contributor@ppid.local',
            'password' => Hash::make('password'),
            'role' => 'contributor',
            'opd_id' => $diskominfo?->id,
        ]);

        User::create([
            'name' => 'Kontributor Disdik',
            'email' => 'contributor.disdik@ppid.local',
            'password' => Hash::make('password'),
            'role' => 'contributor',
            'opd_id' => $disdik?->id,
        ]);
    }
}
