<?php

namespace Database\Seeders;

use App\Models\Opd;
use Illuminate\Database\Seeder;

class OpdSeeder extends Seeder
{
    public function run(): void
    {
        $opds = [
            ['name' => 'Dinas Komunikasi dan Informatika', 'code' => 'DISKOMINFO'],
            ['name' => 'Dinas Pendidikan', 'code' => 'DISDIK'],
            ['name' => 'Dinas Kesehatan', 'code' => 'DINKES'],
            ['name' => 'Dinas Pekerjaan Umum dan Penataan Ruang', 'code' => 'DPUPR'],
            ['name' => 'Badan Perencanaan Pembangunan Daerah', 'code' => 'BAPPEDA'],
        ];

        foreach ($opds as $opd) {
            Opd::create($opd);
        }
    }
}
