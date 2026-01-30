<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'اسم_الخدمة',
        'التكلفة',
        'سعر_البيع',
        'الوصف',
        'نشط',
    ];

    protected $casts = [
        'التكلفة' => 'decimal:2',
        'سعر_البيع' => 'decimal:2',
        'نشط' => 'boolean',
    ];

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Calculate profit margin (سعر_البيع - التكلفة)
     */
    public function getProfitAttribute(): float
    {
        return $this->سعر_البيع - $this->التكلفة;
    }

    /**
     * Calculate profit margin percentage
     */
    public function getProfitPercentageAttribute(): float
    {
        if ($this->التكلفة == 0) {
            return 0;
        }

        return (($this->سعر_البيع - $this->التكلفة) / $this->التكلفة) * 100;
    }
}
