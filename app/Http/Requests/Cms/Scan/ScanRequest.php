<?php

namespace App\Http\Requests\Cms\Scan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'website_id' => 'required|exists:websites,id',
            'scan_type' => 'required|string|in:single,site',
            'wcag_version' => 'required|string|in:2.0,2.1,2.2',
            'compliance_level' => 'required|string|in:A,AA,AAA',
            'standards_checked' => 'required|array',
            'standards_checked.*' => Rule::in(['wcag', 'ada', 'section508', 'aoda', 'en301549']),
            'scan_options' => 'required|array',
            'scan_options.exclude_paths' => 'array|nullable',
            'scan_options.include_paths' => 'array|nullable',
            'scan_options.max_pages' => 'integer|min:1|max:500|nullable',
            'scan_options.follow_redirects' => 'boolean|nullable',
            'scan_options.check_subdomains' => 'boolean|nullable',
            'scan_options.concurrent_requests' => 'integer|min:1|max:10|nullable',
            'scan_options.request_delay' => 'integer|min:0|max:10000|nullable',
            'status' => 'required|string|in:pending,in_progress,completed,failed',
            'issues_found' => 'integer|min:0',
            'issues_resolved' => 'integer|min:0',
            'created_by' => 'exists:users,id',
            'modified_by' => 'exists:users,id'
        ];
    }
}
