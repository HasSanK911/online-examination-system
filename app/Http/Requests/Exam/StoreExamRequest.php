<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['super_admin', 'teacher']);
    }

    public function rules(): array
    {
        return [
            'course_id'               => 'required|exists:courses,id',
            'title'                   => 'required|string|max:255',
            'description'             => 'nullable|string',
            'instructions'            => 'nullable|string',
            'total_marks'             => 'required|numeric|min:1',
            'passing_marks'           => 'required|numeric|min:0|lte:total_marks',
            'duration_minutes'        => 'required|integer|min:5|max:300',
            'start_time'              => 'required|date|after:now',
            'end_time'                => 'required|date|after:start_time',
            'shuffle_questions'       => 'boolean',
            'shuffle_options'         => 'boolean',
            'allow_backtrack'         => 'boolean',
            'show_result_immediately' => 'boolean',
        ];
    }
}
