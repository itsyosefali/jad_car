<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'المدفوعات';

    protected static ?string $modelLabel = 'دفعة';

    protected static ?string $pluralModelLabel = 'المدفوعات';

    protected static ?string $navigationGroup = 'المالية';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الدفعة')
                    ->schema([
                        Forms\Components\Select::make('transaction_id')
                            ->label('المعاملة')
                            ->relationship('transaction', 'الرقم_المرجعي')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->الرقم_المرجعي.' - '.$record->client->الاسم_الكامل.' ('.number_format($record->السعر, 2).' LYD)')
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state) {
                                    $transaction = \App\Models\Transaction::find($state);
                                    if ($transaction) {
                                        $remaining = $transaction->remaining_amount;
                                        $set('المبلغ', $remaining);
                                    }
                                }
                            }),
                        Forms\Components\Placeholder::make('transaction_info')
                            ->label('معلومات المعاملة')
                            ->content(function (Forms\Get $get) {
                                $transactionId = $get('transaction_id');
                                if (!$transactionId) {
                                    return '—';
                                }
                                $transaction = \App\Models\Transaction::with('client')->find($transactionId);
                                if (!$transaction) {
                                    return '—';
                                }
                                $totalPaid = $transaction->total_paid;
                                $remaining = $transaction->remaining_amount;
                                
                                return "العميل: {$transaction->client->الاسم_الكامل}\n".
                                       "إجمالي المعاملة: ".number_format($transaction->السعر, 2)." LYD\n".
                                       "المدفوع: ".number_format($totalPaid, 2)." LYD\n".
                                       "المتبقي: ".number_format($remaining, 2)." LYD";
                            })
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('المبلغ')
                            ->label('المبلغ')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->suffix('LYD')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $transactionId = $get('transaction_id');
                                if ($transactionId && $state) {
                                    $transaction = \App\Models\Transaction::find($transactionId);
                                    if ($transaction) {
                                        $remaining = $transaction->remaining_amount;
                                        if ($state > $remaining) {
                                            $set('المبلغ', $remaining);
                                        }
                                    }
                                }
                            }),
                        Forms\Components\Select::make('طريقة_الدفع')
                            ->label('طريقة الدفع')
                            ->required()
                            ->options([
                                'نقدي' => 'نقدي',
                                'تحويل بنكي' => 'تحويل بنكي',
                                'شيك' => 'شيك',
                                'بطاقة' => 'بطاقة',
                                'أخرى' => 'أخرى',
                            ])
                            ->default('نقدي'),
                        Forms\Components\TextInput::make('رقم_الإيصال')
                            ->label('رقم الإيصال')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('تاريخ_الدفع')
                            ->label('تاريخ الدفع')
                            ->required()
                            ->default(now())
                            ->displayFormat('Y-m-d'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction.الرقم_المرجعي')
                    ->label('الرقم المرجعي')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction.client.الاسم_الكامل')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('المبلغ')
                    ->label('المبلغ')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('طريقة_الدفع')
                    ->label('طريقة الدفع')
                    ->searchable()
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'نقدي' => 'success',
                        'تحويل بنكي' => 'info',
                        'شيك' => 'warning',
                        'بطاقة' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('رقم_الإيصال')
                    ->label('رقم الإيصال')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('تاريخ_الدفع')
                    ->label('تاريخ الدفع')
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('طريقة_الدفع')
                    ->label('طريقة الدفع')
                    ->options([
                        'نقدي' => 'نقدي',
                        'تحويل بنكي' => 'تحويل بنكي',
                        'شيك' => 'شيك',
                        'بطاقة' => 'بطاقة',
                        'أخرى' => 'أخرى',
                    ]),
                Tables\Filters\Filter::make('تاريخ_الدفع')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('من تاريخ'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('تاريخ_الدفع', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('تاريخ_الدفع', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('تاريخ_الدفع', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'bulk-allocation' => Pages\BulkPaymentAllocation::route('/bulk-allocation'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
