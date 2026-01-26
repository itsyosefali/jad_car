<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $fillable = [
        'الرقم_المرجعي',
        'نوع_المعاملة',
        'تاريخ_المعاملة',
        'تاريخ_الإدخال',
        'client_id',
        'vehicle_id',
        'السعر',
        'الحالة',
        'الملاحظات',
    ];

    protected $casts = [
        'تاريخ_المعاملة' => 'date',
        'تاريخ_الإدخال' => 'datetime',
        'السعر' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($transaction) {
            // منع تعديل الرقم المرجعي
            if ($transaction->isDirty('الرقم_المرجعي')) {
                $transaction->الرقم_المرجعي = $transaction->getOriginal('الرقم_المرجعي');
            }

            // منع تعديل السعر إذا كانت المعاملة مكتملة
            if ($transaction->isCompleted() && $transaction->isDirty('السعر')) {
                $transaction->السعر = $transaction->getOriginal('السعر');
            }
        });

        static::deleting(function ($transaction) {
            // منع حذف المعاملات المكتملة
            if ($transaction->isCompleted()) {
                throw new \Exception('لا يمكن حذف معاملة مكتملة.');
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function inspection(): HasOne
    {
        return $this->hasOne(Inspection::class);
    }

    public function isCompleted(): bool
    {
        return $this->الحالة === 'مكتملة';
    }

    public function isDraft(): bool
    {
        return $this->الحالة === 'مسودة';
    }

    public function isCancelled(): bool
    {
        return $this->الحالة === 'ملغاة';
    }
}
