<?php

namespace App\Http\Requests\Admin\News;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class NewsUpdateRequest extends FormRequest
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

            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:news,slug,' . ($this->route('news') ? $this->route('news') : 'NULL') . ',id', // Ensure unique slug, except for the current record when updating
            'cat_id' => 'required|integer|exists:news_categories,id', // Assuming it's a reference to the categories table
            'news_dtl' => 'nullable|string', // Assuming news_dtl is a string or text field
            'is_external' => 'required|boolean',
            'external_url' => 'nullable|url|max:255|required_if:is_external,true', // Only required if is_external is true
            'photo' => 'nullable', // Assuming photo is an image file
            'status' => 'required|boolean',
            'on_headline' => 'required|boolean',
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
