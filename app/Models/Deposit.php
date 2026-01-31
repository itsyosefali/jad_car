<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = [
        'المبلغ',
        'الفئة',
        'التاريخ',
        'الوصف',
        'الملاحظات',
    ];

    protected $casts = [
        'المبلغ' => 'decimal:2',
        'التاريخ' => 'date',
    ];
}
