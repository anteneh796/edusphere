<?php

namespace App\Domains\Settings\Models;

use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasUuid;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    public static function value(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }

    public static function schoolName(): string
    {
        return (string) static::value('school_name', config('app.name', 'EduSphere'));
    }

    public static function academicYear(): ?string
    {
        return static::value('academic_year');
    }
}
