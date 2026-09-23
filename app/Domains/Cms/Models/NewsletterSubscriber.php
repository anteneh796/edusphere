<?php

namespace App\Domains\Cms\Models;

use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    use HasUuid;

    protected $table = 'newsletter_subscribers';

    protected $fillable = [
        'email',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function isSubscribed(): bool
    {
        return $this->unsubscribed_at === null;
    }

    public function scopeSubscribed(Builder $query): Builder
    {
        return $query->whereNull('unsubscribed_at');
    }

    public static function subscribe(string $email): self
    {
        return static::updateOrCreate(
            ['email' => strtolower(trim($email))],
            ['subscribed_at' => now()->setTimezone('UTC')]
        );
    }
}
