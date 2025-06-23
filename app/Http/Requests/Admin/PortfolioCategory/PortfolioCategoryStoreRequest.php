<?php

namespace App\Http\Requests\Admin\PortfolioCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PortfolioCategoryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:portfolio_categories,slug'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
        ];
    }
}
