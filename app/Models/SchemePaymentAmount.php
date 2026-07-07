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
    ];
}
