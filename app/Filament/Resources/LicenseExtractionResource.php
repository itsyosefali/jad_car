<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LicenseExtractionResource\Pages;
use App\Filament\Resources\LicenseExtractionResource\RelationManagers;
use App\Models\LicenseExtraction;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LicenseExtractionResource extends Resource
{
    protected static ?string $model = LicenseExtraction::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'استخراج الرخص';

    protected static ?string $modelLabel = 'استخراج رخصة';

    protected static ?string $pluralModelLabel = 'استخراج الرخص';

    protected static ?string $navigationGroup = 'الوثائق';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات استخراج الرخصة')
                    ->icon('heroicon-o-document-duplicate')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('رقم_الرخصة')
                                    ->label('رقم الرخصة')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('التاريخ')
                                    ->label('التاريخ')
                                    ->required()
                                    ->default(now()),
                                Forms\Components\TextInput::make('السعر')
                                    ->label('السعر')
                                    ->required()
                                    ->numeric()
                                    ->prefix('LYD')
                                    ->default(0),
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
                Tables\Columns\TextColumn::make('رقم_الرخصة')
                    ->label('رقم الرخصة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-document-text'),
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
                Tables\Columns\TextColumn::make('التاريخ')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                Tables\Columns\TextColumn::make('السعر')
                    ->label('السعر')
                    ->formatStateUsing(fn ($state) => '<span class="font-semibold">'.number_format($state, 2).' LYD</span>')
                    ->html()
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
                Infolists\Components\Section::make('معلومات استخراج الرخصة')
                    ->icon('heroicon-o-document-duplicate')
                    ->schema([
                        Infolists\Components\TextEntry::make('رقم_الرخصة')
                            ->label('رقم الرخصة')
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('التاريخ')
                            ->label('التاريخ')
                            ->date('Y-m-d'),
                        Infolists\Components\TextEntry::make('السعر')
                            ->label('السعر')
                            ->formatStateUsing(fn ($state) => number_format($state, 2).' LYD')
                            ->weight('bold')
                            ->size('lg'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y-m-d H:i'),
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
                    ->visible(fn (LicenseExtraction $record) => $record->vehicle !== null),
                Infolists\Components\Section::make('ملاحظات')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Infolists\Components\TextEntry::make('الملاحظات')
                            ->label('الملاحظات')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (LicenseExtraction $record) => $record->الملاحظات),
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
            'index' => Pages\ListLicenseExtractions::route('/'),
            'create' => Pages\CreateLicenseExtraction::route('/create'),
            'view' => Pages\ViewLicenseExtraction::route('/{record}'),
            'edit' => Pages\EditLicenseExtraction::route('/{record}/edit'),
        ];
    }
}
