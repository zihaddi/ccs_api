<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Partner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'short_desc',
        'logo',
        'status',
        'created_by',
        'modified_by'
    ];

    public function getLogoAttribute($value)
    {
        if ($value && filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        return $value ? url(Storage::url($value)) : null;
    }
    public function features()
    {
        return $this->hasMany(Feature::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function scopeFilter($query, $params)
    {
        if (isset($params['search'])) {
            $query->where('name', 'like', '%' . $params['search'] . '%')
                  ->orWhere('short_desc', 'like', '%' . $params['search'] . '%');
        }

        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }

        return $query;
    }
}
