<?php

namespace App\Filament\Pages\Reports;

use App\Models\Client;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class UnpaidClientsReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string $view = 'filament.pages.reports.unpaid-clients-report';

    protected static ?string $navigationLabel = 'تقرير العملاء غير المدفوعين';

    protected static ?string $title = 'تقرير العملاء غير المدفوعين';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?int $navigationSort = 7;

    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfYear()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\UnpaidClientsStats::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('الاسم_الكامل')
                    ->label('اسم العميل')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('رقم_الهاتف')
                    ->label('رقم الهاتف')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('total_owed')
                    ->label('إجمالي المستحقات')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2).' LYD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                            ->label('الإجمالي'),
                    ]),
                Tables\Columns\TextColumn::make('total_paid')
                    ->label('إجمالي المدفوع')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2).' LYD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                            ->label('الإجمالي'),
                    ]),
                Tables\Columns\TextColumn::make('balance')
                    ->label('الرصيد المستحق')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2).' LYD')
                    ->color('danger')
                    ->weight('bold')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                            ->label('الإجمالي'),
                    ]),
                Tables\Columns\TextColumn::make('unpaid_transactions_count')
                    ->label('عدد المعاملات غير المدفوعة')
                    ->getStateUsing(fn (Client $record) => $record->getUnpaidTransactions()->count())
                    ->formatStateUsing(fn ($state) => number_format($state, 0))
                    ->sortable(),
                Tables\Columns\TextColumn::make('oldest_unpaid_date')
                    ->label('أقدم معاملة غير مدفوعة')
                    ->getStateUsing(function (Client $record) {
                        $unpaid = $record->getUnpaidTransactions();

                        return $unpaid->isNotEmpty() ? $unpaid->min('تاريخ_المعاملة') : null;
                    })
                    ->date('Y-m-d')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (Client $record) => $record->balance > 0 ? 'غير مدفوع' : 'مدفوع')
                    ->color(fn (Client $record) => $record->balance > 0 ? 'danger' : 'success'),
            ])
            ->filters([
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('startDate')
                            ->label('من تاريخ')
                            ->default(fn () => now()->startOfYear())
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
                        // Filter is handled in getTableQuery
                        return $query;
                    }),
                Tables\Filters\Filter::make('minimum_balance')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('minimum_balance')
                            ->label('الحد الأدنى للرصيد')
                            ->numeric()
                            ->prefix('LYD')
                            ->default(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        // This will be handled after data is loaded
                        return $query;
                    }),
            ])
            ->defaultSort('balance', 'desc')
            ->actions([
                Tables\Actions\Action::make('view_client')
                    ->label('عرض العميل')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Client $record) => \App\Filament\Resources\ClientResource::getUrl('edit', ['record' => $record])),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        $startDate = $this->startDate ? Carbon::parse($this->startDate) : now()->startOfYear();
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : now()->endOfMonth();

        return Client::query()
            ->select('clients.*')
            ->selectRaw('
                COALESCE((
                    SELECT SUM(transactions.السعر)
                    FROM transactions
                    WHERE transactions.client_id = clients.id
                    AND transactions.الحالة = ?
                    AND transactions.تاريخ_المعاملة BETWEEN ? AND ?
                ), 0) as total_owed
            ', ['مكتملة', $startDate, $endDate])
            ->selectRaw('
                COALESCE((
                    SELECT SUM(payments.المبلغ)
                    FROM payments
                    INNER JOIN transactions ON payments.transaction_id = transactions.id
                    WHERE transactions.client_id = clients.id
                    AND transactions.الحالة = ?
                    AND transactions.تاريخ_المعاملة BETWEEN ? AND ?
                ), 0) as total_paid
            ', ['مكتملة', $startDate, $endDate])
            ->selectRaw('
                (COALESCE((
                    SELECT SUM(transactions.السعر)
                    FROM transactions
                    WHERE transactions.client_id = clients.id
                    AND transactions.الحالة = ?
                    AND transactions.تاريخ_المعاملة BETWEEN ? AND ?
                ), 0) - COALESCE((
                    SELECT SUM(payments.المبلغ)
                    FROM payments
                    INNER JOIN transactions ON payments.transaction_id = transactions.id
                    WHERE transactions.client_id = clients.id
                    AND transactions.الحالة = ?
                    AND transactions.تاريخ_المعاملة BETWEEN ? AND ?
                ), 0)) as balance
            ', ['مكتملة', $startDate, $endDate, 'مكتملة', $startDate, $endDate])
            ->havingRaw('balance > 0')
            ->with(['transactions' => function ($q) use ($startDate, $endDate) {
                $q->where('الحالة', 'مكتملة')
                    ->whereBetween('تاريخ_المعاملة', [$startDate, $endDate])
                    ->with('payments');
            }]);
    }
}
