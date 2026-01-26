<?php

namespace App\Services;

use App\Models\ReferenceSequence;
use Carbon\Carbon;

class ReferenceNumberService
{
    public function generateNextReferenceNumber(): string
    {
        $today = Carbon::today();
        $dateString = $today->format('Ymd');

        $sequence = ReferenceSequence::firstOrCreate(
            ['date' => $today],
            ['sequence' => 0]
        );

        $sequence->increment('sequence');
        $sequence->refresh();

        $sequenceNumber = str_pad($sequence->sequence, 4, '0', STR_PAD_LEFT);

        return "INS-{$dateString}-{$sequenceNumber}";
    }
}
