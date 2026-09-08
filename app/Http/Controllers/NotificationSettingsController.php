<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Panel diagnostik notifikasi push (khusus superadmin), dipakai untuk
 * memastikan notifikasi antar user (kontributor/editor/leader) benar-benar
 * sampai: lihat status token FCM tiap user, lalu kirim notifikasi percobaan.
 */
class NotificationSettingsController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'role', 'opd_id', 'fcm_token'])
            ->with('opd:id,name')
            ->orderBy('role')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'opd' => $user->opd?->name,
                // Jangan pernah kirim token asli ke frontend, cukup status aktif/tidaknya.
                'push_enabled' => filled($user->fcm_token),
            ]);

        return Inertia::render('Superadmin/NotificationSettings', [
            'users' => $users,
        ]);
    }

    public function sendTest(User $user, FirebaseNotificationService $notifier): RedirectResponse
    {
        if (blank($user->fcm_token)) {
            return back()->with('error', "Notifikasi tidak terkirim: {$user->name} belum mengaktifkan notifikasi push di perangkatnya.");
        }

        $sent = $notifier->sendToUser(
            $user,
            'Notifikasi Percobaan',
            'Ini adalah notifikasi percobaan dari Superadmin untuk memastikan notifikasi push Anda berjalan dengan baik.',
            ['type' => 'test', 'url' => '/dashboard']
        );

        if ($sent) {
            return back()->with('message', "Notifikasi percobaan berhasil dikirim ke {$user->name}.");
        }

        return back()->with('error', "Gagal mengirim notifikasi ke {$user->name}. Token kemungkinan sudah tidak valid dan telah dihapus otomatis, minta user mengaktifkan ulang notifikasi.");
    }
}
