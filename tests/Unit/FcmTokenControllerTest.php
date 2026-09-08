<?php

namespace Tests\Unit;

use App\Http\Controllers\FcmTokenController;
use Illuminate\Http\Request;
use Tests\TestCase;

class FcmTokenControllerTest extends TestCase
{
    public function test_store_updates_authenticated_user_fcm_token(): void
    {
        $user = new class
        {
            public array $updated = [];

            public function update(array $attributes): bool
            {
                $this->updated = $attributes;

                return true;
            }
        };

        $request = new class('test-fcm-token-123', $user) extends Request
        {
            public function __construct(private string $token, private $user)
            {
                parent::__construct([], ['fcm_token' => $this->token]);
            }

            public function validate(array $rules = [], ...$params): array
            {
                return ['fcm_token' => $this->token];
            }

            public function user($guard = null)
            {
                return $this->user;
            }
        };

        $response = app(FcmTokenController::class)->store($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['message' => 'FCM token tersimpan.'], json_decode($response->getContent(), true));

        $this->assertSame(['fcm_token' => 'test-fcm-token-123'], $user->updated);
    }
}
