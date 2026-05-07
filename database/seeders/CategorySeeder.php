<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Informasi Publik',
            'Pengumuman',
            'Berita Daerah',
            'Layanan PPID',
            'Regulasi & Kebijakan',
        ];

        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }
    }
}
