<?php

namespace App\Filament\Resources\RoomResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $recordTitleAttribute = 'details.title';

    protected static ?string $title = 'Xonadagi darslar';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('teacher_id')
                    ->label("O'qituvchi")
                    ->relationship('teacher', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required(),

                DatePicker::make('date')
                    ->label('Sana')
                    ->required(),

                FileUpload::make('image')
                    ->label('Rasm (ixtiyoriy)')
                    ->image()
                    ->directory('lessons')
                    ->nullable(),

                TextInput::make('details.title')
                    ->label('Sarlavha')
                    ->maxLength(255)
                    ->required(),

                TextInput::make('details.fakultet')
                    ->label('Fakultet')
                    ->maxLength(255)
                    ->nullable(),

                TextInput::make('details.subject_name')
                    ->label('Fan nomi')
                    ->maxLength(255)
                    ->required(),

                TextInput::make('details.time_at')
                    ->label('Vaqt')
                    ->maxLength(255)
                    ->required()
                    ->helperText('Masalan: 09:00 - 10:30'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('details.title')
            ->columns([
                TextColumn::make('row')
                    ->label('T/R')
                    ->rowIndex(),

                TextColumn::make('teacher.full_name')
                    ->label("O'qituvchi")
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('date')
                    ->label('Sana')
                    ->date()
                    ->sortable(),

                TextColumn::make('details.subject_name')
                    ->label('Fan nomi')
                    ->formatStateUsing(fn ($state) => $state ?? '-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('attendances_total')
                    ->label('Umumiy Soni')
                    ->getStateUsing(fn ($record) => $record?->attendances()->count() ?? 0)
                    ->sortable(),

                BadgeColumn::make('keldi')
                    ->label('Keldi')
                    ->getStateUsing(fn ($record) => $record?->attendances()->where('came', true)->count() ?? 0)
                    ->colors([
                        'success' => fn ($state) => $state > 0,
                    ]),

                BadgeColumn::make('kelmadi')
                    ->label('Kelmadi')
                    ->getStateUsing(fn ($record) => $record?->attendances()->where('came', false)->count() ?? 0)
                    ->colors([
                        'danger' => fn ($state) => $state > 0,
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label("Dars qo'shish"),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label("Tahrirlash"),
                Tables\Actions\DeleteAction::make()->label("O'chirish"),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label("O‘chirish"),
                ]),
            ]);
    }
}
