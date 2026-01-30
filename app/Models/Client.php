<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'الاسم_الكامل',
        'الرقم_الوطني',
        'رقم_الهاتف',
        'الملاحظات',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get total amount owed (sum of all completed transaction prices)
     */
    public function getTotalOwedAttribute(): float
    {
        return $this->transactions()
            ->where('الحالة', 'مكتملة')
            ->sum('السعر');
    }

    /**
     * Get total amount paid across all transactions
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->transactions()
            ->where('الحالة', 'مكتملة')
            ->with('payments')
            ->get()
            ->sum(function ($transaction) {
                return $transaction->payments->sum('المبلغ');
            });
    }

    /**
     * Get balance (total owed - total paid)
     */
    public function getBalanceAttribute(): float
    {
        return $this->total_owed - $this->total_paid;
    }

    /**
     * Check if client has unpaid transactions
     */
    public function hasUnpaidTransactions(): bool
    {
        return $this->getUnpaidTransactions()->isNotEmpty();
    }

    /**
     * Get unpaid transactions (where payment is less than transaction price)
     */
    public function getUnpaidTransactions()
    {
        return $this->transactions()
            ->where('الحالة', 'مكتملة')
            ->with('payments')
            ->get()
            ->filter(function ($transaction) {
                return $transaction->total_paid < $transaction->السعر;
            });
    }
}
