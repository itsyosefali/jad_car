<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'رقم_اللوحة',
        'نوع_المركبة',
        'رقم_الهيكل',
        'الصنف',
        'اللون',
        'سنة_الصنع',
        'ملاحظات',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
