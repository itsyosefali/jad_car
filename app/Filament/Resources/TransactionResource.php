<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'المعاملات';

    protected static ?string $modelLabel = 'معاملة';

    protected static ?string $pluralModelLabel = 'المعاملات';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المعاملة')
                    ->schema([
                        Forms\Components\TextInput::make('الرقم_المرجعي')
                            ->label('الرقم المرجعي')
                            ->disabled()
                            ->dehydrated(false),
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
                            ->relationship('vehicle', 'رقم_اللوحة')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('رقم_اللوحة')
                                    ->label('رقم اللوحة')
                                    ->required(),
                                Forms\Components\TextInput::make('نوع_المركبة')
                                    ->label('نوع المركبة (العلامة التجارية)')
                                    ->placeholder('مثال: BMW، هونداي، تويوتا، مرسيدس، إلخ'),
                                Forms\Components\Select::make('الصنف')
                                    ->label('الصنف')
                                    ->required()
                                    ->options([
                                        'سيارة' => 'سيارة',
                                        'شاحنة' => 'شاحنة',
                                        'دراجة' => 'دراجة',
                                        'آلية' => 'آلية',
                                    ]),
                                Forms\Components\TextInput::make('رقم_الهيكل')
                                    ->label('رقم الهيكل'),
                                Forms\Components\TextInput::make('اللون')
                                    ->label('اللون'),
                                Forms\Components\TextInput::make('سنة_الصنع')
                                    ->label('سنة الصنع')
                                    ->numeric(),
                            ]),
                        Forms\Components\Select::make('نوع_المعاملة')
                            ->label('نوع المعاملة')
                            ->required()
                            ->options([
                                'تسجيل' => 'تسجيل',
                                'تأمين' => 'تأمين',
                                'تجديد' => 'تجديد',
                                'فحص' => 'فحص',
                            ]),
                        Forms\Components\Select::make('الحالة')
                            ->label('الحالة')
                            ->required()
                            ->options([
                                'مسودة' => 'مسودة',
                                'مكتملة' => 'مكتملة',
                                'ملغاة' => 'ملغاة',
                            ])
                            ->default('مسودة'),
                        Forms\Components\TextInput::make('السعر')
                            ->label('السعر')
                            ->required()
                            ->numeric()
                            ->prefix('LYD')
                            ->disabled(fn ($record) => $record && $record->isCompleted()),
                        Forms\Components\DatePicker::make('تاريخ_المعاملة')
                            ->label('تاريخ المعاملة')
                            ->required()
                            ->default(now()),
                        Forms\Components\Textarea::make('الملاحظات')
                            ->label('الملاحظات')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('الرقم_المرجعي')
                    ->label('الرقم المرجعي')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('client.الاسم_الكامل')
                    ->label('اسم العميل')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.رقم_اللوحة')
                    ->label('رقم اللوحة')
                    ->searchable()
                    ->default('—'),
                Tables\Columns\TextColumn::make('نوع_المعاملة')
                    ->label('نوع المعاملة')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('الحالة')
                    ->label('الحالة')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'مكتملة' => 'success',
                        'مسودة' => 'warning',
                        'ملغاة' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('السعر')
                    ->label('السعر')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' LYD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('تاريخ_المعاملة')
                    ->label('تاريخ المعاملة')
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإدخال')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('الحالة')
                    ->label('الحالة')
                    ->options([
                        'مسودة' => 'مسودة',
                        'مكتملة' => 'مكتملة',
                        'ملغاة' => 'ملغاة',
                    ]),
                Tables\Filters\Filter::make('تاريخ_المعاملة')
                    ->label('تاريخ المعاملة')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('تاريخ_المعاملة', '>=', $date),
                            )
                            ->when(
                                $data['إلى'],
                                fn (Builder $query, $date): Builder => $query->whereDate('تاريخ_المعاملة', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Transaction $record) => !$record->isCompleted()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات المعاملة')
                    ->schema([
                        Infolists\Components\TextEntry::make('الرقم_المرجعي')
                            ->label('الرقم المرجعي')
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('نوع_المعاملة')
                            ->label('نوع المعاملة')
                            ->badge(),
                        Infolists\Components\TextEntry::make('الحالة')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'مكتملة' => 'success',
                                'مسودة' => 'warning',
                                'ملغاة' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('السعر')
                            ->label('السعر')
                            ->formatStateUsing(fn ($state) => number_format($state, 2) . ' LYD'),
                        Infolists\Components\TextEntry::make('تاريخ_المعاملة')
                            ->label('تاريخ المعاملة')
                            ->date('Y-m-d'),
                        Infolists\Components\TextEntry::make('تاريخ_الإدخال')
                            ->label('تاريخ الإدخال')
                            ->dateTime('Y-m-d H:i'),
                        Infolists\Components\TextEntry::make('الملاحظات')
                            ->label('الملاحظات')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
                Infolists\Components\Section::make('معلومات العميل')
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
                    ->schema([
                        Infolists\Components\TextEntry::make('vehicle.رقم_اللوحة')
                            ->label('رقم اللوحة')
                            ->placeholder('لا توجد مركبة مرتبطة'),
                        Infolists\Components\TextEntry::make('vehicle.نوع_المركبة')
                            ->label('نوع المركبة')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('vehicle.الصنف')
                            ->label('الصنف')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('vehicle.رقم_الهيكل')
                            ->label('رقم الهيكل')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('vehicle.اللون')
                            ->label('اللون')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('vehicle.سنة_الصنع')
                            ->label('سنة الصنع')
                            ->placeholder('—'),
                    ])
                    ->columns(3)
                    ->visible(fn (Transaction $record) => $record->vehicle !== null),
                Infolists\Components\Section::make('معلومات الدفع')
                    ->schema([
                        Infolists\Components\TextEntry::make('payment.المبلغ')
                            ->label('المبلغ المدفوع')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' LYD' : 'لم يتم الدفع')
                            ->placeholder('لم يتم الدفع'),
                        Infolists\Components\TextEntry::make('payment.طريقة_الدفع')
                            ->label('طريقة الدفع')
                            ->badge()
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('payment.رقم_الإيصال')
                            ->label('رقم الإيصال')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('payment.تاريخ_الدفع')
                            ->label('تاريخ الدفع')
                            ->date('Y-m-d')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('payment_difference')
                            ->label('الفرق')
                            ->state(function (Transaction $record): ?float {
                                if (!$record->payment) {
                                    return null;
                                }
                                return $record->السعر - $record->payment->المبلغ;
                            })
                            ->formatStateUsing(fn (?float $state) => $state !== null ? number_format($state, 2) . ' LYD' : '—')
                            ->color(fn (?float $state): ?string => $state !== null && $state < 0 ? 'danger' : ($state !== null && $state > 0 ? 'warning' : 'success'))
                            ->placeholder('—'),
                    ])
                    ->columns(3)
                    ->visible(fn (Transaction $record) => $record->payment !== null),
                Infolists\Components\Section::make('معلومات الفحص')
                    ->schema([
                        Infolists\Components\TextEntry::make('inspection.نوع_الإجراء')
                            ->label('نوع الإجراء')
                            ->badge(),
                        Infolists\Components\TextEntry::make('inspection.النتيجة')
                            ->label('النتيجة')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'صالح' => 'success',
                                'غير صالح' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('inspection.ملاحظات')
                            ->label('ملاحظات')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn (Transaction $record) => $record->inspection !== null),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentRelationManager::class,
            RelationManagers\InspectionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
