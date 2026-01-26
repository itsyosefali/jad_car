<?php

namespace App\Filament\Pages\Reports;

use App\Models\Transaction;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class MonthlyReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static string $view = 'filament.pages.reports.monthly-report';

    protected static ?string $navigationLabel = 'التقرير الشهري';

    protected static ?string $title = 'التقرير الشهري';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?int $navigationSort = 6;

    public ?string $selectedMonth = null;

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = Transaction::query()
                    ->where('الحالة', 'مكتملة')
                    ->with(['client', 'vehicle']);

                if ($this->selectedMonth) {
                    $date = Carbon::parse($this->selectedMonth . '-01');
                    $query->whereYear('تاريخ_المعاملة', $date->year)
                        ->whereMonth('تاريخ_المعاملة', $date->month);
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('الرقم_المرجعي')
                    ->label('الرقم المرجعي')
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('client.الاسم_الكامل')
                    ->label('اسم العميل')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle.رقم_اللوحة')
                    ->label('رقم اللوحة')
                    ->default('—'),
                Tables\Columns\TextColumn::make('نوع_المعاملة')
                    ->label('نوع المعاملة')
                    ->badge(),
                Tables\Columns\TextColumn::make('السعر')
                    ->label('المبلغ')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' LYD')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn ($state) => number_format($state, 2) . ' LYD')
                            ->label('الإجمالي'),
                    ]),
                Tables\Columns\TextColumn::make('تاريخ_المعاملة')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('نوع_المعاملة')
                    ->label('نوع المعاملة')
                    ->options([
                        'تسجيل' => 'تسجيل',
                        'تأمين' => 'تأمين',
                        'تجديد' => 'تجديد',
                        'فحص' => 'فحص',
                    ]),
            ])
            ->defaultSort('تاريخ_المعاملة', 'desc');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\MonthlyReportStats::class,
        ];
    }
}
