<?php

namespace App\Domains\Cms\Models;

use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ContentBlock extends Model
{
    use HasFactory, HasUuid;

    /** Public pages (or 'site' for site-wide sections like office hours). */
    public const PAGE_LABELS = [
        'home' => 'Home',
        'about' => 'About',
        'academics' => 'Academics',
        'admissions' => 'Admissions',
        'contact' => 'Contact',
        'site' => 'Site-wide (header & footer)',
    ];

    /** Known section keys with a short admin label. */
    public const KEY_LABELS = [
        'hero' => 'Hero (intro banner)',
        'stats' => 'Statistics',
        'principal-message' => 'Principal message',
        'programmes' => 'Academic programmes',
        'why-us' => 'Why choose us',
        'facilities' => 'Facilities',
        'mv-cards' => 'Mission / vision / values',
        'story' => 'Our story',
        'leadership' => 'Leadership team',
        'stages' => 'Programme stages',
        'extras' => 'Beyond the classroom',
        'steps' => 'Admissions steps',
        'documents' => 'Documents & requirements',
        'age-table' => 'Age criteria by grade',
        'faq' => 'Frequently asked questions',
        'departments' => 'Contact departments',
        'office-hours' => 'Office hours',
        'events' => 'Upcoming events (heading)',
        'news' => 'Latest news (heading)',
        'gallery' => 'Gallery (heading)',
        'testimonials' => 'Testimonials (heading)',
        'cta' => 'Call to action',
    ];

    protected $fillable = [
        'page_slug',
        'key',
        'eyebrow',
        'title',
        'lead',
        'body',
        'payload',
        'sort_order',
        'published',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'sort_order' => 'integer',
            'published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(static fn () => static::forgetCache());
        static::deleted(static fn () => static::forgetCache());
    }

    /* ------------------------------- Accessors ------------------------------- */

    /** Items list stored in the JSON payload (each item may hold icon/title/text/detail/points). */
    public function getItemsAttribute(): array
    {
        return ($this->payload ?? [])['items'] ?? [];
    }

    public function hasSectionHead(): bool
    {
        return (bool) ($this->eyebrow ?? $this->title ?? $this->lead);
    }

    public function getPageLabelAttribute(): string
    {
        return self::PAGE_LABELS[$this->page_slug] ?? ucfirst($this->page_slug);
    }

    public function getKeyLabelAttribute(): string
    {
        return self::KEY_LABELS[$this->key] ?? title(str_replace('-', ' ', $this->key));
    }

    /* ------------------------------- Scopes ---------------------------------- */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('key');
    }

    public function scopeForPage(Builder $query, string $page): Builder
    {
        return $query->where('page_slug', $page);
    }

    /* --------------------------- Public read helpers -------------------------- */

    /**
     * Published blocks for a page, keyed by section key.
     */
    public static function forPage(string $page): Collection
    {
        return static::allCached()->get($page, collect())->keyBy('key');
    }

    public static function allCached(): Collection
    {
        return Cache::remember('content_blocks.all', now()->addHour(), static function () {
            return static::published()->ordered()->get()->groupBy('page_slug');
        });
    }

    public static function forgetCache(): void
    {
        Cache::forget('content_blocks.all');
    }
}
