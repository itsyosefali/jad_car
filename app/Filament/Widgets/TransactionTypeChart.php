<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TransactionTypeChart extends ChartWidget
{
    protected static ?string $heading = 'المعاملات حسب النوع';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $today = now()->startOfDay();
        
        $data = Transaction::whereDate('تاريخ_المعاملة', $today)
            ->where('الحالة', 'مكتملة')
            ->selectRaw('نوع_المعاملة, COUNT(*) as count')
            ->groupBy('نوع_المعاملة')
            ->get();

        $labels = ['تسجيل', 'تأمين', 'تجديد', 'فحص'];
        $values = [];
        
        foreach ($labels as $type) {
            $values[] = $data->firstWhere('نوع_المعاملة', $type)?->count ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'عدد المعاملات',
                    'data' => $values,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.5)', // blue
                        'rgba(16, 185, 129, 0.5)', // green
                        'rgba(245, 158, 11, 0.5)', // yellow
                        'rgba(239, 68, 68, 0.5)',  // red
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
