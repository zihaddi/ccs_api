<?php


namespace App\Http\Requests\Admin\Survey;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:1000'],
            'question_bn' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'The question field is required.',
            'question.max' => 'The question may not be greater than 1000 characters.',
            'question_bn.max' => 'The Bengali question may not be greater than 1000 characters.',
            'status.required' => 'The status field is required.',
            'status.boolean' => 'The status field must be true or false.',
            'start_date.date' => 'The start date must be a valid date.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
        ];
    }
}