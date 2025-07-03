<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TvProgram extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tv_programs';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'channel_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'broadcast_date',
        'broadcast_time',
        'duration',
        'type',
        'status',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'broadcast_date' => 'datetime',
        'broadcast_time' => 'datetime:H:i',
    ];

    public function getThumbnailAttribute($value)
    {
        if ($value && filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        return $value ? url(Storage::url($value)) : null;
    }

    public function channel()
    {
        return $this->belongsTo(TvChannel::class, 'channel_id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function modified_by()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }

    public function scopeOrderByName($query)
    {
        return $query->orderBy('title');
    }

    public function scopeOrderByBroadcastDate($query)
    {
        return $query->orderBy('broadcast_date', 'desc')->orderBy('broadcast_time', 'desc');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
            });
        })->when($filters['channel_id'] ?? null, function ($query, $channelId) {
            $query->where('channel_id', $channelId);
        })->when($filters['type'] ?? null, function ($query, $type) {
            $query->where('type', $type);
        })->when($filters['date'] ?? null, function ($query, $date) {
            $query->whereDate('broadcast_date', $date);
        })->when(isset($filters['status']) && $filters['status'] !== null, function ($query) use ($filters) {
            $query->where('status', '=', $filters['status']);
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

        static::creating(function ($model) {
            if (!$model->isDirty('created_by')) {
                $model->created_by = Auth::user()->id ?? null;
            }
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::user()->id ?? null;
            }
        });

        static::updating(function ($model) {
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::user()->id ?? null;
            }
        });
    }
}
