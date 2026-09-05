<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    /**
     * Simpan/perbarui FCM token milik user yang sedang login.
     * Dipanggil oleh frontend setelah Firebase Messaging berhasil
     * mendapatkan registration token (lihat firebase-messaging-sw.js & getToken()).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'fcm_token' => 'required|string|max:1000',
        ]);

        $request->user()->update([
            'fcm_token' => $data['fcm_token'],
        ]);

        return response()->json(['message' => 'FCM token tersimpan.']);
    }

    /**
     * Hapus FCM token milik user (dipanggil saat logout / user menonaktifkan notifikasi).
     */
    public function destroy(Request $request)
    {
        $request->user()->update(['fcm_token' => null]);

        return response()->json(['message' => 'FCM token dihapus.']);
    }
}
