<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'billing_id';

    protected $fillable = [
        'subscription_id',
        'bill_amount',
        'bill_date',
        'status',
        'payment_due_date',
        'paid_at',
        'payment_transaction_id'
    ];

    protected $casts = [
        'bill_date' => 'datetime',
        'payment_due_date' => 'datetime',
        'paid_at' => 'datetime'
    ];

    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id');
    }

    public function paymentTransaction()
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id', 'payment_intent_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                    ->where('payment_due_date', '<', now());
    }
}
