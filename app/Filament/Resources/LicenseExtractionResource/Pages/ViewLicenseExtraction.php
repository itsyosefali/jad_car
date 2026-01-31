<?php

namespace App\Filament\Resources\LicenseExtractionResource\Pages;

use App\Filament\Resources\LicenseExtractionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLicenseExtraction extends ViewRecord
{
    protected static string $resource = LicenseExtractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
