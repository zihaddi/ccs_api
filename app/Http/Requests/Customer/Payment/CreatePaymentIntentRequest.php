<?php

namespace App\Http\Requests\Customer\Payment;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class CreatePaymentIntentRequest extends FormRequest
{
    use HttpResponses;
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'gateway' => 'required|string|in:stripe,sslcommerz,paypal,googlepay',
            'plan_id' => 'nullable|exists:plans,id',
            'billing_cycle' => 'required_with:plan_id|string|in:monthly,yearly'
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Payment amount is required',
            'amount.numeric' => 'Payment amount must be a number',
            'amount.min' => 'Payment amount must be at least 1',
            'currency.required' => 'Currency code is required',
            'currency.size' => 'Currency code must be exactly 3 characters',
            'plan_id.exists' => 'Selected plan does not exist',
            'billing_cycle.required_with' => 'Billing cycle is required when selecting a plan',
            'billing_cycle.in' => 'Invalid billing cycle selected',
            'gateway.required' => 'Payment gateway is required',
            'gateway.in' => 'Invalid payment gateway selected'
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
