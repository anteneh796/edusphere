<?php

namespace App\Domains\Cms\Models;

use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'slug',
        'title',
        'subtitle',
        'body',
        'published',
    ];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
        ];
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function url(): string
    {
        return match ($this->slug) {
            'home' => route('public.home'),
            default => route('public.page', $this->slug),
        };
    }

    /* --------------------------------- Scopes ---------------------------------- */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }
}