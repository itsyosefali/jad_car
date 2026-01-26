<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'transaction_id',
        'المبلغ',
        'طريقة_الدفع',
        'رقم_الإيصال',
        'تاريخ_الدفع',
    ];

    protected $casts = [
        'المبلغ' => 'decimal:2',
        'تاريخ_الدفع' => 'date',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
