<?php

namespace Database\Seeders;

use App\Models\Opd;
use Illuminate\Database\Seeder;

class OpdSeeder extends Seeder
{
    public function run(): void
    {
        $opds = [
            ['name' => 'Dinas Komunikasi dan Informatika', 'code' => 'DISKOMINFO', 'daerah' => 'Prov. Kaltim'],
            ['name' => 'Dinas Pendidikan', 'code' => 'DISDIK', 'daerah' => 'Prov. Kaltim'],
            ['name' => 'Dinas Kesehatan', 'code' => 'DINKES', 'daerah' => 'Prov. Kaltim'],
            ['name' => 'Dinas Pekerjaan Umum dan Penataan Ruang', 'code' => 'DPUPR', 'daerah' => 'Prov. Kaltim'],
            ['name' => 'Badan Perencanaan Pembangunan Daerah', 'code' => 'BAPPEDA', 'daerah' => 'Prov. Kaltim'],
        ];

        foreach ($opds as $opd) {
            Opd::create($opd);
        }
    }
}
