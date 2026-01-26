<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class VehicleReportStats extends BaseWidget
{
    protected function getStats(): array
    {
        $selectedVehicle = null;
        
        // Try to get the owner using getCachedOwner (Livewire method)
        try {
            if (method_exists($this, 'getCachedOwner')) {
                $owner = $this->getCachedOwner();
                if ($owner && property_exists($owner, 'selectedVehicle') && $owner->selectedVehicle) {
                    $selectedVehicle = $owner->selectedVehicle;
                }
            } elseif (property_exists($this, 'owner') && $this->owner) {
                if (property_exists($this->owner, 'selectedVehicle') && $this->owner->selectedVehicle) {
                    $selectedVehicle = $this->owner->selectedVehicle;
                }
            }
        } catch (\Exception $e) {
            // Fallback to null if owner is not available
        }

        if (!$selectedVehicle) {
            return [];
        }

        $transactions = Transaction::where('vehicle_id', $selectedVehicle)
            ->where('الحالة', 'مكتملة')
            ->with('payment')
            ->get();

        $totalTransactions = $transactions->count();
        $totalRevenue = $transactions->sum('السعر');
        $totalPaid = $transactions->sum(function ($transaction) {
            return $transaction->payment?->المبلغ ?? 0;
        });

        return [
            Stat::make('عدد المعاملات', $totalTransactions)
                ->description('معاملات مكتملة لهذه المركبة')
                ->color('primary'),
            Stat::make('إجمالي الإيرادات', Number::format($totalRevenue, 2) . ' LYD')
                ->description('إجمالي قيمة المعاملات')
                ->color('success'),
            Stat::make('إجمالي المدفوع', Number::format($totalPaid, 2) . ' LYD')
                ->description('إجمالي المبالغ المدفوعة')
                ->color('info'),
        ];
    }
}
