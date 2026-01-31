<?php

namespace App\Filament\Pages;

use App\Models\Deposit;
use App\Models\Expense;
use App\Models\Transaction;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TreasuryReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.treasury-report';

    protected static ?string $navigationLabel = 'تقرير الخزينة';

    protected static ?string $title = 'تقرير الخزينة (GL Entry)';

    protected static ?string $navigationGroup = 'المالية';

    protected static ?int $navigationSort = 5;

    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function updateFilters(): void
    {
        // This method is called when filters are updated
        // The data will be refreshed automatically via Livewire
    }

    public function getEntries(): Collection
    {
        $startDate = $this->startDate ? Carbon::parse($this->startDate) : now()->startOfMonth();
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : now()->endOfMonth();

        // Get all transactions (revenue)
        $transactions = Transaction::where('الحالة', 'مكتملة')
            ->whereBetween('تاريخ_المعاملة', [$startDate, $endDate])
            ->with('client')
            ->get()
            ->map(function ($transaction) {
                return (object) [
                    'id' => 't_' . $transaction->id,
                    'التاريخ' => Carbon::parse($transaction->تاريخ_المعاملة),
                    'النوع' => 'إيراد',
                    'الوصف' => 'معاملة: ' . ($transaction->الرقم_المرجعي ?? ''),
                    'المبلغ' => $transaction->السعر,
                    'العميل' => $transaction->client?->الاسم_الكامل ?? '—',
                    'المرجع' => $transaction->الرقم_المرجعي ?? '—',
                ];
            });

        // Get all deposits
        $deposits = Deposit::whereBetween('التاريخ', [$startDate, $endDate])
            ->get()
            ->map(function ($deposit) {
                return (object) [
                    'id' => 'd_' . $deposit->id,
                    'التاريخ' => Carbon::parse($deposit->التاريخ),
                    'النوع' => 'إيداع',
                    'الوصف' => ($deposit->الوصف ?? 'إيداع') . ($deposit->الفئة ? ' (' . $deposit->الفئة . ')' : ''),
                    'المبلغ' => $deposit->المبلغ,
                    'العميل' => '—',
                    'المرجع' => '—',
                ];
            });

        // Get all expenses
        $expenses = Expense::whereBetween('التاريخ', [$startDate, $endDate])
            ->get()
            ->map(function ($expense) {
                return (object) [
                    'id' => 'e_' . $expense->id,
                    'التاريخ' => Carbon::parse($expense->التاريخ),
                    'النوع' => 'مصروف',
                    'الوصف' => ($expense->الوصف ?? 'مصروف') . ($expense->الفئة ? ' (' . $expense->الفئة . ')' : ''),
                    'المبلغ' => $expense->المبلغ,
                    'العميل' => '—',
                    'المرجع' => '—',
                ];
            });

        // Merge all and sort by date
        $allEntries = $transactions->concat($deposits)->concat($expenses)
            ->sortBy('التاريخ')
            ->values();

        // Calculate running balance
        $runningBalance = 0;
        $allEntries = $allEntries->map(function ($entry) use (&$runningBalance) {
            if ($entry->النوع === 'مصروف') {
                $runningBalance -= $entry->المبلغ;
            } else {
                $runningBalance += $entry->المبلغ;
            }
            $entry->الرصيد_المتراكم = $runningBalance;
            return $entry;
        });

        return $allEntries;
    }

    public function getSummary(): array
    {
        $entries = $this->getEntries();
        
        $lastEntry = $entries->last();
        
        return [
            'total_income' => $entries->whereIn('النوع', ['إيراد', 'إيداع'])->sum('المبلغ'),
            'total_expenses' => $entries->where('النوع', 'مصروف')->sum('المبلغ'),
            'final_balance' => $lastEntry ? $lastEntry->الرصيد_المتراكم : 0,
            'count' => $entries->count(),
        ];
    }
}
