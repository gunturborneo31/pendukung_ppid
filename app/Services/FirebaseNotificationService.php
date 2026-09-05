<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\Notification;

/**
 * Service khusus untuk memicu & mengirim push notification (FCM) ke user.
 *
 * Menggunakan package kreait/laravel-firebase, dikonfigurasi lewat
 * config/firebase.php (kredensial diambil dari .env FIREBASE_CREDENTIALS).
 */
class FirebaseNotificationService
{
    public function __construct(protected Messaging $messaging)
    {
    }

    /**
     * Kirim notifikasi ke satu user (berdasarkan fcm_token miliknya).
     *
     * @param  array<string, string>  $data  Payload tambahan (contoh: ['article_id' => '12', 'url' => '/editor/inbox'])
     * @return bool True jika berhasil terkirim, false jika user tidak punya token atau gagal.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): bool
    {
        if (empty($user->fcm_token)) {
            Log::info("FCM: user #{$user->id} ({$user->email}) belum memiliki fcm_token, notifikasi dilewati.");

            return false;
        }

        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body))
            ->withData($data)
            ->withToken($user->fcm_token);

        try {
            $this->messaging->send($message);

            return true;
        } catch (NotFound|InvalidMessage $e) {
            // Token sudah tidak valid/kadaluarsa (device uninstall, dsb) -> bersihkan dari DB.
            Log::warning("FCM: token user #{$user->id} tidak valid, token dihapus. " . $e->getMessage());
            $user->update(['fcm_token' => null]);

            return false;
        } catch (MessagingException $e) {
            Log::error("FCM: gagal mengirim notifikasi ke user #{$user->id}. " . $e->getMessage());

            return false;
        }
    }

    /**
     * Kirim notifikasi yang sama ke banyak user sekaligus (multicast).
     * User yang belum punya fcm_token otomatis dilewati.
     *
     * @param  Collection<int, User>|iterable<User>  $users
     * @param  array<string, string>  $data
     */
    public function sendToUsers(iterable $users, string $title, string $body, array $data = []): ?MulticastSendReport
    {
        $tokens = collect($users)
            ->filter(fn (User $user) => filled($user->fcm_token))
            ->pluck('fcm_token')
            ->unique()
            ->values()
            ->all();

        if (empty($tokens)) {
            Log::info('FCM: tidak ada penerima dengan fcm_token aktif, notifikasi dilewati.');

            return null;
        }

        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        try {
            return $this->messaging->sendMulticast($message, $tokens);
        } catch (MessagingException $e) {
            Log::error('FCM: gagal mengirim notifikasi multicast. ' . $e->getMessage());

            return null;
        }
    }
}
