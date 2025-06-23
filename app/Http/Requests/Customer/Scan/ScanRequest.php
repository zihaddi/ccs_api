<?php

namespace App\Http\Requests\Customer\Scan;

use App\Http\Traits\HttpResponses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Constants\ValidationConstants;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class ScanRequest extends FormRequest
{
    use HttpResponses;
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => 'required|url',
            'website_id' => 'required|integer|exists:websites,id',
            'scan_entire_site' => 'boolean',
            'max_pages' => 'integer|min:1|max:100',
            'wcag_version' => ['string', Rule::in(['2.0', '2.1', '2.2'])],
            'compliance_level' => ['string', Rule::in(['A', 'AA', 'AAA'])],
            'standards' => ['array', Rule::in(['wcag', 'ada', 'section508', 'aoda', 'en301549'])],
            'options' => 'array',
            'options.exclude_paths' => 'array|nullable',
            'options.include_paths' => 'array|nullable',
            'options.follow_redirects' => 'boolean|nullable',
            'options.check_subdomains' => 'boolean|nullable',
            'options.concurrent_requests' => 'integer|min:1|max:5|nullable',
            'options.request_delay' => 'integer|min:0|max:5000|nullable'
        ];
    }

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
