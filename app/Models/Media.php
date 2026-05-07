<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'article_id', 'uploader_id', 'type', 'path', 'alt_text', 'size',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
