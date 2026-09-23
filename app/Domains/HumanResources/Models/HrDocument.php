<?php

namespace App\Domains\HumanResources\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\DocumentVerificationStatus;
use App\Support\Enums\HrDocumentCategory;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrDocument extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'category',
        'title',
        'file_path',
        'uploaded_by_id',
        'uploaded_at',
        'verification_status',
        'expires_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'expires_at' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function categoryEnum(): ?HrDocumentCategory
    {
        return HrDocumentCategory::tryFrom($this->category);
    }

    public function categoryLabel(): string
    {
        return $this->categoryEnum()?->label() ?? ucfirst(str_replace('_', ' ', $this->category));
    }

    public function verificationEnum(): ?DocumentVerificationStatus
    {
        return DocumentVerificationStatus::tryFrom($this->verification_status);
    }

    public function verificationBadgeColor(): string
    {
        return $this->verificationEnum()?->badgeColor() ?? 'neutral';
    }
}