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

        // Store inspection رقم_الوثيقة for afterCreate
        $this->inspection_رقم_الوثيقة = $data['inspection_رقم_الوثيقة'] ?? null;
        unset($data['inspection_رقم_الوثيقة']); // Remove from data to prevent saving to transaction

        return $data;
    }

    protected $inspection_رقم_الوثيقة = null;

    protected function afterCreate(): void
    {
        // Recalculate total from items after creation
        $this->record->recalculateTotal();

        // Handle inspection رقم_الوثيقة
        $رقم_الوثيقة = $this->inspection_رقم_الوثيقة;
        
        if (!empty($رقم_الوثيقة)) {
            // Create new inspection if رقم_الوثيقة is provided
            // Only create if transaction type is فحص or تجديد
            if (in_array($this->record->نوع_المعاملة, ['فحص', 'تجديد'])) {
                $this->record->inspection()->create([
                    'رقم_الوثيقة' => $رقم_الوثيقة,
                    'نوع_الإجراء' => $this->record->نوع_المعاملة === 'فحص' ? 'فحص' : 'تجديد',
                    'النتيجة' => 'صالح', // Default value
                ]);
            }
        }
    }
}
