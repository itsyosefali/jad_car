<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Eager load vehicle relationship to ensure it's available in infolist
        $this->record->load('vehicle');

        return $data;
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);
        
        // Ensure vehicle is loaded
        $this->record->load('vehicle');
    }

    /**
     * Check if this transaction is a registration transaction
     * Checks both transaction type field and service names
     */
    protected function isRegistrationTransaction(): bool
    {
        // Ensure items and services are loaded
        $this->record->load('items.service');

        // Check transaction type field
        if ($this->record->نوع_المعاملة === 'تسجيل') {
            return true;
        }

        // Check if any service contains "تسجيل" in its name
        return $this->record->items->contains(function ($item) {
            return $item->service && str_contains($item->service->اسم_الخدمة, 'تسجيل');
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('completeTransaction')
                ->label('إكمال المعاملة')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form(function () {
                    $fields = [];

                    // Check if license plate is required (تسجيل transaction with vehicle that has no license plate)
                    $needsLicensePlate = $this->isRegistrationTransaction()
                        && $this->record->vehicle
                        && empty($this->record->vehicle->رقم_اللوحة);

                    if ($needsLicensePlate) {
                        $fields[] = Forms\Components\TextInput::make('رقم_اللوحة')
                            ->label('رقم اللوحة')
                            ->required()
                            ->placeholder('أدخل رقم اللوحة')
                            ->maxLength(255)
                            ->helperText('يجب إدخال رقم اللوحة لإكمال معاملة التسجيل');
                    }

                    return $fields;
                })
                ->modalHeading('إكمال المعاملة')
                ->modalDescription(function () {
                    $needsLicensePlate = $this->isRegistrationTransaction()
                        && $this->record->vehicle
                        && empty($this->record->vehicle->رقم_اللوحة);

                    if ($needsLicensePlate) {
                        return 'يجب إدخال رقم اللوحة لإكمال هذه المعاملة. سيتم حفظ رقم اللوحة في بيانات المركبة.';
                    }

                    return 'هل أنت متأكد من إكمال هذه المعاملة؟ سيتم تغيير الحالة إلى "مكتملة" ولن يمكن تعديل السعر بعد ذلك.';
                })
                ->modalSubmitActionLabel('نعم، أكمل المعاملة')
                ->modalCancelActionLabel('إلغاء')
                ->visible(fn () => $this->record->isDraft())
                ->action(function (array $data) {
                    // Check if license plate is required
                    $needsLicensePlate = $this->isRegistrationTransaction()
                        && $this->record->vehicle
                        && empty($this->record->vehicle->رقم_اللوحة);

                    // Validate license plate if required
                    if ($needsLicensePlate && empty($data['رقم_اللوحة'])) {
                        throw new \Filament\Support\Exceptions\Halt;
                    }

                    // Update transaction status
                    $this->record->update([
                        'الحالة' => 'مكتملة',
                    ]);

                    // Update vehicle license plate if provided
                    if ($needsLicensePlate && ! empty($data['رقم_اللوحة'])) {
                        $this->record->vehicle->update([
                            'رقم_اللوحة' => $data['رقم_اللوحة'],
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('تم إكمال المعاملة بنجاح')
                            ->body('تم حفظ رقم اللوحة: '.$data['رقم_اللوحة'])
                            ->success()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('تم إكمال المعاملة بنجاح')
                            ->success()
                            ->send();
                    }
                }),
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
            Actions\EditAction::make(),
        ];
    }
}
