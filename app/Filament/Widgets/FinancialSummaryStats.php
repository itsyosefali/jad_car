<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class FinancialSummaryStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Get date range from parent page properties
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        // Try to get date range from parent page if available
        // In Livewire, widgets can access parent component
        try {
            if (method_exists($this, 'getParent')) {
                $parent = $this->getParent();
                if ($parent && isset($parent->startDate) && $parent->startDate) {
                    $startDate = Carbon::parse($parent->startDate);
                }
                if ($parent && isset($parent->endDate) && $parent->endDate) {
                    $endDate = Carbon::parse($parent->endDate);
                }
            }
        } catch (\BadMethodCallException $e) {
            // getParent() doesn't exist, use default dates
        } catch (\Exception $e) {
            // Fallback to default dates if parent is not accessible
        }

        $transactions = Transaction::where('الحالة', 'مكتملة')
            ->whereBetween('تاريخ_المعاملة', [$startDate, $endDate])
            ->with('items.service')
            ->get();

        $totalRevenue = $transactions->sum('السعر');
        $totalCost = $transactions->sum(function ($transaction) {
            return $transaction->items->sum(function ($item) {
                $serviceCost = $item->service ? $item->service->التكلفة : ($item->التكلفة ?? 0);

                return $serviceCost * $item->الكمية;
            });
        });
        $netProfit = $totalRevenue - $totalCost;
        $transactionsCount = $transactions->count();
        $averageTransactionValue = $transactionsCount > 0 ? $totalRevenue / $transactionsCount : 0;

        return [
            Stat::make('إجمالي الإيرادات', number_format($totalRevenue, 2).' LYD')
                ->description('من المعاملات المكتملة')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('إجمالي التكلفة', number_format($totalCost, 2).' LYD')
                ->description('تكلفة الخدمات المقدمة')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('صافي الربح', number_format($netProfit, 2).' LYD')
                ->description('الإيرادات - التكلفة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($netProfit >= 0 ? 'success' : 'danger'),
            Stat::make('عدد المعاملات', number_format($transactionsCount, 0))
                ->description('معاملة مكتملة')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
            Stat::make('متوسط قيمة المعاملة', number_format($averageTransactionValue, 2).' LYD')
                ->description('إجمالي الإيرادات ÷ عدد المعاملات')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
        ];
    }

}
