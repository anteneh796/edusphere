<?php

namespace App\Domains\Cms\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UpdateContentBlockRequest extends StoreContentBlockRequest
{
    protected function keyUniqueRule(): Unique
    {
        return Rule::unique('content_blocks', 'key')
            ->where('page_slug', $this->input('page_slug'))
            ->ignore($this->route('contentBlock')?->getKey());
    }
}
