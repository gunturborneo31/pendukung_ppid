<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Opd extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'daerah', 'address', 'phone', 'logo', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($opd) {
            if (empty($opd->code)) {
                $opd->code = Str::upper(Str::slug($opd->name, '_'));
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function contributors()
    {
        if (!Schema::hasTable('opd_user')) {
            return $this->users()->where('role', 'contributor');
        }

        return $this->belongsToMany(User::class, 'opd_user')
            ->withTimestamps()
            ->where('role', 'contributor');
    }
}
