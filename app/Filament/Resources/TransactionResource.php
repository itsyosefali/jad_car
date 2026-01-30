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
use Illuminate\Contracts\View\View;

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
                    ->icon('heroicon-o-document-text')
                    ->description('المعلومات الأساسية للمعاملة')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('الرقم_المرجعي')
                                    ->label('الرقم المرجعي')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(1),
                                Forms\Components\Select::make('الحالة')
                                    ->label('الحالة')
                                    ->required()
                                    ->options([
                                        'مسودة' => 'مسودة',
                                        'مكتملة' => 'مكتملة',
                                        'ملغاة' => 'ملغاة',
                                    ])
                                    ->default('مسودة')
                                    ->columnSpan(1),
                                Forms\Components\DatePicker::make('تاريخ_المعاملة')
                                    ->label('تاريخ المعاملة')
                                    ->required()
                                    ->default(now())
                                    ->columnSpan(1),
                            ]),
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
                                return \App\Models\Vehicle::query()
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
                            ->nullable()
                            ->createOptionUsing(function (array $data) {
                                return \App\Models\Vehicle::create($data)->id;
                            })
                            ->createOptionForm([
                                Forms\Components\TextInput::make('رقم_الهيكل')
                                    ->label('رقم الهيكل')
                                    ->required()
                                    ->unique(),
                                Forms\Components\TextInput::make('رقم_اللوحة')
                                    ->label('رقم اللوحة'),
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
                                Forms\Components\TextInput::make('اللون')
                                    ->label('اللون'),
                                Forms\Components\TextInput::make('سنة_الصنع')
                                    ->label('سنة الصنع')
                                    ->numeric(),
                            ]),
                        Forms\Components\Select::make('نوع_المعاملة')
                            ->label('نوع المعاملة')
                            ->options([
                                'تسجيل' => 'تسجيل',
                                'تأمين' => 'تأمين',
                                'تجديد' => 'تجديد',
                                'فحص' => 'فحص',
                            ])
                            ->placeholder('اختياري - سيتم تحديده من الخدمات المختارة'),
                        Forms\Components\TextInput::make('السعر')
                            ->label('السعر الإجمالي')
                            ->required(fn ($record) => ! $record || $record->items()->count() === 0)
                            ->numeric()
                            ->prefix('LYD')
                            ->default(0)
                            ->disabled(fn ($record) => ($record && $record->isCompleted()) || ($record && $record->items()->count() > 0))
                            ->helperText(fn ($record) => $record && $record->items()->count() > 0 ? 'السعر محسوب تلقائياً من عناصر المعاملة' : 'أدخل السعر يدوياً أو أضف خدمات من القائمة أدناه'),
                        Forms\Components\Textarea::make('الملاحظات')
                            ->label('الملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('تفاصيل المعاملة (الخدمات)')
                    ->icon('heroicon-o-shopping-cart')
                    ->description('اختر الخدمات من الكتالوج وأضف الكميات المطلوبة')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('عناصر المعاملة')
                            ->relationship('items')
                            ->disabled(fn ($record) => $record && $record->isCompleted())
                            ->schema([
                                Forms\Components\Grid::make(5)
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
                                            ->columnSpan(2)
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                if ($state) {
                                                    $service = \App\Models\Service::find($state);
                                                    if ($service) {
                                                        $set('التكلفة', $service->التكلفة);
                                                        $set('اسم_الخدمة', $service->اسم_الخدمة);
                                                        $quantity = $get('الكمية') ?? 1;
                                                        $set('total', $service->سعر_البيع * $quantity);
                                                    }
                                                }
                                            }),
                                        Forms\Components\TextInput::make('الكمية')
                                            ->label('الكمية')
                                            ->required()
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->live()
                                            ->columnSpan(1)
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                $serviceId = $get('service_id');
                                                if ($serviceId) {
                                                    $service = \App\Models\Service::find($serviceId);
                                                    if ($service) {
                                                        $set('total', $service->سعر_البيع * $state);
                                                    }
                                                }
                                            }),
                                        Forms\Components\Placeholder::make('selling_price_display')
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
                                            ->extraAttributes(['class' => 'font-semibold text-primary-600'])
                                            ->columnSpan(1)
                                            ->visible(fn (Forms\Get $get) => $get('service_id')),
                                        Forms\Components\Placeholder::make('total')
                                            ->label('الإجمالي')
                                            ->content(function (Forms\Get $get) {
                                                $serviceId = $get('service_id');
                                                $quantity = $get('الكمية') ?? 1;
                                                if ($serviceId) {
                                                    $service = \App\Models\Service::find($serviceId);
                                                    if ($service) {
                                                        return number_format($service->سعر_البيع * $quantity, 2).' LYD';
                                                    }
                                                }

                                                return '0.00 LYD';
                                            })
                                            ->extraAttributes(['class' => 'font-bold text-success-600 text-lg'])
                                            ->columnSpan(1),
                                    ]),
                                Forms\Components\Grid::make(3)
                                    ->schema([
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
                                        Forms\Components\Placeholder::make('profit_display')
                                            ->label('الربح')
                                            ->content(function (Forms\Get $get) {
                                                $serviceId = $get('service_id');
                                                $quantity = $get('الكمية') ?? 1;
                                                if ($serviceId) {
                                                    $service = \App\Models\Service::find($serviceId);
                                                    if ($service) {
                                                        $profit = ($service->سعر_البيع - $service->التكلفة) * $quantity;

                                                        return number_format($profit, 2).' LYD';
                                                    }
                                                }

                                                return '—';
                                            })
                                            ->extraAttributes(['class' => 'font-semibold text-success-600'])
                                            ->visible(fn (Forms\Get $get) => $get('service_id')),
                                    ]),
                                Forms\Components\Textarea::make('الملاحظات')
                                    ->label('ملاحظات')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('إضافة خدمة')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                if (isset($state['service_id'])) {
                                    $service = \App\Models\Service::find($state['service_id']);
                                    if ($service) {
                                        $quantity = $state['الكمية'] ?? 1;

                                        return $service->اسم_الخدمة.' (×'.$quantity.')';
                                    }
                                }

                                return $state['اسم_الخدمة'] ?? 'خدمة جديدة';
                            })
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                // Calculate total from all items using selling prices
                                $items = $get('items') ?? [];
                                $total = 0;
                                foreach ($items as $item) {
                                    if (isset($item['service_id']) && isset($item['الكمية'])) {
                                        $service = \App\Models\Service::find($item['service_id']);
                                        if ($service) {
                                            $total += ($service->سعر_البيع * $item['الكمية']);
                                        }
                                    }
                                }
                                $set('السعر', $total);
                            }),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Placeholder::make('items_count')
                                    ->label('عدد الخدمات')
                                    ->content(function (Forms\Get $get) {
                                        $count = count($get('items') ?? []);

                                        return $count.' خدمة';
                                    })
                                    ->extraAttributes(['class' => 'text-2xl font-bold text-primary-600'])
                                    ->visible(fn (Forms\Get $get) => count($get('items') ?? []) > 0),
                                Forms\Components\Placeholder::make('total_summary')
                                    ->label('المجموع الكلي')
                                    ->content(function (Forms\Get $get) {
                                        $items = $get('items') ?? [];
                                        $total = 0;
                                        foreach ($items as $item) {
                                            if (isset($item['service_id']) && isset($item['الكمية'])) {
                                                $service = \App\Models\Service::find($item['service_id']);
                                                if ($service) {
                                                    $total += ($service->سعر_البيع * $item['الكمية']);
                                                }
                                            }
                                        }

                                        return number_format($total, 2).' LYD';
                                    })
                                    ->extraAttributes(['class' => 'text-2xl font-bold text-success-600'])
                                    ->helperText('إجمالي المبلغ')
                                    ->visible(fn (Forms\Get $get) => count($get('items') ?? []) > 0),
                                Forms\Components\Placeholder::make('profit_summary')
                                    ->label('إجمالي الربح')
                                    ->content(function (Forms\Get $get) {
                                        $items = $get('items') ?? [];
                                        $totalProfit = 0;
                                        foreach ($items as $item) {
                                            if (isset($item['service_id']) && isset($item['الكمية'])) {
                                                $service = \App\Models\Service::find($item['service_id']);
                                                if ($service) {
                                                    $quantity = $item['الكمية'] ?? 1;
                                                    $totalProfit += (($service->سعر_البيع - $service->التكلفة) * $quantity);
                                                }
                                            }
                                        }

                                        return number_format($totalProfit, 2).' LYD';
                                    })
                                    ->extraAttributes(['class' => 'text-2xl font-bold text-success-600'])
                                    ->helperText(function (Forms\Get $get) {
                                        $items = $get('items') ?? [];
                                        $totalCost = 0;
                                        foreach ($items as $item) {
                                            if (isset($item['service_id']) && isset($item['الكمية'])) {
                                                $service = \App\Models\Service::find($item['service_id']);
                                                if ($service) {
                                                    $quantity = $item['الكمية'] ?? 1;
                                                    $totalCost += ($service->التكلفة * $quantity);
                                                }
                                            }
                                        }

                                        return 'من إجمالي تكلفة: '.number_format($totalCost, 2).' LYD';
                                    })
                                    ->visible(fn (Forms\Get $get) => count($get('items') ?? []) > 0),
                            ])
                            ->visible(fn (Forms\Get $get) => count($get('items') ?? []) > 0),
                    ])
                    ->collapsible()
                    ->collapsed(false),
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
                    ->color('primary')
                    ->icon('heroicon-o-hashtag'),
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
                Tables\Columns\TextColumn::make('vehicle.رقم_اللوحة')
                    ->label('رقم اللوحة')
                    ->searchable()
                    ->default('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('نوع_المعاملة')
                    ->label('نوع المعاملة')
                    ->searchable()
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'تسجيل' => 'heroicon-o-document-plus',
                        'تأمين' => 'heroicon-o-shield-check',
                        'تجديد' => 'heroicon-o-arrow-path',
                        'فحص' => 'heroicon-o-clipboard-document-check',
                        default => 'heroicon-o-document',
                    }),
                Tables\Columns\TextColumn::make('الحالة')
                    ->label('الحالة')
                    ->searchable()
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'مكتملة' => 'heroicon-o-check-circle',
                        'مسودة' => 'heroicon-o-clock',
                        'ملغاة' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'مكتملة' => 'success',
                        'مسودة' => 'warning',
                        'ملغاة' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('السعر')
                    ->label('السعر')
                    ->formatStateUsing(fn ($state) => '<span class="font-semibold">'.number_format($state, 2).' LYD</span>')
                    ->html()
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('تاريخ_المعاملة')
                    ->label('تاريخ المعاملة')
                    ->date('Y-m-d')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
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
                    ->visible(fn (Transaction $record) => ! $record->isCompleted()),
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
                    ->icon('heroicon-o-document-text')
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
                            ->label('السعر الإجمالي')
                            ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                            ->helperText(fn (Transaction $record) => $record->items()->count() > 0 ? 'محسوب من '.$record->items()->count().' عنصر' : null)
                            ->weight('bold')
                            ->size('lg'),
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
                        Infolists\Components\TextEntry::make('vehicle.رقم_اللوحة')
                            ->label('رقم اللوحة')
                            ->formatStateUsing(fn ($state, $record) => $state ?? 'لم يتم التسجيل بعد')
                            ->placeholder('لم يتم التسجيل بعد'),
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
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Infolists\Components\TextEntry::make('payment_status')
                            ->label('حالة الدفع')
                            ->badge()
                            ->formatStateUsing(fn (Transaction $record) => match ($record->payment_status) {
                                'paid' => 'مدفوع بالكامل',
                                'partial' => 'مدفوع جزئياً',
                                'unpaid' => 'غير مدفوع',
                                default => 'غير محدد',
                            })
                            ->color(fn (Transaction $record) => match ($record->payment_status) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                'unpaid' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('total_paid')
                            ->label('المبلغ المدفوع')
                            ->formatStateUsing(fn (Transaction $record) => number_format($record->total_paid, 2).' LYD'),
                        Infolists\Components\TextEntry::make('remaining_amount')
                            ->label('المبلغ المتبقي')
                            ->formatStateUsing(fn (Transaction $record) => number_format($record->remaining_amount, 2).' LYD')
                            ->color(fn (Transaction $record) => $record->remaining_amount > 0 ? 'warning' : 'success')
                            ->visible(fn (Transaction $record) => $record->remaining_amount > 0),
                        Infolists\Components\TextEntry::make('payments_count')
                            ->label('عدد المدفوعات')
                            ->formatStateUsing(fn (Transaction $record) => $record->payments()->count().' دفعة')
                            ->visible(fn (Transaction $record) => $record->hasPayment()),
                    ])
                    ->columns(3)
                    ->visible(fn (Transaction $record) => $record->isCompleted()),
                Infolists\Components\Section::make('تفاصيل المعاملة')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('عناصر المعاملة')
                            ->schema([
                                Infolists\Components\TextEntry::make('service.اسم_الخدمة')
                                    ->label('اسم الخدمة')
                                    ->placeholder(fn ($record) => $record->اسم_الخدمة ?? '—'),
                                Infolists\Components\TextEntry::make('service.التكلفة')
                                    ->label('التكلفة')
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' LYD' : '—')
                                    ->placeholder('—'),
                                Infolists\Components\TextEntry::make('service.سعر_البيع')
                                    ->label('سعر البيع')
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' LYD' : '—')
                                    ->placeholder('—'),
                                Infolists\Components\TextEntry::make('الكمية')
                                    ->label('الكمية'),
                                Infolists\Components\TextEntry::make('total')
                                    ->label('الإجمالي')
                                    ->formatStateUsing(fn ($record) => number_format($record->total, 2).' LYD'),
                                Infolists\Components\TextEntry::make('profit')
                                    ->label('الربح')
                                    ->state(function ($record) {
                                        if ($record->service) {
                                            return ($record->service->سعر_البيع - $record->service->التكلفة) * $record->الكمية;
                                        }

                                        return null;
                                    })
                                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state, 2).' LYD' : '—')
                                    ->placeholder('—'),
                                Infolists\Components\TextEntry::make('الملاحظات')
                                    ->label('الملاحظات')
                                    ->placeholder('لا توجد ملاحظات')
                                    ->columnSpanFull(),
                            ])
                            ->columns(5),
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('items_count')
                                    ->label('عدد الخدمات')
                                    ->state(fn (Transaction $record) => $record->items()->count())
                                    ->formatStateUsing(fn ($state) => '<span class="text-xl font-bold text-primary-600">'.$state.'</span>')
                                    ->html(),
                                Infolists\Components\TextEntry::make('total_profit')
                                    ->label('إجمالي الربح')
                                    ->state(function (Transaction $record) {
                                        $totalProfit = 0;
                                        foreach ($record->items as $item) {
                                            if ($item->service) {
                                                $totalProfit += (($item->service->سعر_البيع - $item->service->التكلفة) * $item->الكمية);
                                            }
                                        }

                                        return $totalProfit;
                                    })
                                    ->formatStateUsing(fn ($state) => '<span class="text-xl font-bold text-success-600">'.number_format($state, 2).' LYD</span>')
                                    ->html(),
                                Infolists\Components\TextEntry::make('total_cost')
                                    ->label('إجمالي التكلفة')
                                    ->state(function (Transaction $record) {
                                        $totalCost = 0;
                                        foreach ($record->items as $item) {
                                            if ($item->service) {
                                                $totalCost += ($item->service->التكلفة * $item->الكمية);
                                            }
                                        }

                                        return $totalCost;
                                    })
                                    ->formatStateUsing(fn ($state) => '<span class="text-lg font-semibold text-gray-600">'.number_format($state, 2).' LYD</span>')
                                    ->html(),
                            ])
                            ->visible(fn (Transaction $record) => $record->items()->count() > 0),
                    ])
                    ->visible(fn (Transaction $record) => $record->items()->count() > 0),
                Infolists\Components\Section::make('معلومات الفحص')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Infolists\Components\TextEntry::make('inspection.رقم_الوثيقة')
                            ->label('رقم الوثيقة')
                            ->placeholder('غير متوفر'),
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
                    ->columns(3)
                    ->visible(fn (Transaction $record) => $record->inspection !== null),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
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
