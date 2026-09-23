<?php

namespace App\Domains\Students\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\StudentDocumentCategory;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDocument extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'category',
        'name',
        'path',
        'mime',
        'size',
        'verified',
        'verified_by_id',
        'verified_at',
        'notes',
        'uploaded_by_id',
    ];

    protected function casts(): array
    {
        return [
            'verified' => 'boolean',
            'verified_at' => 'datetime',
            'size' => 'integer',
        ];
    }

    public function categoryLabel(): string
    {
        return StudentDocumentCategory::tryFrom($this->category)?->label() ?? ucfirst($this->category);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }
}
