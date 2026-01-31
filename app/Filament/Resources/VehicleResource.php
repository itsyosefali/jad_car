<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'السيارات';

    protected static ?string $modelLabel = 'مركبة';

    protected static ?string $pluralModelLabel = 'السيارات';

    protected static ?string $navigationGroup = 'الأساسيات';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المركبة')
                    ->schema([
                        Forms\Components\TextInput::make('رقم_الهيكل')
                            ->label('رقم الهيكل')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('رقم_اللوحة')
                            ->label('رقم اللوحة')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('نوع_المركبة')
                            ->label('نوع المركبة (العلامة التجارية)')
                            ->placeholder('مثال: BMW، هونداي، تويوتا، مرسيدس، إلخ')
                            ->maxLength(255),
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
                            ->label('اللون')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('سنة_الصنع')
                            ->label('سنة الصنع')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(now()->year + 1),
                        Forms\Components\Textarea::make('ملاحظات')
                            ->label('ملاحظات')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('رقم_الهيكل')
                    ->label('رقم الهيكل')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('رقم_اللوحة')
                    ->label('رقم اللوحة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('نوع_المركبة')
                    ->label('نوع المركبة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('الصنف')
                    ->label('الصنف')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('اللون')
                    ->label('اللون')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('سنة_الصنع')
                    ->label('سنة الصنع')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('transactions_count')
                    ->label('عدد المعاملات')
                    ->counts('transactions'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('الصنف')
                    ->label('الصنف')
                    ->options([
                        'سيارة' => 'سيارة',
                        'شاحنة' => 'شاحنة',
                        'دراجة' => 'دراجة',
                        'آلية' => 'آلية',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
