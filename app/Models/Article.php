<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'body_web', 'excerpt', 'thumbnail',
        'caption_ig', 'hashtags_ig', 'status', 'target_platform',
        'author_id', 'editor_id', 'editor_notes', 'published_at',
        'preview_token', 'category_id', 'opd_id',
    ];

    protected $casts = [
        'body_web' => 'array',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->preview_token)) {
                $article->preview_token = Str::uuid()->toString();
            }
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title) . '-' . Str::random(6);
            }
            // Artikel selalu terikat ke OPD milik penulisnya (denormalisasi untuk performa query).
            if (empty($article->opd_id) && $article->author_id) {
                $article->opd_id = User::find($article->author_id)?->opd_id;
            }
        });

        // Invalidasi cache ringkasan dashboard OPD setiap ada perubahan status artikel.
        static::saved(fn () => Cache::forget('dashboard.opd_summary'));
        static::deleted(fn () => Cache::forget('dashboard.opd_summary'));
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function opd()
    {
        return $this->belongsTo(Opd::class);
    }

    /**
     * Batasi query ke OPD milik user, kecuali user punya akses lintas OPD.
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasCrossOpdAccess()) {
            return $query;
        }

        return $query->where('opd_id', $user->opd_id);
    }

    public function seo()
    {
        return $this->hasOne(ArticleSeo::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
