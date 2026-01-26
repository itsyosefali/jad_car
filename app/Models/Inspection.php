<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inspection extends Model
{
    protected $fillable = [
        'transaction_id',
        'نوع_الإجراء',
        'النتيجة',
        'ملاحظات',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($inspection) {
            $transaction = $inspection->transaction;
            if ($transaction && !in_array($transaction->نوع_المعاملة, ['فحص', 'تجديد'])) {
                throw new \Exception('لا يمكن إنشاء فحص إلا لمعاملات من نوع "فحص" أو "تجديد".');
            }
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
