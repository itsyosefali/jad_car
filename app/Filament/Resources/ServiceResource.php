<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'الخدمات';

    protected static ?string $modelLabel = 'خدمة';

    protected static ?string $pluralModelLabel = 'الخدمات';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الخدمة')
                    ->schema([
                        Forms\Components\TextInput::make('اسم_الخدمة')
                            ->label('اسم الخدمة')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('الوصف')
                            ->label('الوصف')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('التكلفة')
                            ->label('التكلفة')
                            ->required()
                            ->numeric()
                            ->prefix('LYD')
                            ->step(0.01)
                            ->default(0)
                            ->live(),
                        Forms\Components\TextInput::make('سعر_البيع')
                            ->label('سعر البيع')
                            ->required()
                            ->numeric()
                            ->prefix('LYD')
                            ->step(0.01)
                            ->default(0)
                            ->live(),
                        Forms\Components\Placeholder::make('profit')
                            ->label('الربح')
                            ->content(function (Forms\Get $get) {
                                $cost = $get('التكلفة') ?? 0;
                                $sellingPrice = $get('سعر_البيع') ?? 0;
                                $profit = $sellingPrice - $cost;
                                $percentage = $cost > 0 ? (($profit / $cost) * 100) : 0;

                                return number_format($profit, 2).' LYD ('.number_format($percentage, 2).'%)';
                            })
                            ->visible(fn (Forms\Get $get) => ($get('التكلفة') ?? 0) > 0 || ($get('سعر_البيع') ?? 0) > 0),
                        Forms\Components\Toggle::make('نشط')
                            ->label('نشط')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('اسم_الخدمة')
                    ->label('اسم الخدمة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('التكلفة')
                    ->label('التكلفة')
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('سعر_البيع')
                    ->label('سعر البيع')
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('profit')
                    ->label('الربح')
                    ->formatStateUsing(fn (Service $record) => number_format($record->profit, 2).' LYD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('profit_percentage')
                    ->label('نسبة الربح')
                    ->formatStateUsing(fn (Service $record) => number_format($record->profit_percentage, 2).'%')
                    ->sortable(),
                Tables\Columns\IconColumn::make('نشط')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transactionItems_count')
                    ->label('عدد الاستخدامات')
                    ->counts('transactionItems')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('نشط')
                    ->label('نشط')
                    ->placeholder('الكل')
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط'),
            ])
            ->actions([
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
