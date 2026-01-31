<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class DashboardStats extends BaseWidget
{
    public static function canView(): bool
    {
        // Only show when explicitly added to a page, not auto-discovered
        return false;
    }

    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        // إحصائيات اليوم - استخدام whereDate مع string لتجنب التكرار
        $todayTotal = Transaction::whereDate('تاريخ_المعاملة', $today->toDateString())
            ->count();
        $todayCompleted = Transaction::whereDate('تاريخ_المعاملة', $today->toDateString())
            ->where('الحالة', 'مكتملة')
            ->count();
        $todayPending = Transaction::whereDate('تاريخ_المعاملة', $today->toDateString())
            ->where('الحالة', 'مسودة')
            ->count();
        $todayRevenue = Transaction::whereDate('تاريخ_المعاملة', $today->toDateString())
            ->where('الحالة', 'مكتملة')
            ->sum('السعر');

        // إحصائيات الشهر - استخدام whereBetween لتجنب التكرار
        $monthEnd = now()->endOfMonth();
        $monthRevenue = Transaction::whereBetween('تاريخ_المعاملة', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->where('الحالة', 'مكتملة')
            ->sum('السعر');

        return [
            Stat::make('المعاملات اليوم', $todayTotal)
                ->description('إجمالي المعاملات اليوم')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
            Stat::make('المعاملات المكتملة', $todayCompleted)
                ->description('معاملات مكتملة اليوم')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('المعاملات المعلقة', $todayPending)
                ->description('معاملات مسودة اليوم')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('إيرادات اليوم', Number::format($todayRevenue, 2).' LYD')
                ->description('إجمالي الإيرادات اليوم')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('إيرادات الشهر', Number::format($monthRevenue, 2).' LYD')
                ->description('إجمالي الإيرادات الشهرية')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
        ];
    }
}
