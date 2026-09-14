<?php

namespace App\Domains\Cms\Models;

use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GalleryItem extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'caption',
        'image_path',
        'sort_order',
        'published',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'published' => 'boolean',
        ];
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    /* --------------------------------- Scopes ---------------------------------- */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}