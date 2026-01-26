<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Services\ReferenceNumberService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['الرقم_المرجعي'] = app(ReferenceNumberService::class)->generateNextReferenceNumber();
        $data['تاريخ_الإدخال'] = now();

        return $data;
    }
}
