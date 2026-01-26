<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Number;
use ArPHP\I18N\Arabic;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('completeTransaction')
                ->label('إكمال المعاملة')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('إكمال المعاملة')
                ->modalDescription('هل أنت متأكد من إكمال هذه المعاملة؟ سيتم تغيير الحالة إلى "مكتملة" ولن يمكن تعديل السعر بعد ذلك.')
                ->modalSubmitActionLabel('نعم، أكمل المعاملة')
                ->modalCancelActionLabel('إلغاء')
                ->visible(fn () => $this->record->isDraft())
                ->action(function () {
                    $this->record->update([
                        'الحالة' => 'مكتملة',
                    ]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('تم إكمال المعاملة بنجاح')
                        ->success()
                        ->send();
                }),
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
            Actions\EditAction::make(),
        ];
    }
}
