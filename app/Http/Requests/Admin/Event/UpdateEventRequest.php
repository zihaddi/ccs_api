<?php

namespace App\Http\Requests\Admin\Event;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class UpdateEventRequest extends FormRequest
{
    use HttpResponses;
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'unique:events,slug,' . $this->route('event') . ',id'
            ],
            'category_id' => ['required', 'exists:event_categories,id'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'string'],
            'event_at' => ['nullable', 'date'],
            'status' => ['required', 'boolean'],
            'details' => ['nullable', 'array'],
            'details.*.id' => ['nullable', 'exists:event_details,id'],
            'details.*.year_id' => ['required', 'exists:years,id'],
            'details.*.venue' => ['required', 'string', 'max:255'],
            'details.*.start_date' => ['required', 'date'],
            'details.*.end_date' => ['required', 'date', 'after_or_equal:details.*.start_date'],
            'details.*.status' => ['required', 'boolean'],
        ];
    }

    /**
     * @param Validator $validator
     * @return HttpResponseException
     */
    public function failedValidation(Validator $validator): HttpResponseException
    {
        throw new HttpResponseException(
            $this->error(
                $validator->errors()->messages(),
                ValidationConstants::ERROR,
                Response::HTTP_NOT_FOUND
            )
        );
    }
}
