<?php

namespace App\Filament\Resources\InsuranceResource\Pages;

use App\Filament\Resources\InsuranceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInsurance extends ViewRecord
{
    protected static string $resource = InsuranceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
