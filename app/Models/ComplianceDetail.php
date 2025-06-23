<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplianceDetail extends Model
{
    use HasFactory;

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        "compliance_id",
        "title",
        "details",
        "price",
        "status",
    ];
    public function compliance()
    {
        return $this->belongsTo(Compliance::class);
    }
}
