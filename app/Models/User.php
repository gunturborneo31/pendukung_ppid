<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    private static ?bool $hasOpdUserTable = null;

    protected $fillable = ['name', 'email', 'password', 'role', 'field', 'opd_id', 'fcm_token'];

    protected $hidden = ['password', 'remember_token', 'fcm_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function articles()
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function editedArticles()
    {
        return $this->hasMany(Article::class, 'editor_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function opd()
    {
        return $this->belongsTo(Opd::class);
    }

    public function accessibleOpds()
    {
        return $this->belongsToMany(Opd::class, 'opd_user')
            ->withTimestamps()
            ->orderBy('opds.name');
    }

    public function accessibleOpdIds(): array
    {
        if (self::$hasOpdUserTable === null) {
            self::$hasOpdUserTable = Schema::hasTable('opd_user');
        }

        if (!self::$hasOpdUserTable) {
            return !empty($this->opd_id) ? [(int) $this->opd_id] : [];
        }

        $ids = $this->relationLoaded('accessibleOpds')
            ? $this->accessibleOpds->pluck('id')->map(fn ($id) => (int) $id)->all()
            : $this->accessibleOpds()->pluck('opds.id')->map(fn ($id) => (int) $id)->all();

        if (empty($ids) && !empty($this->opd_id)) {
            $ids[] = (int) $this->opd_id;
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * Role yang boleh mengakses data lintas OPD (superadmin, editor, leader, uploader).
     */
    public function hasCrossOpdAccess(): bool
    {
        return in_array($this->role, ['superadmin', 'editor', 'leader', 'uploader']);
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Get bidang (field) yang diwakili editor.
     */
    public function getFieldAttribute($value)
    {
        return $value ?: 'Umum';
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }
}
