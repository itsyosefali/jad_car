<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceSequence extends Model
{
    protected $fillable = [
        'date',
        'sequence',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
