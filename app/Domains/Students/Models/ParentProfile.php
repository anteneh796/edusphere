<?php

namespace App\Domains\Students\Models;

use App\Support\Enums\ParentRelationship;

class ParentProfile extends Guardian
{
    public function relationshipLabel(): string
    {
        return ParentRelationship::tryFrom($this->relationship)?->label()
            ?? ucfirst($this->relationship ?? '');
    }
}
