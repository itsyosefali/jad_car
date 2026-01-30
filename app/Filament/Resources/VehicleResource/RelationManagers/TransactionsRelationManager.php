<?php

namespace App\Filament\Resources\VehicleResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'المعاملات';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('الرقم_المرجعي')
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
                    ->searchable(),
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
                    ->label('السعر')
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('تاريخ_المعاملة')
                    ->label('تاريخ المعاملة')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
