<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;

/**
 * Contoh pemanggilan FirebaseNotificationService lewat Artisan Command.
 *
 * Penggunaan:
 *   php artisan fcm:test-send {user_id} --title="Judul" --body="Isi pesan"
 *
 * Contoh:
 *   php artisan fcm:test-send 5 --title="Ada artikel baru" --body="Silakan cek inbox review."
 */
class SendTestPushNotification extends Command
{
    protected $signature = 'fcm:test-send
        {user_id : ID user penerima notifikasi}
        {--title=Notifikasi Uji Coba : Judul notifikasi}
        {--body=Ini adalah pesan uji coba dari Pendukung PPID. : Isi notifikasi}';

    protected $description = 'Kirim push notification uji coba ke satu user berdasarkan fcm_token miliknya.';

    public function handle(FirebaseNotificationService $notifier): int
    {
        $user = User::find($this->argument('user_id'));

        if (!$user) {
            $this->error('User tidak ditemukan.');

            return self::FAILURE;
        }

        $sent = $notifier->sendToUser(
            $user,
            (string) $this->option('title'),
            (string) $this->option('body'),
            ['type' => 'test']
        );

        if ($sent) {
            $this->info("Notifikasi berhasil dikirim ke {$user->name} ({$user->email}).");

            return self::SUCCESS;
        }

        $this->warn("Notifikasi gagal/dilewati untuk {$user->name}. Pastikan fcm_token tersimpan & valid.");

        return self::FAILURE;
    }
}
