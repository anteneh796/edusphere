<?php

namespace App\Domains\Admissions\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\AdmissionDocumentCategory;
use App\Support\Enums\DocumentVerificationStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantDocument extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'application_id',
        'category',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'status',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    public function categoryLabel(): string
    {
        return AdmissionDocumentCategory::tryFrom($this->category)?->label() ?? ucfirst($this->category ?? '');
    }

    public function statusLabel(): string
    {
        return DocumentVerificationStatus::tryFrom($this->status)?->label() ?? ucfirst($this->status ?? '');
    }

    public function statusBadgeColor(): string
    {
        return DocumentVerificationStatus::tryFrom($this->status)?->badgeColor() ?? 'neutral';
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
