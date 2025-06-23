<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSubscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'subscription_id';

    protected $fillable = [
        'user_id',
        'plan_id',
        'subscription_type',
        'start_date',
        'end_date',
        'status',
        'next_billing_date',
        'amount',
        'currency',
        'auto_renew'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'next_billing_date' => 'datetime',
        'auto_renew' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function billingRecords()
    {
        return $this->hasMany(BillingRecord::class, 'subscription_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePendingBilling($query)
    {
        return $query->where('next_billing_date', '<=', now()->addDays(7))
                    ->where('status', 'active');
    }

    public function scopeNearingExpiration($query)
    {
        return $query->where('end_date', '<=', now()->addDays(7))
                    ->where('status', 'active')
                    ->where('auto_renew', false);
    }
}
