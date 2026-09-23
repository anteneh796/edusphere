<?php

namespace App\Domains\Cms\Models;

use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'testimonials';

    protected $fillable = [
        'name',
        'role',
        'quote',
        'avatar_path',
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

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path ? url('storage/'.$this->avatar_path) : null;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}
