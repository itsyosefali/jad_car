<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class UnpaidClientsStats extends BaseWidget
{
    public static function canView(): bool
    {
        // Only show when explicitly added to a page, not auto-discovered
        return false;
    }
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Get date range from parent page properties
        $startDate = now()->startOfYear();
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

        // Use the same query logic as the report page
        $clients = Client::query()
            ->select('clients.*')
            ->selectRaw('
                COALESCE((
                    SELECT SUM(transactions.السعر)
                    FROM transactions
                    WHERE transactions.client_id = clients.id
                    AND transactions.الحالة = "مكتملة"
                    AND transactions.تاريخ_المعاملة BETWEEN ? AND ?
                ), 0) as total_owed,
                COALESCE((
                    SELECT SUM(payments.المبلغ)
                    FROM payments
                    INNER JOIN transactions ON payments.transaction_id = transactions.id
                    WHERE transactions.client_id = clients.id
                    AND transactions.الحالة = "مكتملة"
                    AND transactions.تاريخ_المعاملة BETWEEN ? AND ?
                ), 0) as total_paid
            ', [$startDate, $endDate, $startDate, $endDate])
            ->havingRaw('(total_owed - total_paid) > 0')
            ->get()
            ->map(function ($client) {
                $client->balance = $client->total_owed - $client->total_paid;
                return $client;
            });

        $totalOutstanding = $clients->sum('balance');
        $clientsCount = $clients->count();
        $averageBalance = $clientsCount > 0 ? $totalOutstanding / $clientsCount : 0;
        
        // Count unpaid transactions more efficiently
        $totalUnpaidTransactions = \App\Models\Transaction::where('الحالة', 'مكتملة')
            ->whereBetween('تاريخ_المعاملة', [$startDate, $endDate])
            ->whereIn('client_id', $clients->pluck('id'))
            ->whereRaw('السعر > COALESCE((SELECT SUM(المبلغ) FROM payments WHERE payments.transaction_id = transactions.id), 0)')
            ->count();

        return [
            Stat::make('إجمالي المبلغ المستحق', number_format($totalOutstanding, 2).' LYD')
                ->description('من جميع العملاء غير المدفوعين')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            Stat::make('عدد العملاء غير المدفوعين', number_format($clientsCount, 0))
                ->description('عميل لديه رصيد مستحق')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
            Stat::make('متوسط الرصيد المستحق', number_format($averageBalance, 2).' LYD')
                ->description('لكل عميل')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
            Stat::make('عدد المعاملات غير المدفوعة', number_format($totalUnpaidTransactions, 0))
                ->description('معاملة')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
        ];
    }
}
