<?php

namespace App\Filament\Resources\TransactionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentRelationManager extends RelationManager
{
    protected static string $relationship = 'payment';

    protected static ?string $title = 'الدفع';

    protected static ?string $modelLabel = 'دفع';

    protected static ?string $pluralModelLabel = 'المدفوعات';

    protected static bool $isLazy = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('المبلغ')
                    ->label('المبلغ')
                    ->required()
                    ->numeric()
                    ->prefix('LYD'),
                Forms\Components\Select::make('طريقة_الدفع')
                    ->label('طريقة الدفع')
                    ->required()
                    ->options([
                        'نقدي' => 'نقدي',
                        'تحويل' => 'تحويل',
                    ]),
                Forms\Components\TextInput::make('رقم_الإيصال')
                    ->label('رقم الإيصال')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('تاريخ_الدفع')
                    ->label('تاريخ الدفع')
                    ->required()
                    ->default(now()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('المبلغ')
            ->columns([
                Tables\Columns\TextColumn::make('المبلغ')
                    ->label('المبلغ المدفوع')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' LYD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction.السعر')
                    ->label('سعر المعاملة')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' LYD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_difference')
                    ->label('الفرق')
                    ->state(function ($record): float {
                        return $record->transaction->السعر - $record->المبلغ;
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' LYD')
                    ->color(fn (float $state): string => $state < 0 ? 'danger' : ($state > 0 ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('طريقة_الدفع')
                    ->label('طريقة الدفع')
                    ->badge(),
                Tables\Columns\TextColumn::make('رقم_الإيصال')
                    ->label('رقم الإيصال')
                    ->searchable(),
                Tables\Columns\TextColumn::make('تاريخ_الدفع')
                    ->label('تاريخ الدفع')
                    ->date()
                    ->sortable(),
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
            ]);
    }
}
