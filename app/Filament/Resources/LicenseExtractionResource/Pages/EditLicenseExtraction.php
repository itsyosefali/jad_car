<?php

namespace App\Filament\Resources\LicenseExtractionResource\Pages;

use App\Filament\Resources\LicenseExtractionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLicenseExtraction extends EditRecord
{
    protected static string $resource = LicenseExtractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
