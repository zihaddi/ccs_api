<?php

namespace App\Http\Requests\Cms\News;

use Illuminate\Foundation\Http\FormRequest;

class NewsRequest extends FormRequest
{
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
            'slug' => 'required|string|max:255|unique:news,slug,' . ($this->route('news') ? $this->route('news') : 'NULL') . ',id',
            'cat_id' => 'required|integer|exists:news_categories,id',
            'news_dtl' => 'nullable|string',
            'is_external' => 'required|boolean',
            'external_url' => 'nullable|url|max:255|required_if:is_external,true',
            'photo' => 'nullable',
            'status' => 'required|boolean',
            'on_headline' => 'required|boolean',
        ];
    }
}
