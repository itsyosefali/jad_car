<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\TreasuryOverviewWidget;
use Filament\Pages\Page;

class Treasury extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static string $view = 'filament.pages.treasury';

    protected static ?string $navigationLabel = 'الخزينة';

    protected static ?string $title = 'الخزينة';

    protected static ?string $navigationGroup = 'المالية';

    protected static ?int $navigationSort = 4;

    protected function getHeaderWidgets(): array
    {
        return [
            TreasuryOverviewWidget::class,
        ];
    }

    protected function getWidgets(): array
    {
        return [];
    }
}
