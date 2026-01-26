<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class DailyReportStats extends BaseWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $today = now()->startOfDay();
        
        $todayCount = Transaction::whereDate('تاريخ_المعاملة', $today->toDateString())
            ->where('الحالة', 'مكتملة')
            ->count();
        
        $todayRevenue = Transaction::whereDate('تاريخ_المعاملة', $today->toDateString())
            ->where('الحالة', 'مكتملة')
            ->sum('السعر');

        $byType = Transaction::whereDate('تاريخ_المعاملة', $today->toDateString())
            ->where('الحالة', 'مكتملة')
            ->selectRaw('نوع_المعاملة, COUNT(*) as count, SUM(السعر) as total')
            ->groupBy('نوع_المعاملة')
            ->get();

        $stats = [
            Stat::make('عدد المعاملات', $todayCount)
                ->description('معاملات مكتملة اليوم')
                ->color('primary'),
            Stat::make('إجمالي الإيرادات', Number::format($todayRevenue, 2) . ' LYD')
                ->description('إجمالي الإيرادات اليوم')
                ->color('success'),
        ];

        // إضافة إحصائيات حسب نوع المعاملة
        foreach ($byType as $type) {
            $stats[] = Stat::make($type->نوع_المعاملة, $type->count)
                ->description(Number::format($type->total, 2) . ' LYD')
                ->color('info');
        }

        return $stats;
    }
}
