<?php

namespace App\Filament\Resources\TransactionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'الدفع';

    protected static ?string $modelLabel = 'دفع';

    protected static ?string $pluralModelLabel = 'المدفوعات';

    protected static bool $isLazy = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الدفع')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Placeholder::make('transaction_price')
                                    ->label('سعر المعاملة')
                                    ->content(function () {
                                        $transaction = $this->getOwnerRecord();

                                        return number_format($transaction->السعر, 2).' LYD';
                                    })
                                    ->extraAttributes(['class' => 'text-lg font-semibold']),
                                Forms\Components\Placeholder::make('total_paid')
                                    ->label('المبلغ المدفوع')
                                    ->content(function () {
                                        $transaction = $this->getOwnerRecord();

                                        return number_format($transaction->total_paid, 2).' LYD';
                                    })
                                    ->extraAttributes(['class' => 'text-lg font-semibold text-success-600']),
                                Forms\Components\Placeholder::make('remaining_amount')
                                    ->label('المبلغ المتبقي')
                                    ->content(function () {
                                        $transaction = $this->getOwnerRecord();

                                        return number_format($transaction->remaining_amount, 2).' LYD';
                                    })
                                    ->extraAttributes(['class' => 'text-lg font-semibold text-warning-600'])
                                    ->visible(function () {
                                        $transaction = $this->getOwnerRecord();

                                        return $transaction->remaining_amount > 0;
                                    }),
                            ]),
                    ]),
                Forms\Components\TextInput::make('المبلغ')
                    ->label('المبلغ')
                    ->required()
                    ->numeric()
                    ->prefix('LYD')
                    ->minValue(0.01)
                    ->maxValue(function () {
                        $transaction = $this->getOwnerRecord();

                        return $transaction->remaining_amount;
                    })
                    ->helperText(function () {
                        $transaction = $this->getOwnerRecord();

                        return $transaction->remaining_amount > 0 ? 'الحد الأقصى: '.number_format($transaction->remaining_amount, 2).' LYD' : 'تم دفع المبلغ بالكامل';
                    })
                    ->disabled(function () {
                        $transaction = $this->getOwnerRecord();

                        return $transaction->remaining_amount <= 0;
                    })
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        $transaction = $this->getOwnerRecord();
                        if ($state > $transaction->remaining_amount) {
                            $set('المبلغ', $transaction->remaining_amount);
                        }
                    }),
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
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('المبلغ')
                    ->label('المبلغ المدفوع')
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                            ->label('إجمالي المدفوع'),
                    ]),
                Tables\Columns\TextColumn::make('transaction.السعر')
                    ->label('سعر المعاملة')
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                    ->sortable()
                    ->visible(fn () => false),
                Tables\Columns\TextColumn::make('payment_remaining')
                    ->label('المتبقي بعد الدفع')
                    ->state(function ($record): float {
                        $transaction = $record->transaction;
                        $paidBeforeThis = $transaction->payments()
                            ->where('id', '<=', $record->id)
                            ->sum('المبلغ');

                        return max(0, $transaction->السعر - $paidBeforeThis);
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                    ->color(fn (float $state): string => $state <= 0 ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('طريقة_الدفع')
                    ->label('طريقة الدفع')
                    ->badge(),
                Tables\Columns\TextColumn::make('رقم_الإيصال')
                    ->label('رقم الإيصال')
                    ->searchable()
                    ->placeholder('—'),
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
