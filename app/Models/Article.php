<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'body_web', 'excerpt', 'thumbnail',
        'caption_ig', 'hashtags_ig', 'status', 'target_platform',
        'author_id', 'editor_id', 'editor_notes', 'published_at',
        'preview_token', 'category_id',
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
        });
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
