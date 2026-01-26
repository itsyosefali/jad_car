<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class MonthlyReportStats extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // Get the parent Livewire component (the page) to access selectedMonth
        $month = now()->format('Y-m');
        
        // Try to get the owner using getCachedOwner (Livewire method)
        try {
            if (method_exists($this, 'getCachedOwner')) {
                $owner = $this->getCachedOwner();
                if ($owner && property_exists($owner, 'selectedMonth') && $owner->selectedMonth) {
                    $month = $owner->selectedMonth;
                }
            } elseif (property_exists($this, 'owner') && $this->owner) {
                if (property_exists($this->owner, 'selectedMonth') && $this->owner->selectedMonth) {
                    $month = $this->owner->selectedMonth;
                }
            }
        } catch (\Exception $e) {
            // Fallback to current month if owner is not available
        }
        
        $date = Carbon::parse($month . '-01');
        
        $monthCount = Transaction::whereYear('تاريخ_المعاملة', $date->year)
            ->whereMonth('تاريخ_المعاملة', $date->month)
            ->where('الحالة', 'مكتملة')
            ->count();
        
        $monthRevenue = Transaction::whereYear('تاريخ_المعاملة', $date->year)
            ->whereMonth('تاريخ_المعاملة', $date->month)
            ->where('الحالة', 'مكتملة')
            ->sum('السعر');

        $byType = Transaction::whereYear('تاريخ_المعاملة', $date->year)
            ->whereMonth('تاريخ_المعاملة', $date->month)
            ->where('الحالة', 'مكتملة')
            ->selectRaw('نوع_المعاملة, COUNT(*) as count, SUM(السعر) as total')
            ->groupBy('نوع_المعاملة')
            ->get();

        $stats = [
            Stat::make('عدد المعاملات', $monthCount)
                ->description("معاملات مكتملة في {$date->translatedFormat('F Y')}")
                ->color('primary'),
            Stat::make('إجمالي الإيرادات', Number::format($monthRevenue, 2) . ' LYD')
                ->description("إجمالي الإيرادات للشهر")
                ->color('success'),
        ];

        foreach ($byType as $type) {
            $stats[] = Stat::make($type->نوع_المعاملة, $type->count)
                ->description(Number::format($type->total, 2) . ' LYD')
                ->color('info');
        }

        return $stats;
    }
}
