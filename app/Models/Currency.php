<?php

namespace App\Models;

use App\Constants\Constants;
use App\Http\Traits\HttpResponses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpFoundation\Response;

class Currency extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HttpResponses;

    protected $table = 'currencies';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        "id",
        "code",
        "name",
        "exchange_rate",
        "status",
        "created_by",
        "modified_by",
        "deleted_at"
    ];

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
        $query->orderBy('name');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
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

    function convertPrice($price, $currencyCode)
    {
        try {
            $currency = Currency::where('code', $currencyCode)->first();
            if (!$currency) {
                return $this->error(null, "Currency not supported", Response::HTTP_NOT_FOUND, false);
            } else {
                return $price * $currency->exchange_rate;
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
