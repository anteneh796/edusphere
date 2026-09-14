<?php

namespace App\Domains\Cms\Models;

use App\Domains\Accounts\Models\User;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsItem extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'news';

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'body',
        'image_path',
        'author_id',
        'published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    /* -------------------------------- Relations -------------------------------- */

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /* --------------------------------- Scopes ---------------------------------- */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true)
            ->whereNotNull('published_at');
    }
}