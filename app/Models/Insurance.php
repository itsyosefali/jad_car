<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insurance extends Model
{
    protected $fillable = [
        'رقم_الوثيقة',
        'التاريخ',
        'كم_سنة',
        'تاريخ_النهاية',
        'تكلفة_الوثيقة',
        'client_id',
        'vehicle_id',
        'الملاحظات',
    ];

    protected $casts = [
        'التاريخ' => 'date',
        'كم_سنة' => 'integer',
        'تاريخ_النهاية' => 'date',
        'تكلفة_الوثيقة' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($insurance) {
            // Auto-calculate end date if start date and duration are set
            if ($insurance->التاريخ && $insurance->كم_سنة) {
                $insurance->تاريخ_النهاية = Carbon::parse($insurance->التاريخ)->addYears($insurance->كم_سنة);
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

    /**
     * Check if insurance is expired
     */
    public function isExpired(): bool
    {
        return $this->تاريخ_النهاية && Carbon::parse($this->تاريخ_النهاية)->isPast();
    }

    /**
     * Check if insurance is active (not expired)
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }
}
