<?php

namespace App\Filament\Pages\Reports;

use App\Models\Transaction;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class FinancialSummaryReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static string $view = 'filament.pages.reports.financial-summary-report';

    protected static ?string $navigationLabel = 'التقرير المالي';

    protected static ?string $title = 'التقرير المالي';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?int $navigationSort = 6;

    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\FinancialSummaryStats::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('تاريخ_المعاملة')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('الرقم_المرجعي')
                    ->label('الرقم المرجعي')
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('client.الاسم_الكامل')
                    ->label('العميل')
                    ->searchable(),
                Tables\Columns\TextColumn::make('revenue')
                    ->label('الإيرادات')
                    ->getStateUsing(fn ($record) => $record->السعر)
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD'),
                Tables\Columns\TextColumn::make('cost')
                    ->label('التكلفة')
                    ->getStateUsing(function ($record) {
                        return $record->items->sum(function ($item) {
                            $serviceCost = $item->service ? $item->service->التكلفة : ($item->التكلفة ?? 0);

                            return $serviceCost * $item->الكمية;
                        });
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD'),
                Tables\Columns\TextColumn::make('profit')
                    ->label('الربح')
                    ->getStateUsing(function ($record) {
                        $revenue = $record->السعر;
                        $cost = $record->items->sum(function ($item) {
                            $serviceCost = $item->service ? $item->service->التكلفة : ($item->التكلفة ?? 0);

                            return $serviceCost * $item->الكمية;
                        });

                        return $revenue - $cost;
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                    ->color('success')
                    ->weight('bold'),
            ])
            ->filters([
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('startDate')
                            ->label('من تاريخ')
                            ->default(fn () => now()->startOfMonth())
                            ->displayFormat('Y-m-d')
                            ->live()
                            ->afterStateUpdated(fn () => $this->resetTable()),
                        \Filament\Forms\Components\DatePicker::make('endDate')
                            ->label('إلى تاريخ')
                            ->default(fn () => now()->endOfMonth())
                            ->displayFormat('Y-m-d')
                            ->live()
                            ->afterStateUpdated(fn () => $this->resetTable()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['startDate'],
                                fn (Builder $query, $date): Builder => $query->whereDate('تاريخ_المعاملة', '>=', $date)
                            )
                            ->when(
                                $data['endDate'],
                                fn (Builder $query, $date): Builder => $query->whereDate('تاريخ_المعاملة', '<=', $date)
                            );
                    }),
            ])
            ->defaultSort('تاريخ_المعاملة', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $startDate = $this->startDate ? Carbon::parse($this->startDate) : now()->startOfMonth();
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : now()->endOfMonth();

        return Transaction::query()
            ->where('الحالة', 'مكتملة')
            ->whereBetween('تاريخ_المعاملة', [$startDate, $endDate])
            ->with(['client', 'items.service']);
    }
}
