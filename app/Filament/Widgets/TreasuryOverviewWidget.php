<?php

namespace App\Filament\Widgets;

use App\Models\Deposit;
use App\Models\Expense;
use App\Models\Transaction;
use Filament\Widgets\Widget;

class TreasuryOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.treasury-overview-widget';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Only show when explicitly added to a page, not auto-discovered
        // This prevents the widget from appearing twice (once auto-discovered, once from getHeaderWidgets)
        return false;
    }

    public function getIncome(): float
    {
        // Calculate total income from completed transactions (revenue)
        $totalRevenue = Transaction::where('الحالة', 'مكتملة')
            ->sum('السعر');

        // Calculate total deposits
        $totalDeposits = Deposit::sum('المبلغ');

        // Total income = revenue + deposits
        return $totalRevenue + $totalDeposits;
    }

    public function getExpenses(): float
    {
        return Expense::sum('المبلغ');
    }

    public function getProfit(): float
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

        return $totalProfit;
    }

    public function getDeposits(): float
    {
        return Deposit::sum('المبلغ');
    }

    public function getBalance(): float
    {
        return $this->getIncome() - $this->getExpenses();
    }

    public function getRevenue(): float
    {
        return Transaction::where('الحالة', 'مكتملة')
            ->sum('السعر');
    }

    public function getCost(): float
    {
        // Calculate total cost from completed transactions
        $transactions = Transaction::where('الحالة', 'مكتملة')
            ->with('items.service')
            ->get();

        $totalCost = $transactions->sum(function ($transaction) {
            $cost = 0;
            foreach ($transaction->items as $item) {
                if ($item->service) {
                    $cost += ($item->service->التكلفة * $item->الكمية);
                }
            }

            return $cost;
        });

        return $totalCost;
    }

    public function getNetProfit(): float
    {
        return $this->getRevenue() - $this->getCost();
    }

    public function getTotalIncome(): float
    {
        // Total income = Revenue from transactions + Deposits
        return $this->getRevenue() + $this->getDeposits();
    }

    public function getFinalBalance(): float
    {
        // Final balance = (Revenue + Deposits) - Expenses
        return $this->getTotalIncome() - $this->getExpenses();
    }
}
