<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleSeo extends Model
{
    protected $table = 'article_seo';

    protected $fillable = [
        'article_id', 'seo_title', 'seo_description', 'seo_keywords',
        'og_title', 'og_description', 'og_image', 'canonical_url',
        'robots_index', 'robots_follow',
    ];

    protected $casts = [
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
