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
use Filament\Forms\Components\Toggle;

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

                Toggle::make('status')
                    ->label('Faol holat')
                    ->default(true)
                    ->helperText('O‘qituvchi tizimda faol yoki nofaol holatda bo‘lishi')
                    ->inline(false),
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

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => fn ($state) => $state === true,
                        'danger'  => fn ($state) => $state === false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Holati')
                    ->options([
                        '1' => 'Faol',
                        '0' => 'Nofaol',
                    ])
                    ->placeholder('Barchasi')
                    ->query(function ($query, array $data) {
                        if ($data['value'] === null) {
                            return $query;
                        }

                        return $query->where('status', $data['value']);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button(),
                Tables\Actions\DeleteAction::make()->button(),
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