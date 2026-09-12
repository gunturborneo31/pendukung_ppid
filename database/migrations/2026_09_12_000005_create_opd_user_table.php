<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opd_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->constrained('opds')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['opd_id', 'user_id']);
        });

        DB::table('users')
            ->where('role', 'contributor')
            ->whereNotNull('opd_id')
            ->orderBy('id')
            ->chunkById(200, function ($users) {
                $rows = [];
                $now = now();

                foreach ($users as $user) {
                    $rows[] = [
                        'opd_id' => $user->opd_id,
                        'user_id' => $user->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($rows)) {
                    DB::table('opd_user')->upsert($rows, ['opd_id', 'user_id']);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('opd_user');
    }
};
