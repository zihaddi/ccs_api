<?php

namespace App\Http\Requests\Admin\Portfolio;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PortfolioUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => 'sometimes|string|max:255|unique:portfolios,slug,' . $this->route('portfolio'),
            'cat_id' => ['required', 'integer', 'exists:portfolio_categories,id'],
            'description' => ['required', 'string'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'project_url' => ['nullable', 'string', 'url', 'max:255'],
            'completion_date' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'string'],
            'technologies' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
        ];
    }
}
