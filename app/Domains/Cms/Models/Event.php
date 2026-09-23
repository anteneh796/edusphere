<?php

namespace App\Domains\Cms\Models;

use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'events';

    protected $fillable = [
        'slug',
        'title',
        'description',
        'location',
        'starts_at',
        'ends_at',
        'cover_path',
        'featured',
        'published',
    ];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'published' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function getStartsAtForHuman(): string
    {
        return $this->starts_at?->format('F j, Y · g:i A') ?? 'To be announced';
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_path ? url('storage/'.$this->cover_path) : null;
    }

    /* -------------------------------- Scopes -------------------------------- */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now());
    }
}
