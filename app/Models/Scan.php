<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Scan extends Model
{
    protected $table = 'scans';

    protected $fillable = [
        'website_id',
        'scan_date',
        'issues_found',
        'issues_resolved',
        'issues',
        'status',
        'wcag_version',
        'compliance_level',
        'standards_checked',
        'errors_count',
        'warnings_count',
        'notices_count',
        'pages_scanned',
        'pages_with_issues',
        'scan_type',
        'scanned_url',
        'scan_options',
        'completed_at',
        'scan_duration',
        'issue_categories',
        'wcag_violations',
        'compliance_status',
        'created_by',
        'modified_by'
    ];

    protected $casts = [
        'scan_date' => 'datetime',
        'completed_at' => 'datetime',
        'issues' => 'array',
        'standards_checked' => 'array',
        'scan_options' => 'array',
        'issue_categories' => 'array',
        'wcag_violations' => 'array',
        'compliance_status' => 'array',
        'issues_found' => 'integer',
        'issues_resolved' => 'integer',
        'errors_count' => 'integer',
        'warnings_count' => 'integer',
        'notices_count' => 'integer',
        'pages_scanned' => 'integer',
        'pages_with_issues' => 'integer',
        'scan_duration' => 'float'
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    protected static function boot()
    {

        parent::boot();

        // updating created_by and modified_by when model is created
        static::creating(function ($model) {
            if (!$model->isDirty('created_by')) {
                $model->created_by = Auth::user()->id;
            }
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::user()->id;
            }
        });

        // updating modified_by when model is updated
        static::updating(function ($model) {
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::user()->id;
            }
        });
    }
}
