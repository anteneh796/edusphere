<?php

namespace App\Domains\Cms\Requests;

use App\Domains\Cms\Models\ContentBlock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreContentBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page_slug' => ['required', 'string', 'in:'.implode(',', array_keys(ContentBlock::PAGE_LABELS))],
            'key' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/', $this->keyUniqueRule()],
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:190'],
            'lead' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string'],
            'payload' => ['nullable', 'json'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published' => ['nullable', 'boolean'],
        ];
    }

    protected function keyUniqueRule(): Unique
    {
        return Rule::unique('content_blocks', 'key')
            ->where('page_slug', $this->input('page_slug'));
    }
}
