<?php

namespace App\Filament\Resources\TransactionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'تفاصيل المعاملة';

    protected static ?string $modelLabel = 'عنصر';

    protected static ?string $pluralModelLabel = 'عناصر المعاملة';

    protected static bool $isLazy = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('service_id')
                    ->label('الخدمة')
                    ->options(function () {
                        return \App\Models\Service::where('نشط', true)
                            ->whereNotNull('اسم_الخدمة')
                            ->pluck('اسم_الخدمة', 'id')
                            ->filter()
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            $service = \App\Models\Service::find($state);
                            if ($service) {
                                $set('التكلفة', $service->التكلفة);
                                $set('اسم_الخدمة', $service->اسم_الخدمة);
                            }
                        }
                    }),
                Forms\Components\TextInput::make('اسم_الخدمة')
                    ->label('اسم الخدمة')
                    ->disabled()
                    ->dehydrated()
                    ->visible(fn (Forms\Get $get) => $get('service_id')),
                Forms\Components\TextInput::make('التكلفة')
                    ->label('التكلفة')
                    ->disabled()
                    ->dehydrated()
                    ->prefix('LYD')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) : '0.00')
                    ->visible(fn (Forms\Get $get) => $get('service_id')),
                Forms\Components\Placeholder::make('selling_price')
                    ->label('سعر البيع')
                    ->content(function (Forms\Get $get) {
                        $serviceId = $get('service_id');
                        if ($serviceId) {
                            $service = \App\Models\Service::find($serviceId);
                            if ($service) {
                                return number_format($service->سعر_البيع, 2).' LYD';
                            }
                        }

                        return '—';
                    })
                    ->visible(fn (Forms\Get $get) => $get('service_id')),
                Forms\Components\TextInput::make('الكمية')
                    ->label('الكمية')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
                Forms\Components\Textarea::make('الملاحظات')
                    ->label('الملاحظات')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('اسم_الخدمة')
            ->columns([
                Tables\Columns\TextColumn::make('service.اسم_الخدمة')
                    ->label('اسم الخدمة')
                    ->searchable()
                    ->sortable()
                    ->placeholder(fn ($record) => $record->اسم_الخدمة ?? '—'),
                Tables\Columns\TextColumn::make('service.التكلفة')
                    ->label('التكلفة')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' LYD' : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.سعر_البيع')
                    ->label('سعر البيع')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' LYD' : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('الكمية')
                    ->label('الكمية')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn ($record) => number_format($record->total, 2).' LYD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('profit')
                    ->label('الربح')
                    ->state(function ($record) {
                        if ($record->service) {
                            return ($record->service->سعر_البيع - $record->service->التكلفة) * $record->الكمية;
                        }

                        return null;
                    })
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state, 2).' LYD' : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('الملاحظات')
                    ->label('الملاحظات')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->الملاحظات),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['transaction_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['transaction_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'asc');
    }
}
