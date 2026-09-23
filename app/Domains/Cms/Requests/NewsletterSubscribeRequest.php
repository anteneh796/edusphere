<?php

namespace App\Domains\Cms\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewsletterSubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('newsletter_subscribers', 'email')->whereNull('unsubscribed_at'),
            ],
        ];
    }
}
