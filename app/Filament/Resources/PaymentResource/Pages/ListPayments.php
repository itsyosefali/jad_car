<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('bulk_allocation')
                ->label('تخصيص دفعات للعملاء')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->url(PaymentResource::getUrl('bulk-allocation')),
            Actions\CreateAction::make(),
        ];
    }
}
