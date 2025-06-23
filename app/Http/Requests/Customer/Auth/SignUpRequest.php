<?php

namespace App\Http\Requests\Customer\Auth;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class SignUpRequest extends FormRequest
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
            'mobile' => 'required|string|max:15', // Adjust max length as needed
            'ccode' => 'required|string|max:5', // Adjust max length as needed
            'email' => 'required|email|max:255|unique:users,email', // Ensure email is unique in users table
            'first_name' => 'required|string|max:50', // Adjust max length as needed
            'last_name' => 'required|string|max:50', // Adjust max length as needed
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => 'The mobile number is required.',
            'ccode.required' => 'The country code is required.',
            'email.required' => 'The email address is required.',
            'email.unique' => 'This email is already registered.',
            'first_name.required' => 'The first name is required.',
            'last_name.required' => 'The last name is required.',
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
