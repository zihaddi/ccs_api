<?php

namespace App\Http\Resources\Cms\Scan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'website_id' => $this->website_id,
            'scan_date' => $this->scan_date,
            'issues_found' => $this->issues_found,
            'issues_resolved' => $this->issues_resolved,
            'issues' => $this->issues,
            'status' => $this->status,
            'wcag_version' => $this->wcag_version,
            'compliance_level' => $this->compliance_level,
            'standards_checked' => $this->standards_checked,
            'errors_count' => $this->errors_count,
            'warnings_count' => $this->warnings_count,
            'notices_count' => $this->notices_count,
            'pages_scanned' => $this->pages_scanned,
            'pages_with_issues' => $this->pages_with_issues,
            'scan_type' => $this->scan_type,
            'scanned_url' => $this->scanned_url,
            'scan_duration' => $this->scan_duration,
            'issue_categories' => $this->issue_categories,
            'wcag_violations' => $this->wcag_violations,
            'compliance_status' => $this->compliance_status,
            'scan_options' => $this->scan_options,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'modified_by' => $this->modified_by,
            'website' => $this->whenLoaded('website'),
            'creator' => $this->whenLoaded('creator'),
            'modifier' => $this->whenLoaded('modifier'),
        ];
    }
}
