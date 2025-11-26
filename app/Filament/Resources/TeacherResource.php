<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationGroup = "Bo'limlar";
    protected static ?string $navigationLabel = "O'qituvchilar";
    protected static ?string $pluralModelLabel = "O'qituvchilar";
    protected static ?string $modelLabel = "O'qituvchi";

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('full_name')
                    ->label('Full name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('login')
                    ->label('Login')
                    ->required()
                    ->maxLength(255)
                    ->unique(Teacher::class, 'login', fn ($record) => $record),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof Pages\CreateTeacher)
                    ->dehydrated(fn ($state) => filled($state))
                    ->minLength(6)
                    ->afterStateHydrated(function (Forms\Get $get, Forms\Set $set) {
                        $set('password', '');
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Full Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('login')
                    ->label('Login')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => fn ($state) => $state === true,
                        'danger'  => fn ($state) => $state === false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}