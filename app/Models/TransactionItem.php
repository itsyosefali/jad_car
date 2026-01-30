<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id',
        'service_id',
        'اسم_الخدمة',
        'التكلفة',
        'الكمية',
        'الملاحظات',
    ];

    protected $casts = [
        'التكلفة' => 'decimal:2',
        'الكمية' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // Recalculate transaction total when item is created, updated, or deleted
        static::saved(function ($item) {
            $item->transaction->recalculateTotal();
        });

        static::deleted(function ($item) {
            $item->transaction->recalculateTotal();
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get service name - from service if available, otherwise from manual entry
     */
    public function getServiceNameAttribute(): string
    {
        return $this->service ? $this->service->اسم_الخدمة : ($this->اسم_الخدمة ?? '');
    }

    /**
     * Get cost - from service if available, otherwise from manual entry
     */
    public function getCostAttribute(): float
    {
        return $this->service ? $this->service->التكلفة : ($this->التكلفة ?? 0);
    }

    /**
     * Get selling price - from service if available, otherwise use cost
     */
    public function getSellingPriceAttribute(): float
    {
        return $this->service ? $this->service->سعر_البيع : ($this->التكلفة ?? 0);
    }

    /**
     * Calculate the total for this item (selling price × quantity)
     */
    public function getTotalAttribute(): float
    {
        $sellingPrice = $this->selling_price;

        return $sellingPrice * $this->الكمية;
    }
}
