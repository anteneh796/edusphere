<?php

namespace App\Domains\Exams\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveExamPapersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'papers' => ['nullable', 'array'],
            'papers.*.class_room_id' => ['nullable', 'required_with:papers.*.subject_id', 'exists:class_rooms,id'],
            'papers.*.subject_id' => ['nullable', 'required_with:papers.*.class_room_id', 'exists:subjects,id'],
            'papers.*.max_marks' => ['nullable', 'numeric', 'min:1', 'max:10000'],
            'papers.*.pass_marks' => ['nullable', 'numeric', 'min:0', 'lt:papers.*.max_marks'],
            'papers.*.weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'papers.*.exam_date' => ['nullable', 'date'],
            'papers.*.instruction' => ['nullable', 'string', 'max:500'],
        ];
    }
}
