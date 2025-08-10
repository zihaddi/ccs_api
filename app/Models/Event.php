<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class Event extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'events';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'description',
        'status',
        'photo',
        'event_at',
        'created_by',
        'modified_by',
        'deleted_at',
    ];

    protected $casts = [
        'event_at' => 'datetime',
        'status' => 'boolean',
    ];

    public function getPhotoAttribute($value)
    {
        if ($value && filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        return $value ? url(Storage::url($value)) : null;
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function modified_by()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(EventDetail::class);
    }

    public function scopeOrderByName($query)
    {
        $query->orderBy('title');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        })->when(isset($filters['status']) && $filters['status'] !== null, function ($query) use ($filters) {
            $query->where('status', '=', strtoupper($filters['status']));
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        })->when($filters['event_at_from'] ?? null, function ($query, $from) {
            $query->where('event_at', '>=', $from);
        })->when($filters['event_at_to'] ?? null, function ($query, $to) {
            $query->where('event_at', '<=', $to);
        });
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_at', '>=', now())
                    ->where('status', true)
                    ->orderBy('event_at', 'asc');
    }

    public function scopeCompleted($query)
    {
        return $query->where('event_at', '<', now())
                    ->where('status', true)
                    ->orderBy('event_at', 'desc');
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
