<?php

namespace App\Http\Requests\Admin\Compliance;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class ComplianceStoreRequest extends FormRequest
{
    use HttpResponses;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|unique:compliances,title',
            'slug' => 'required|string|max:255|unique:compliances,slug',
            'description' => 'nullable|string',
            'images' => 'nullable',
            'totalPrice' => 'nullable|numeric|min:0',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'status' => 'required|integer|in:0,1', // Assuming 0 = inactive, 1 = active
            'details' => 'nullable|array',
            'details.*.details' => 'required|string',
            'details.*.title' => 'required|string',
            'details.*.price' => 'required|numeric|min:0',
            'details.*.status' => 'required|integer|in:0,1',
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
