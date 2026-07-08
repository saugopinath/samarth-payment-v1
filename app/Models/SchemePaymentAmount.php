<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchemePaymentAmount extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheme_id',
        'financial_year',
        'january_amount',
        'february_amount',
        'march_amount',
        'april_amount',
        'may_amount',
        'june_amount',
        'july_amount',
        'august_amount',
        'september_amount',
        'october_amount',
        'november_amount',
        'december_amount',
        'january_payment_mode',
        'february_payment_mode',
        'march_payment_mode',
        'april_payment_mode',
        'may_payment_mode',
        'june_payment_mode',
        'july_payment_mode',
        'august_payment_mode',
        'september_payment_mode',
        'october_payment_mode',
        'november_payment_mode',
        'december_payment_mode',
    ];
}
