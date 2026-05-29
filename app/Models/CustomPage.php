<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'published',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags($this->content), 200);
    }
}
