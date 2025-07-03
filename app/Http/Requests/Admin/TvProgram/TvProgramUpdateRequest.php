<?php

namespace App\Http\Requests\Admin\TvProgram;

use Illuminate\Foundation\Http\FormRequest;

class TvProgramUpdateRequest extends FormRequest
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
            'slug' => 'required|string|max:255|unique:tv_programs,slug,' . $this->route('id'),
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|string',
            'broadcast_date' => 'required|date',
            'broadcast_time' => 'required|date_format:H:i',
            'duration' => 'nullable|integer|min:1',
            'type' => 'required|in:live,recorded,special',
            'status' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'channel_id.required' => 'The channel field is required.',
            'channel_id.exists' => 'The selected channel is invalid.',
            'title.required' => 'The title field is required.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'slug.required' => 'The slug field is required.',
            'slug.unique' => 'The slug has already been taken.',
            'slug.max' => 'The slug may not be greater than 255 characters.',
            'broadcast_date.required' => 'The broadcast date field is required.',
            'broadcast_date.date' => 'The broadcast date must be a valid date.',
            'broadcast_time.required' => 'The broadcast time field is required.',
            'broadcast_time.date_format' => 'The broadcast time must be in HH:MM format.',
            'duration.integer' => 'The duration must be a number.',
            'duration.min' => 'The duration must be at least 1 minute.',
            'type.required' => 'The type field is required.',
            'type.in' => 'The selected type is invalid.',
            'status.boolean' => 'The status field must be true or false.',
        ];
    }
}
