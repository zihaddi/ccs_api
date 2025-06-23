<?php

namespace App\Http\Requests\Admin\PortfolioCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PortfolioCategoryUpdateRequest extends FormRequest
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
            'slug' => 'sometimes|string|max:255|unique:portfolio_categories,slug,' . $this->route('portfolio_category'),
            'description' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
        ];
    }
}
