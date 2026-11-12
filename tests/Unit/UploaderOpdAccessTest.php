<?php

namespace Tests\Unit;

use App\Http\Controllers\ArticleController;
use App\Models\Article;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class UploaderOpdAccessTest extends TestCase
{
    /**
     * User palsu dengan daftar OPD tetap, tanpa query database.
     */
    private function fakeUser(string $role, array $opdIds = []): User
    {
        $user = new class extends User
        {
            public array $fakeOpdIds = [];

            public function accessibleOpdIds(): array
            {
                return $this->fakeOpdIds;
            }
        };

        $user->forceFill(['id' => 99, 'role' => $role]);
        $user->fakeOpdIds = $opdIds;

        return $user;
    }

    private function article(?int $opdId): Article
    {
        return (new Article())->forceFill(['id' => 1, 'opd_id' => $opdId, 'author_id' => 1]);
    }

    private function controller(): ArticleController
    {
        $notifier = (new \ReflectionClass(FirebaseNotificationService::class))->newInstanceWithoutConstructor();

        return new ArticleController($notifier);
    }

    private function assertForbidden(callable $callback): void
    {
        try {
            $callback();
            $this->fail('Seharusnya ditolak dengan 403.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_uploader_has_no_cross_opd_access(): void
    {
        $uploader = $this->fakeUser('uploader', [2]);

        $this->assertFalse($uploader->hasCrossOpdAccess());
        $this->assertTrue($uploader->canAccessOpd(2));
        $this->assertFalse($uploader->canAccessOpd(3));
        $this->assertFalse($uploader->canAccessOpd(null));
    }

    public function test_uploader_without_assigned_opd_cannot_access_any_opd(): void
    {
        $uploader = $this->fakeUser('uploader', []);

        $this->assertFalse($uploader->canAccessOpd(1));
    }

    public function test_editor_and_leader_keep_cross_opd_access(): void
    {
        $this->assertTrue($this->fakeUser('editor')->canAccessOpd(5));
        $this->assertTrue($this->fakeUser('leader')->canAccessOpd(5));
        $this->assertTrue($this->fakeUser('superadmin')->canAccessOpd(5));
    }

    public function test_uploader_cannot_update_upload_proofs_of_other_opd(): void
    {
        $this->actingAs($this->fakeUser('uploader', [2]));

        $this->assertForbidden(fn () => $this->controller()->updateUploadProofs(
            Request::create('/upload-proofs/articles/1', 'PUT', ['upload_proofs' => ['instagram' => 'x']]),
            $this->article(3)
        ));
    }

    public function test_uploader_cannot_view_upload_proofs_of_other_opd(): void
    {
        $this->actingAs($this->fakeUser('uploader', [2]));

        $this->assertForbidden(fn () => $this->controller()->uploadProofs($this->article(3)));
    }

    public function test_uploader_cannot_access_article_without_opd(): void
    {
        $this->actingAs($this->fakeUser('uploader', [2]));

        $this->assertForbidden(fn () => $this->controller()->uploadProofs($this->article(null)));
    }

    public function test_visible_to_scope_limits_uploader_to_assigned_opds(): void
    {
        $query = Article::query()->visibleTo($this->fakeUser('uploader', [2, 4]));

        $this->assertStringContainsString('"opd_id" in', $query->toSql());
        $this->assertSame([2, 4], $query->getBindings());
    }
}
