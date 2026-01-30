<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Services\ReferenceNumberService;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['الرقم_المرجعي'] = app(ReferenceNumberService::class)->generateNextReferenceNumber();
        $data['تاريخ_الإدخال'] = now();

        // Calculate total from items if items exist (using selling prices from services)
        if (isset($data['items']) && is_array($data['items'])) {
            $total = 0;
            foreach ($data['items'] as $item) {
                if (isset($item['service_id']) && isset($item['الكمية'])) {
                    $service = \App\Models\Service::find($item['service_id']);
                    if ($service) {
                        $total += ($service->سعر_البيع * $item['الكمية']);
                    }
                }
            }
            $data['السعر'] = $total;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Recalculate total from items after creation
        $this->record->recalculateTotal();
    }
}
