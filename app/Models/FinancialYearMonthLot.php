<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FinancialYearMonthLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheme_id',
        'financial_year',
        'month',
        'is_regular_lot',
        'is_arrear_lot',
    ];
}
