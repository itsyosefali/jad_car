<?php

namespace App\Filament\Resources\TransactionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InspectionRelationManager extends RelationManager
{
    protected static string $relationship = 'inspection';

    protected static ?string $title = 'الفحص';

    protected static ?string $modelLabel = 'فحص';

    protected static ?string $pluralModelLabel = 'الفحوصات';

    protected static bool $isLazy = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('رقم_الوثيقة')
                    ->label('رقم الوثيقة')
                    ->maxLength(255),
                Forms\Components\Select::make('نوع_الإجراء')
                    ->label('نوع الإجراء')
                    ->required()
                    ->options([
                        'فحص' => 'فحص',
                        'تجديد' => 'تجديد',
                    ]),
                Forms\Components\Select::make('النتيجة')
                    ->label('النتيجة')
                    ->required()
                    ->options([
                        'صالح' => 'صالح',
                        'غير صالح' => 'غير صالح',
                    ])
                    ->live(),
                Forms\Components\Textarea::make('ملاحظات')
                    ->label('ملاحظات')
                    ->required(fn (Forms\Get $get) => $get('النتيجة') === 'غير صالح')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('نوع_الإجراء')
            ->columns([
                Tables\Columns\TextColumn::make('رقم_الوثيقة')
                    ->label('رقم الوثيقة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('نوع_الإجراء')
                    ->label('نوع الإجراء')
                    ->badge(),
                Tables\Columns\TextColumn::make('النتيجة')
                    ->label('النتيجة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'صالح' => 'success',
                        'غير صالح' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('ملاحظات')
                    ->label('ملاحظات')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->ملاحظات),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الفحص')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn () => in_array($this->getOwnerRecord()->نوع_المعاملة, ['فحص', 'تجديد']))
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
                    ->visible(fn () => in_array($this->getOwnerRecord()->نوع_المعاملة, ['فحص', 'تجديد']))
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
