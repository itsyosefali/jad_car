<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use ArPHP\I18N\Arabic;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('printReceipt')
                ->label('طباعة الإيصال')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->visible(fn () => $this->record->isCompleted())
                ->action(function () {
                    $transaction = $this->record->load(['client', 'vehicle', 'payment', 'inspection']);
                    
                    // استخدام ar-php لتحسين عرض النصوص العربية
                    $Arabic = new Arabic();
                    
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
                    }, 'receipt-' . $transaction->الرقم_المرجعي . '.pdf');
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
        
        // لا يمكن تعديل السعر إذا كانت المعاملة مكتملة
        if ($this->record->isCompleted() && isset($data['السعر'])) {
            $data['السعر'] = $this->record->السعر;
        }

        return $data;
    }
}
