<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $title = 'Talabalar';

    protected static ?string $recordTitleAttribute = 'full_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('full_name')
                    ->label("F.I.Sh")
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ism Familiya'),

                Forms\Components\TextInput::make('details.JSHSHIR')
                    ->label('JSHSHIR')
                    ->required()
                    ->maxLength(14)
                    ->placeholder('14 raqam')
                    ->rules(['regex:/^\d{14}$/'])
                    ->helperText('14 ta raqamdan iborat bo‘lishi kerak (raqamlar faqat).'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->recordTitleAttribute('full_name')
            ->columns([
                Tables\Columns\TextColumn::make('row')
                    ->label('T/R')
                    ->rowIndex()
                    ->sortable(false),

                Tables\Columns\TextColumn::make('full_name')
                    ->label("F.I.Sh")
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('details.JSHSHIR')
                    ->label('JSHSHIR')
                    ->formatStateUsing(fn ($state) => $state ?? '-')
                    ->searchable()
                    ->sortable()
                    ->toggleable(false)
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Yaratildi')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(false)
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label("Yangi o'quvchi qo'shish"),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Tahrirlash'),
                Tables\Actions\DeleteAction::make()->label('O‘chirish'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('O‘chirish'),
                ]),
            ]);
    }
}