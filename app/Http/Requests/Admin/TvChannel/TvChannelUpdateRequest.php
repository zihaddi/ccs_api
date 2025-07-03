<?php


namespace App\Http\Requests\Admin\TvChannel;

use Illuminate\Foundation\Http\FormRequest;

class TvChannelUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tv_channels,slug,' . $this->route('tv_channel'),
            'logo' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'boolean',
        ];
    }
}
