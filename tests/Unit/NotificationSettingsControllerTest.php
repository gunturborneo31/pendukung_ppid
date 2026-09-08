<?php

namespace Tests\Unit;

use App\Http\Controllers\NotificationSettingsController;
use App\Services\FirebaseNotificationService;
use Tests\TestCase;

class NotificationSettingsControllerTest extends TestCase
{
    private function fakeNotifier(?bool $sendResult): FirebaseNotificationService
    {
        return new class($sendResult) extends FirebaseNotificationService
        {
            public ?array $lastCall = null;

            public function __construct(private ?bool $sendResult)
            {
                // Sengaja tidak memanggil parent constructor supaya tidak butuh binding Messaging asli.
            }

            public function sendToUser(\App\Models\User $user, string $title, string $body, array $data = []): bool
            {
                $this->lastCall = compact('user', 'title', 'body', 'data');

                return $this->sendResult ?? false;
            }
        };
    }

    private function fakeUser(?string $fcmToken, string $name = 'Budi'): \App\Models\User
    {
        return new class($fcmToken, $name) extends \App\Models\User
        {
            public function __construct(public $fcm_token, public $name)
            {
            }
        };
    }

    public function test_send_test_fails_gracefully_when_user_has_no_token(): void
    {
        $user = $this->fakeUser(null);
        $notifier = $this->fakeNotifier(true);

        $response = app(NotificationSettingsController::class)->sendTest($user, $notifier);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('belum mengaktifkan notifikasi push', session('error'));
        $this->assertNull($notifier->lastCall, 'Notifier tidak boleh dipanggil jika user belum punya token.');
    }

    public function test_send_test_reports_success_when_notifier_sends_successfully(): void
    {
        $user = $this->fakeUser('token-abc');
        $notifier = $this->fakeNotifier(true);

        $response = app(NotificationSettingsController::class)->sendTest($user, $notifier);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('berhasil dikirim', session('message'));
        $this->assertSame('token-abc', $user->fcm_token);
        $this->assertNotNull($notifier->lastCall);
        $this->assertSame('Notifikasi Percobaan', $notifier->lastCall['title']);
    }

    public function test_send_test_reports_failure_when_notifier_fails(): void
    {
        $user = $this->fakeUser('token-abc');
        $notifier = $this->fakeNotifier(false);

        $response = app(NotificationSettingsController::class)->sendTest($user, $notifier);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('Gagal mengirim', session('error'));
    }
}
