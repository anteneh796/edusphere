<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Accounts\Models\User;
use App\Domains\Notifications\Models\Notification;
use App\Support\Enums\RoleName;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * String keys accepted by create(). `category` and `priority` default
     * sensibly when omitted.
     */
    private const DEFAULTS = [
        'category' => 'system',
        'priority' => 'low',
        'icon' => 'bell',
    ];

    /**
     * Create one notification for every user holding any of the given roles.
     */
    public function sendToRoles(array $roles, array $payload): int
    {
        $userIds = User::whereHas('roles', fn ($query) => $query->whereIn('name', $roles))
            ->pluck('id');

        if ($userIds->isEmpty()) {
            return 0;
        }

        return $this->sendToMany($userIds->all(), $payload);
    }

    /**
     * Notify everyone qualified to review a request (correct role or any role
     * holding the approvals.approve permission).
     */
    public function sendToApprovers(array $payload): int
    {
        $userIds = User::with('roles.permissions:id,name')
            ->whereNotNull('id')
            ->get()
            ->filter(fn (User $user) => $user->roles->isNotEmpty() && (
                $user->hasPermission('approvals.approve') || $user->isSuperAdmin()
            ))
            ->pluck('id')
            ->all();

        return $this->sendToMany($userIds, $payload);
    }

    public function sendToMany(array $userIds, array $payload): int
    {
        if (empty($userIds)) {
            return 0;
        }

        $now = now();
        $rows = array_map(fn (string|int $userId) => $this->row($userId, $payload, $now), $userIds);

        Notification::insert($rows);

        return count($rows);
    }

    public function sendToUser(string|int $userId, array $payload): Notification
    {
        $row = $this->row($userId, $payload, now());

        unset($row['id'], $row['created_at'], $row['updated_at']);

        return Notification::create($row);
    }

    public function unreadCount(?string $userId = null): int
    {
        return Notification::where('user_id', $userId ?? auth()->id())->unread()->count();
    }

    private function row(string|int $userId, array $payload, Carbon $now, bool $uuid = true): array
    {
        return [
            'id' => $uuid ? (string) Str::orderedUuid() : null,
            'user_id' => (string) $userId,
            'type' => $payload['type'] ?? 'system',
            'title' => $payload['title'],
            'body' => $payload['body'] ?? null,
            'category' => $payload['category'] ?? self::DEFAULTS['category'],
            'priority' => $payload['priority'] ?? self::DEFAULTS['priority'],
            'icon' => $payload['icon'] ?? self::DEFAULTS['icon'],
            'redirect_url' => $payload['redirect_url'] ?? null,
            'data' => isset($payload['data']) ? json_encode($payload['data']) : null,
            'read_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * Notify receptionists and administrators about a fresh website inquiry.
     */
    public static function inquiryReceived(string $inquirerName, string $type, string $url): void
    {
        app(static::class)->sendToRoles(
            [RoleName::Reception->value, RoleName::SchoolAdmin->value, RoleName::Principal->value, RoleName::SuperAdmin->value],
            [
                'type' => 'inquiry',
                'category' => 'inquiry',
                'priority' => 'high',
                'icon' => 'mail',
                'title' => __('New website inquiry'),
                'body' => __(':name submitted a :type inquiry.', ['name' => $inquirerName, 'type' => $type]),
                'redirect_url' => $url,
            ]
        );
    }
}
