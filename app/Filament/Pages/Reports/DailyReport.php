<?php

namespace App\Filament\Pages\Reports;

use App\Models\Transaction;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class DailyReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.reports.daily-report';

    protected static ?string $navigationLabel = 'التقرير اليومي';

    protected static ?string $title = 'التقرير اليومي';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?int $navigationSort = 5;

    public function table(Table $table): Table
    {
        $today = now()->startOfDay();
        
        return $table
            ->query(
                Transaction::query()
                    ->whereDate('تاريخ_المعاملة', $today->toDateString())
                    ->where('الحالة', 'مكتملة')
                    ->with(['client', 'vehicle'])
            )
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
                    ->date(),
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
            ->defaultSort('created_at', 'desc');
    }

    protected function getHeaderWidgets(): array
    {
        $today = now()->startOfDay();
        $todayCount = Transaction::whereDate('تاريخ_المعاملة', $today->toDateString())->where('الحالة', 'مكتملة')->count();
        $todayRevenue = Transaction::whereDate('تاريخ_المعاملة', $today->toDateString())->where('الحالة', 'مكتملة')->sum('السعر');

        return [
            \App\Filament\Widgets\DailyReportStats::class,
        ];
    }
}
