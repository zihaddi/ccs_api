<?php

namespace App\Http\Requests\Admin\Plan;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class PlanStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:plans,name',
            'slug' => 'required|string|max:255|unique:plans,slug',
            'description' => 'nullable|string',
            'status' => 'required|integer|in:0,1', // Assuming 0 = inactive, 1 = active

            // Plan Features (One-to-Many)
            'features' => 'nullable|array',
            'features.*.feature' => 'required|string|max:255',
            'features.*.description' => 'nullable|string',
            'features.*.is_included' => 'required|boolean',

            // Plan Prices (One-to-Many)
            'prices' => 'nullable|array',
            'prices.*.billing_cycle' => 'required|string|in:monthly,quarterly,yearly', // Adjust billing cycles as needed
            'prices.*.price' => 'required|numeric|min:0',
            'prices.*.discount' => 'nullable|numeric|min:0',
            'prices.*.final_price' => 'required|numeric|min:0',
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
