<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class EventCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'event_categories';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'title',
        'parent_id',
        'slug',
        'status',
        'created_by',
        'modified_by',
        'deleted_at',
    ];

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function modified_by()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(EventCategory::class, 'parent_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(EventCategory::class, 'parent_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'category_id');
    }

    public function scopeOrderByName($query)
    {
        $query->orderBy('title');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            });
        })->when(isset($filters['status']) && $filters['status'] !== null, function ($query) use ($filters) {
            $query->where('status', '=', strtoupper($filters['status']));
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
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
