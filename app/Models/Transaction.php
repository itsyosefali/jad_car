<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get total amount paid across all payments
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->sum('المبلغ');
    }

    /**
     * Get remaining amount to be paid
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->السعر - $this->total_paid);
    }

    /**
     * Get payment status: 'paid', 'partial', or 'unpaid'
     */
    public function getPaymentStatusAttribute(): string
    {
        $totalPaid = $this->total_paid;
        if ($totalPaid == 0) {
            return 'unpaid';
        }
        if ($totalPaid >= $this->السعر) {
            return 'paid';
        }

        return 'partial';
    }

    /**
     * Check if transaction is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if transaction has any payment
     */
    public function hasPayment(): bool
    {
        return $this->payments()->exists();
    }

    public function inspection(): HasOne
    {
        return $this->hasOne(Inspection::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Recalculate and update the transaction total from items
     * Uses selling price from service, or cost if no service
     */
    public function recalculateTotal(): void
    {
        $total = $this->items()->get()->sum(function ($item) {
            // Use selling price from service if available, otherwise use cost
            $price = $item->selling_price;

            return $price * $item->الكمية;
        });

        // Only update if the total has changed to avoid infinite loops
        // Use withoutEvents to prevent triggering model events
        if (abs($this->السعر - $total) > 0.01) {
            static::withoutEvents(function () use ($total) {
                $this->updateQuietly(['السعر' => $total]);
            });
        }
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
