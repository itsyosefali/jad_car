<?php

namespace App\Filament\Pages\Reports;

use App\Models\Transaction;
use App\Models\Vehicle;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class VehicleReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static string $view = 'filament.pages.reports.vehicle-report';

    protected static ?string $navigationLabel = 'تقرير حسب سيارة';

    protected static ?string $title = 'تقرير حسب سيارة';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?int $navigationSort = 7;

    public ?int $selectedVehicle = null;

    public function mount(): void
    {
        //
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = Transaction::query()
                    ->with(['client', 'vehicle']);

                if ($this->selectedVehicle) {
                    $query->where('vehicle_id', $this->selectedVehicle);
                } else {
                    $query->whereRaw('1 = 0'); // Show nothing if no vehicle selected
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
                Tables\Columns\TextColumn::make('نوع_المعاملة')
                    ->label('نوع المعاملة')
                    ->badge(),
                Tables\Columns\TextColumn::make('الحالة')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'مكتملة' => 'success',
                        'مسودة' => 'warning',
                        'ملغاة' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('السعر')
                    ->label('المبلغ')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' LYD')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn ($state) => number_format($state, 2) . ' LYD')
                            ->label('الإجمالي'),
                    ]),
                Tables\Columns\TextColumn::make('تاريخ_المعاملة')
                    ->label('تاريخ المعاملة')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('الحالة')
                    ->label('الحالة')
                    ->options([
                        'مسودة' => 'مسودة',
                        'مكتملة' => 'مكتملة',
                        'ملغاة' => 'ملغاة',
                    ]),
            ])
            ->defaultSort('تاريخ_المعاملة', 'desc');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\VehicleReportStats::class,
        ];
    }
}
