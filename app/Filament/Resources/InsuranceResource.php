<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InsuranceResource\Pages;
use App\Filament\Resources\InsuranceResource\RelationManagers;
use App\Models\Insurance;
use App\Models\Client;
use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InsuranceResource extends Resource
{
    protected static ?string $model = Insurance::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'التأمين';

    protected static ?string $modelLabel = 'تأمين';

    protected static ?string $pluralModelLabel = 'التأمين';

    protected static ?string $navigationGroup = 'الوثائق';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات التأمين')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('رقم_الوثيقة')
                                    ->label('رقم الوثيقة')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('التاريخ')
                                    ->label('التاريخ')
                                    ->required()
                                    ->default(now())
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                        $date = $get('التاريخ');
                                        $years = $get('كم_سنة');
                                        if ($date && $years) {
                                            $endDate = Carbon::parse($date)->addYears((int) $years);
                                            $set('تاريخ_النهاية', $endDate->format('Y-m-d'));
                                        }
                                    }),
                                Forms\Components\TextInput::make('كم_سنة')
                                    ->label('كم سنة')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                        $date = $get('التاريخ');
                                        $years = $get('كم_سنة');
                                        if ($date && $years) {
                                            $endDate = Carbon::parse($date)->addYears((int) $years);
                                            $set('تاريخ_النهاية', $endDate->format('Y-m-d'));
                                        }
                                    }),
                                Forms\Components\DatePicker::make('تاريخ_النهاية')
                                    ->label('تاريخ النهاية')
                                    ->disabled()
                                    ->dehydrated()
                                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('Y-m-d') : '—')
                                    ->helperText('يتم الحساب تلقائياً من التاريخ + كم سنة'),
                                Forms\Components\TextInput::make('تكلفة_الوثيقة')
                                    ->label('تكلفة الوثيقة')
                                    ->numeric()
                                    ->prefix('LYD')
                                    ->default(0)
                                    ->step(0.01),
                                Forms\Components\Select::make('client_id')
                                    ->label('العميل')
                                    ->relationship('client', 'الاسم_الكامل')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('الاسم_الكامل')
                                            ->label('الاسم الكامل')
                                            ->required(),
                                        Forms\Components\TextInput::make('الرقم_الوطني')
                                            ->label('الرقم الوطني'),
                                        Forms\Components\TextInput::make('رقم_الهاتف')
                                            ->label('رقم الهاتف')
                                            ->tel(),
                                    ]),
                                Forms\Components\Select::make('vehicle_id')
                                    ->label('المركبة')
                                    ->options(function () {
                                        return Vehicle::query()
                                            ->whereNotNull('رقم_الهيكل')
                                            ->get()
                                            ->mapWithKeys(function ($vehicle) {
                                                $label = $vehicle->رقم_الهيكل;
                                                if ($vehicle->رقم_اللوحة) {
                                                    $label .= ' - '.$vehicle->رقم_اللوحة;
                                                }
                                                if ($vehicle->نوع_المركبة) {
                                                    $label .= ' ('.$vehicle->نوع_المركبة.')';
                                                }

                                                return [$vehicle->id => $label];
                                            })
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                Forms\Components\Textarea::make('الملاحظات')
                                    ->label('الملاحظات')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('رقم_الوثيقة')
                    ->label('رقم الوثيقة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('client.الاسم_الكامل')
                    ->label('اسم العميل')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),
                Tables\Columns\TextColumn::make('vehicle.رقم_الهيكل')
                    ->label('رقم الهيكل')
                    ->searchable()
                    ->default('—')
                    ->icon('heroicon-o-truck'),
                Tables\Columns\TextColumn::make('التاريخ')
                    ->label('تاريخ البداية')
                    ->date('Y-m-d')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                Tables\Columns\TextColumn::make('كم_سنة')
                    ->label('المدة (سنوات)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('تاريخ_النهاية')
                    ->label('تاريخ النهاية')
                    ->date('Y-m-d')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state && Carbon::parse($state)->isPast() ? 'danger' : ($state && Carbon::parse($state)->isFuture() ? 'success' : 'warning'))
                    ->icon('heroicon-o-calendar-days'),
                Tables\Columns\TextColumn::make('تكلفة_الوثيقة')
                    ->label('تكلفة الوثيقة')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' LYD' : '—')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->state(fn (Insurance $record) => $record->isExpired() ? 'منتهي' : 'نشط')
                    ->color(fn (Insurance $record) => $record->isExpired() ? 'danger' : 'success'),
            ])
            ->filters([
                Tables\Filters\Filter::make('expiration_status')
                    ->label('حالة الانتهاء')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'expired' => 'منتهي',
                                'active' => 'نشط',
                                'upcoming' => 'قريب الانتهاء',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['status'] === 'expired',
                                fn (Builder $query): Builder => $query->where('تاريخ_النهاية', '<', now()),
                            )
                            ->when(
                                $data['status'] === 'active',
                                fn (Builder $query): Builder => $query->where('تاريخ_النهاية', '>=', now()),
                            )
                            ->when(
                                $data['status'] === 'upcoming',
                                fn (Builder $query): Builder => $query->whereBetween('تاريخ_النهاية', [now(), now()->addMonths(1)]),
                            );
                    }),
                Tables\Filters\Filter::make('التاريخ')
                    ->label('التاريخ')
                    ->form([
                        Forms\Components\DatePicker::make('من')
                            ->label('من'),
                        Forms\Components\DatePicker::make('إلى')
                            ->label('إلى'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['من'],
                                fn (Builder $query, $date): Builder => $query->whereDate('التاريخ', '>=', $date),
                            )
                            ->when(
                                $data['إلى'],
                                fn (Builder $query, $date): Builder => $query->whereDate('التاريخ', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات التأمين')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Infolists\Components\TextEntry::make('رقم_الوثيقة')
                            ->label('رقم الوثيقة')
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('التاريخ')
                            ->label('تاريخ البداية')
                            ->date('Y-m-d'),
                        Infolists\Components\TextEntry::make('كم_سنة')
                            ->label('المدة (سنوات)'),
                        Infolists\Components\TextEntry::make('تاريخ_النهاية')
                            ->label('تاريخ النهاية')
                            ->date('Y-m-d')
                            ->badge()
                            ->color(fn ($state) => $state && Carbon::parse($state)->isPast() ? 'danger' : 'success'),
                        Infolists\Components\TextEntry::make('تكلفة_الوثيقة')
                            ->label('تكلفة الوثيقة')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' LYD' : '—')
                            ->weight('bold')
                            ->size('lg'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->state(fn (Insurance $record) => $record->isExpired() ? 'منتهي' : 'نشط')
                            ->color(fn (Insurance $record) => $record->isExpired() ? 'danger' : 'success'),
                    ])
                    ->columns(3),
                Infolists\Components\Section::make('معلومات العميل')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Infolists\Components\TextEntry::make('client.الاسم_الكامل')
                            ->label('الاسم الكامل'),
                        Infolists\Components\TextEntry::make('client.الرقم_الوطني')
                            ->label('الرقم الوطني')
                            ->placeholder('غير متوفر'),
                        Infolists\Components\TextEntry::make('client.رقم_الهاتف')
                            ->label('رقم الهاتف')
                            ->placeholder('غير متوفر'),
                    ])
                    ->columns(3),
                Infolists\Components\Section::make('معلومات المركبة')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Infolists\Components\TextEntry::make('vehicle.رقم_الهيكل')
                            ->label('رقم الهيكل')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('vehicle.رقم_اللوحة')
                            ->label('رقم اللوحة')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('vehicle.نوع_المركبة')
                            ->label('نوع المركبة')
                            ->placeholder('—'),
                    ])
                    ->columns(3)
                    ->visible(fn (Insurance $record) => $record->vehicle !== null),
                Infolists\Components\Section::make('ملاحظات')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Infolists\Components\TextEntry::make('الملاحظات')
                            ->label('الملاحظات')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Insurance $record) => $record->الملاحظات),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInsurances::route('/'),
            'create' => Pages\CreateInsurance::route('/create'),
            'view' => Pages\ViewInsurance::route('/{record}'),
            'edit' => Pages\EditInsurance::route('/{record}/edit'),
        ];
    }
}
