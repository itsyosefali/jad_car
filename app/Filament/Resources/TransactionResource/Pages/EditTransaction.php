<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load items for the repeater with service data
        $data['items'] = $this->record->items->map(function ($item) {
            return [
                'service_id' => $item->service_id,
                'اسم_الخدمة' => $item->service_name,
                'التكلفة' => $item->cost,
                'الكمية' => $item->الكمية,
                'الملاحظات' => $item->الملاحظات,
            ];
        })->toArray();

        // Load inspection رقم_الوثيقة if exists
        if ($this->record->inspection) {
            $data['inspection_رقم_الوثيقة'] = $this->record->inspection->رقم_الوثيقة;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('printReceipt')
                ->label('طباعة الإيصال')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->visible(fn () => $this->record->isCompleted())
                ->action(function () {
                    $transaction = $this->record->load(['client', 'vehicle', 'payments', 'inspection', 'items.service']);

                    // استخدام ar-php لتحسين عرض النصوص العربية
                    $Arabic = new Arabic;

                    // تحويل النصوص العربية إلى صيغة صحيحة
                    $viewData = [
                        'transaction' => $transaction,
                        'Arabic' => $Arabic,
                    ];

                    $pdf = Pdf::loadView('receipts.transaction-receipt', $viewData)
                        ->setPaper('a4', 'portrait')
                        ->setOption('isHtml5ParserEnabled', true)
                        ->setOption('isRemoteEnabled', true);

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->stream();
                    }, 'receipt-'.$transaction->الرقم_المرجعي.'.pdf');
                }),
            Actions\DeleteAction::make()
                ->disabled(fn () => $this->record->isCompleted()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // لا يمكن تعديل الرقم المرجعي نهائياً
        if (isset($data['الرقم_المرجعي'])) {
            $data['الرقم_المرجعي'] = $this->record->الرقم_المرجعي;
        }

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

        // لا يمكن تعديل السعر إذا كانت المعاملة مكتملة
        if ($this->record->isCompleted() && isset($data['السعر'])) {
            $data['السعر'] = $this->record->السعر;
        }

        // Store inspection رقم_الوثيقة for afterSave
        $this->inspection_رقم_الوثيقة = $data['inspection_رقم_الوثيقة'] ?? null;
        unset($data['inspection_رقم_الوثيقة']); // Remove from data to prevent saving to transaction

        return $data;
    }

    protected $inspection_رقم_الوثيقة = null;

    protected function afterSave(): void
    {
        // Recalculate total from items after save
        if (! $this->record->isCompleted()) {
            $this->record->recalculateTotal();
        }

        // Handle inspection رقم_الوثيقة
        $رقم_الوثيقة = $this->inspection_رقم_الوثيقة;
        
        if ($رقم_الوثيقة !== null) {
            if ($this->record->inspection) {
                // Update existing inspection
                $this->record->inspection->update(['رقم_الوثيقة' => $رقم_الوثيقة]);
            } elseif (!empty($رقم_الوثيقة)) {
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

}
