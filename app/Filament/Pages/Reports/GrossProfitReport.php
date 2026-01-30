<?php

namespace App\Filament\Pages\Reports;

use App\Models\Service;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class GrossProfitReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.reports.gross-profit-report';

    protected static ?string $navigationLabel = 'تقرير الربح الإجمالي';

    protected static ?string $title = 'تقرير الربح الإجمالي';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?int $navigationSort = 5;

    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('اسم_الخدمة')
                    ->label('اسم الخدمة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('التكلفة')
                    ->label('التكلفة (لكل وحدة)')
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('سعر_البيع')
                    ->label('سعر البيع (لكل وحدة)')
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('profit_per_unit')
                    ->label('الربح (لكل وحدة)')
                    ->formatStateUsing(fn ($record) => number_format($record->سعر_البيع - $record->التكلفة, 2).' LYD')
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('الكمية المباعة')
                    ->formatStateUsing(fn ($state) => number_format($state, 0))
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn ($state) => number_format($state, 0))
                            ->label('الإجمالي'),
                    ]),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('إجمالي الإيرادات')
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                            ->label('الإجمالي'),
                    ]),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('إجمالي التكلفة')
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                            ->label('الإجمالي'),
                    ]),
                Tables\Columns\TextColumn::make('total_profit')
                    ->label('إجمالي الربح')
                    ->getStateUsing(fn ($record) => $record->total_revenue - $record->total_cost)
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                    ->color('success')
                    ->weight('bold')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                            ->label('الإجمالي'),
                    ]),
                Tables\Columns\TextColumn::make('transactions_count')
                    ->label('عدد المعاملات')
                    ->formatStateUsing(fn ($state) => number_format($state, 0))
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn ($state) => number_format($state, 0))
                            ->label('الإجمالي'),
                    ]),
                Tables\Columns\IconColumn::make('نشط')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('startDate')
                            ->label('من تاريخ')
                            ->default(fn () => now()->startOfMonth())
                            ->displayFormat('Y-m-d'),
                        \Filament\Forms\Components\DatePicker::make('endDate')
                            ->label('إلى تاريخ')
                            ->default(fn () => now()->endOfMonth())
                            ->displayFormat('Y-m-d'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['startDate'],
                                fn (Builder $query, $date): Builder => $query->whereHas('transactionItems.transaction', function ($q) use ($date) {
                                    $q->where('الحالة', 'مكتملة')
                                        ->whereDate('تاريخ_المعاملة', '>=', $date);
                                })
                            )
                            ->when(
                                $data['endDate'],
                                fn (Builder $query, $date): Builder => $query->whereHas('transactionItems.transaction', function ($q) use ($date) {
                                    $q->where('الحالة', 'مكتملة')
                                        ->whereDate('تاريخ_المعاملة', '<=', $date);
                                })
                            );
                    }),
                Tables\Filters\SelectFilter::make('نشط')
                    ->label('الحالة')
                    ->options([
                        1 => 'نشط',
                        0 => 'غير نشط',
                    ]),
            ])
            ->defaultSort('total_profit', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $startDate = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : now()->startOfMonth();
        $endDate = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : now()->endOfMonth();

        return Service::query()
            ->select('services.*')
            ->selectRaw('
                COALESCE(SUM(CASE 
                    WHEN transactions.الحالة = ? 
                    AND transactions.تاريخ_المعاملة BETWEEN ? AND ?
                    THEN transaction_items.الكمية 
                    ELSE 0 
                END), 0) as total_quantity
            ', ['مكتملة', $startDate, $endDate])
            ->selectRaw('
                COALESCE(SUM(CASE 
                    WHEN transactions.الحالة = ? 
                    AND transactions.تاريخ_المعاملة BETWEEN ? AND ?
                    THEN services.سعر_البيع * transaction_items.الكمية 
                    ELSE 0 
                END), 0) as total_revenue
            ', ['مكتملة', $startDate, $endDate])
            ->selectRaw('
                COALESCE(SUM(CASE 
                    WHEN transactions.الحالة = ? 
                    AND transactions.تاريخ_المعاملة BETWEEN ? AND ?
                    THEN services.التكلفة * transaction_items.الكمية 
                    ELSE 0 
                END), 0) as total_cost
            ', ['مكتملة', $startDate, $endDate])
            ->selectRaw('
                COALESCE(COUNT(DISTINCT CASE 
                    WHEN transactions.الحالة = ? 
                    AND transactions.تاريخ_المعاملة BETWEEN ? AND ?
                    THEN transactions.id 
                END), 0) as transactions_count
            ', ['مكتملة', $startDate, $endDate])
            ->selectRaw('
                (COALESCE(SUM(CASE 
                    WHEN transactions.الحالة = ? 
                    AND transactions.تاريخ_المعاملة BETWEEN ? AND ?
                    THEN services.سعر_البيع * transaction_items.الكمية 
                    ELSE 0 
                END), 0) - COALESCE(SUM(CASE 
                    WHEN transactions.الحالة = ? 
                    AND transactions.تاريخ_المعاملة BETWEEN ? AND ?
                    THEN services.التكلفة * transaction_items.الكمية 
                    ELSE 0 
                END), 0)) as total_profit
            ', ['مكتملة', $startDate, $endDate, 'مكتملة', $startDate, $endDate])
            ->leftJoin('transaction_items', 'services.id', '=', 'transaction_items.service_id')
            ->leftJoin('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->groupBy('services.id', 'services.اسم_الخدمة', 'services.التكلفة', 'services.سعر_البيع', 'services.الوصف', 'services.نشط', 'services.created_at', 'services.updated_at')
            ->havingRaw('transactions_count > 0');
    }
}
