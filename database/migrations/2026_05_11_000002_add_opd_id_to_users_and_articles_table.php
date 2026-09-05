<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Superadmin: mengelola daftar OPD serta akun editor & leader.
        // Editor & leader: akses lintas OPD (opd_id boleh null / tidak terikat satu OPD).
        // Contributor: wajib terikat pada satu OPD.
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'contributor', 'editor', 'leader') NOT NULL DEFAULT 'contributor'");

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('opd_id')->nullable()->after('role')->constrained('opds')->nullOnDelete();
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('opd_id')->nullable()->after('category_id')->constrained('opds')->nullOnDelete();
            $table->index(['opd_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['opd_id', 'status']);
            $table->dropConstrainedForeignId('opd_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('opd_id');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('contributor', 'editor', 'leader') NOT NULL DEFAULT 'contributor'");
    }
};
