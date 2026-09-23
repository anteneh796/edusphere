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
        'album',
        'image_path',
        'media_type',
        'video_url',
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

    public const MEDIA_IMAGE = 'image';

    public const MEDIA_VIDEO = 'video';

    public const MEDIA_TYPE_LABELS = [
        self::MEDIA_IMAGE => 'Photo',
        self::MEDIA_VIDEO => 'Video',
    ];

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? url('storage/'.$this->image_path) : null;
    }

    public function getTitleAttribute(): ?string
    {
        return $this->caption;
    }

    public function isVideo(): bool
    {
        return $this->media_type === self::MEDIA_VIDEO;
    }

    public function getVideoEmbedUrlAttribute(): ?string
    {
        $url = $this->video_url;

        if (! $url) {
            return null;
        }

        if (preg_match('/youtu\.be\/([\w-]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        if (preg_match('/youtube\.com\/watch\?v=([\w-]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        if (preg_match('/youtube\.com\/embed\/([\w-]+)/', $url, $matches)) {
            return $url;
        }

        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        return $url;
    }

    public function getAlbumLabelAttribute(): ?string
    {
        return $this->album ? ucwords($this->album) : null;
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
