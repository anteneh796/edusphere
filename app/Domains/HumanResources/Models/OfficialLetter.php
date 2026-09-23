<?php

namespace App\Domains\HumanResources\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\OfficialLetterType;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficialLetter extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'reference_number',
        'employee_id',
        'letter_type',
        'title',
        'content',
        'issued_by_id',
        'issued_on',
    ];

    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_id');
    }

    public function typeEnum(): ?OfficialLetterType
    {
        return OfficialLetterType::tryFrom($this->letter_type);
    }

    public function typeLabel(): string
    {
        return $this->typeEnum()?->label() ?? ucfirst(str_replace('_', ' ', $this->letter_type));
    }

    public static function nextReferenceNumber(): string
    {
        $prefix = 'HR/LET/'.now()->format('Y').'/';
        $last = self::query()
            ->where('reference_number', 'like', "{$prefix}%")
            ->orderByDesc('reference_number')
            ->value('reference_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}