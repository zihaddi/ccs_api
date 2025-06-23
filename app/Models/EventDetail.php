<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'event_details';

    protected $fillable = [
        'event_id',
        'year_id',
        'venue',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'modified_by',
        'deleted_at',
    ];

    

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function modified_by()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('venue', 'like', '%' . $search . '%');
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
}
