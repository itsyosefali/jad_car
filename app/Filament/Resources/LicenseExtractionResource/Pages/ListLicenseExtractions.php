<?php

namespace App\Filament\Resources\LicenseExtractionResource\Pages;

use App\Filament\Resources\LicenseExtractionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLicenseExtractions extends ListRecords
{
    protected static string $resource = LicenseExtractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
