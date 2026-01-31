<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseExtraction extends Model
{
    protected $fillable = [
        'رقم_الرخصة',
        'التاريخ',
        'السعر',
        'client_id',
        'vehicle_id',
        'الملاحظات',
    ];

    protected $casts = [
        'التاريخ' => 'date',
        'السعر' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
