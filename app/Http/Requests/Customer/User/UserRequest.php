<?php

namespace App\Http\Requests\Customer\User;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class UserRequest extends FormRequest
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
            // users table fields
            'mobile' => 'required|string|max:20',
            'accessibility_statement' => 'nullable|string',
            'compliance_statement' => 'nullable|string',
            'photo' => 'nullable|string|regex:/^data:image\/(jpeg|jpg|png);base64,/',

            // user_infos table fields
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'info_photo' => 'nullable|string|regex:/^data:image\/(jpeg|jpg|png);base64,/',
            'dob' => 'nullable|date',
            'religion_id' => 'nullable|integer|exists:religions,id',
            'gender' => 'nullable|in:male,female,other',
            'occupation' => 'nullable|string|max:100',
            'nationality_id' => 'nullable|integer|exists:nationalities,id',
            'vulnerability_info' => 'nullable|string',

            // present address
            'pre_country' => 'nullable|string|max:100',
            'pre_srteet_address' => 'nullable|string|max:255',
            'pre_city' => 'nullable|string|max:100',
            'pre_provience' => 'nullable|string|max:100',
            'pre_zip' => 'nullable|string|max:20',

            // address flag
            'same_as_present_address' => 'nullable|boolean',

            // permanent address
            'per_country' => 'nullable|string|max:100',
            'per_srteet_address' => 'nullable|string|max:255',
            'per_city' => 'nullable|string|max:100',
            'per_provience' => 'nullable|string|max:100',
            'per_zip' => 'nullable|string|max:20',
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
