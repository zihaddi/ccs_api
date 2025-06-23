<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feature extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'status',
        'created_by',
        'modified_by'
    ];

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
                  ->orWhere('description', 'like', '%' . $params['search'] . '%');
        }

        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }

        return $query;
    }
}
