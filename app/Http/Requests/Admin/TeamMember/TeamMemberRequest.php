<?php

namespace App\Http\Requests\Admin\TeamMember;

use Illuminate\Foundation\Http\FormRequest;

class TeamMemberRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'image' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean'
        ];
    }
}
