<?php

namespace App\Http\Requests\Admin\TvProgram;

use Illuminate\Foundation\Http\FormRequest;

class TvProgramStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel_id' => 'required|exists:tv_channels,id',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tv_programs,slug',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|string',
            'broadcast_date' => 'required|date',
            'broadcast_time' => 'required|date_format:H:i',
            'duration' => 'nullable|integer|min:1',
            'type' => 'required|in:live,recorded,special',
            'status' => 'boolean',
        ];
    }
}
