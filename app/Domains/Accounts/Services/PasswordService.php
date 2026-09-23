<?php

namespace App\Domains\Accounts\Services;

use App\Domains\Accounts\Models\PasswordHistory;
use App\Domains\Accounts\Models\User;
use App\Domains\Settings\Models\Setting;
use Closure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordService
{
    /**
     * Password validation rules derived from Settings > Security.
     */
    public static function policyRules(): array
    {
        $rule = Password::min((int) Setting::value('password_min_length', 8));

        $upper = Setting::bool('password_require_uppercase', true);
        $lower = Setting::bool('password_require_lowercase', true);

        if ($upper && $lower) {
            $rule = $rule->mixedCase();
        } elseif ($lower) {
            $rule = $rule->letters();
        } elseif ($upper) {
            $rule = $rule->mixedCase();
        }

        if (Setting::bool('password_require_number', true)) {
            $rule = $rule->numbers();
        }

        if (Setting::bool('password_require_symbol', false)) {
            $rule = $rule->symbols();
        }

        return [$rule];
    }

    /**
     * Closure rule preventing reuse of the current or recent passwords.
     */
    public static function historyRule(?User $user = null): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($user): void {
            $target = $user ?? auth()->user();

            if ($target && static::wasUsed($target, (string) $value)) {
                $fail('You have used this password recently. Please choose a different password.');
            }
        };
    }

    public static function wasUsed(User $user, string $password): bool
    {
        $limit = (int) Setting::value('password_history_count', 5);

        if ($limit <= 0) {
            return false;
        }

        $hashes = $user->passwordHistories()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->pluck('password')
            ->prepend($user->password);

        foreach ($hashes as $hash) {
            if (Hash::check($password, $hash)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Store the current hash in history, then set the new password and mark
     * the change timestamp so password expiration can be enforced.
     */
    public static function recordHistory(User $user, string $password): void
    {
        PasswordHistory::create([
            'user_id' => $user->getKey(),
            'password' => $user->password,
        ]);

        $user->forceFill([
            'password' => $password,
            'password_changed_at' => now(),
            'must_change_password' => false,
        ])->save();

        self::pruneHistory($user);
    }

    protected static function pruneHistory(User $user): void
    {
        $limit = (int) Setting::value('password_history_count', 5);

        if ($limit <= 0) {
            return;
        }

        $user->passwordHistories()
            ->orderByDesc('created_at')
            ->offset($limit)
            ->limit(500)
            ->get()
            ->each->delete();
    }
}
