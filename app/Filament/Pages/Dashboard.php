<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\TransactionTypeChart;
use App\Filament\Widgets\TreasuryBalanceWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'لوحة التحكم';

    protected static ?string $title = 'لوحة التحكم';

    protected static ?int $navigationSort = 1;

    protected function getHeaderWidgets(): array
    {
        return [
            DashboardStats::class,
            TransactionTypeChart::class,
            TreasuryBalanceWidget::class,
        ];
    }

    // منع ظهور الـ widgets المكتشفة تلقائياً - نريد فقط الـ widgets المحددة في getHeaderWidgets()
    public function getWidgets(): array
    {
        return [];
    }
}
