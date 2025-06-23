<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanPrice extends Model
{
    use HasFactory;

    protected $table = 'plan_prices';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        "plan_id",
        "billing_cycle",
        "price",
        "discount",
        "final_price",
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
