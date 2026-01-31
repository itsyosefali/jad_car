<?php

namespace App\Filament\Widgets;

use App\Models\Deposit;
use App\Models\Expense;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TreasuryBalanceWidget extends BaseWidget
{
    public static function canView(): bool
    {
        // Only show when explicitly added to a page, not auto-discovered
        return false;
    }

    protected function getStats(): array
    {
        // Calculate total profit from completed transactions
        $transactions = Transaction::where('الحالة', 'مكتملة')
            ->with('items.service')
            ->get();

        $totalProfit = $transactions->sum(function ($transaction) {
            $profit = 0;
            foreach ($transaction->items as $item) {
                if ($item->service) {
                    $profit += (($item->service->سعر_البيع - $item->service->التكلفة) * $item->الكمية);
                }
            }

            return $profit;
        });

        // Calculate total expenses
        $totalExpenses = Expense::sum('المبلغ');

        // Calculate total deposits
        $totalDeposits = Deposit::sum('المبلغ');

        // Calculate current cash balance
        $cashBalance = $totalDeposits + $totalProfit - $totalExpenses;

        return [
            Stat::make('إجمالي الربح', number_format($totalProfit, 2).' LYD')
                ->description('من المعاملات المكتملة')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('إجمالي المصروفات', number_format($totalExpenses, 2).' LYD')
                ->description('من سجل المصروفات')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('إجمالي الايداع', number_format($totalDeposits, 2).' LYD')
                ->description('من سجل الايداع')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
            Stat::make('رصيد الخزينة', number_format($cashBalance, 2).' LYD')
                ->description('الايداع + الربح - المصروفات')
                ->descriptionIcon('heroicon-m-wallet')
                ->color($cashBalance >= 0 ? 'success' : 'danger'),
        ];
    }
}
