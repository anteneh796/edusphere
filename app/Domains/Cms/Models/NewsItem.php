<?php

namespace App\Domains\Cms\Models;

use App\Domains\Accounts\Models\User;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class NewsItem extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'news';

    public const CATEGORIES = [
        'announcements' => 'Announcements',
        'academic' => 'Academic news',
        'sports' => 'Sports',
        'competitions' => 'Competitions',
        'achievements' => 'Achievements',
        'holidays' => 'Holidays & school calendar',
        'emergency' => 'Emergency notice',
    ];

    protected $fillable = [
        'slug',
        'title',
        'category',
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

    public function getContentAttribute(): ?string
    {
        return $this->attributes['body'] ?? null;
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = $this->attributes['image_path'] ?? null;

        return $path ? url('storage/'.$path) : null;
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->image_url;
    }

    public function getCategoryLabelAttribute(): ?string
    {
        return isset($this->attributes['category']) && $this->attributes['category']
            ? (self::CATEGORIES[$this->attributes['category']] ?? $this->attributes['category'])
            : null;
    }

    public function getTagsAttribute(): Collection
    {
        $tags = collect();

        $category = $this->category_label;

        if ($category) {
            $tags->push((object) ['name' => $category]);
        } elseif ($this->title) {
            $tags->push((object) ['name' => 'School News']);
        }

        return $tags;
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
