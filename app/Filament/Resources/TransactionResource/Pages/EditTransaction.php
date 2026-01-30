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

        return $data;
    }

    protected function afterSave(): void
    {
        // Recalculate total from items after save
        if (! $this->record->isCompleted()) {
            $this->record->recalculateTotal();
        }
    }
}
