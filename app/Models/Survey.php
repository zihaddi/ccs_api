<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Survey extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'question',
        'question_bn',
        'status',
        'start_date',
        'end_date',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true)
                    ->where(function ($q) {
                        $q->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
    }

    public function getResponseCounts()
    {
        return $this->responses()
                   ->selectRaw('response, COUNT(*) as count')
                   ->groupBy('response')
                   ->pluck('count', 'response')
                   ->toArray();
    }

    public function getTotalVotes()
    {
        return $this->responses()->count();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->modified_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->modified_by = Auth::id();
            }
        });
    }
}
