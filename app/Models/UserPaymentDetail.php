<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPaymentDetail extends Model
{
    protected $table = 'user_payment_details';

    protected $fillable = [
        'user_id',
        'payment_id',
        'payment_amount',
        'payment_currency',
        'payment_description',
        'payment_status',
        'payment_method',
        'payment_token',
        'payment_type',
        'payment_response',
        'payment_response_code',
        'payment_response_message',
        'payment_response_status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
