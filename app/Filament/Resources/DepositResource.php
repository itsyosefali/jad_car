<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepositResource\Pages;
use App\Filament\Resources\DepositResource\RelationManagers;
use App\Models\Deposit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DepositResource extends Resource
{
    protected static ?string $model = Deposit::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?string $navigationLabel = 'ايداع';

    protected static ?string $modelLabel = 'ايداع';

    protected static ?string $pluralModelLabel = 'الايداع';

    protected static ?string $navigationGroup = 'المالية';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الايداع')
                    ->icon('heroicon-o-arrow-trending-up')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('المبلغ')
                                    ->label('المبلغ')
                                    ->required()
                                    ->numeric()
                                    ->prefix('LYD')
                                    ->default(0),
                                Forms\Components\Select::make('الفئة')
                                    ->label('الفئة')
                                    ->required()
                                    ->options([
                                        'إيداع نقدي' => 'إيداع نقدي',
                                        'تحويل بنكي' => 'تحويل بنكي',
                                        'أخرى' => 'أخرى',
                                    ]),
                                Forms\Components\DatePicker::make('التاريخ')
                                    ->label('التاريخ')
                                    ->required()
                                    ->default(now()),
                                Forms\Components\TextInput::make('الوصف')
                                    ->label('الوصف')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('المبلغ')
                    ->label('المبلغ')
                    ->formatStateUsing(fn ($state) => '<span class="font-semibold">'.number_format($state, 2).' LYD</span>')
                    ->html()
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('الفئة')
                    ->label('الفئة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('التاريخ')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                Tables\Columns\TextColumn::make('الوصف')
                    ->label('الوصف')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('الفئة')
                    ->label('الفئة')
                    ->options([
                        'إيداع نقدي' => 'إيداع نقدي',
                        'تحويل بنكي' => 'تحويل بنكي',
                        'أخرى' => 'أخرى',
                    ]),
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
                Infolists\Components\Section::make('معلومات الايداع')
                    ->icon('heroicon-o-arrow-trending-up')
                    ->schema([
                        Infolists\Components\TextEntry::make('المبلغ')
                            ->label('المبلغ')
                            ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                            ->weight('bold')
                            ->size('lg'),
                        Infolists\Components\TextEntry::make('الفئة')
                            ->label('الفئة')
                            ->badge()
                            ->color('success'),
                        Infolists\Components\TextEntry::make('التاريخ')
                            ->label('التاريخ')
                            ->date('Y-m-d'),
                        Infolists\Components\TextEntry::make('الوصف')
                            ->label('الوصف')
                            ->placeholder('لا يوجد وصف')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('الملاحظات')
                            ->label('الملاحظات')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y-m-d H:i'),
                    ])
                    ->columns(3),
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
            'index' => Pages\ListDeposits::route('/'),
            'create' => Pages\CreateDeposit::route('/create'),
            'view' => Pages\ViewDeposit::route('/{record}'),
            'edit' => Pages\EditDeposit::route('/{record}/edit'),
        ];
    }
}
