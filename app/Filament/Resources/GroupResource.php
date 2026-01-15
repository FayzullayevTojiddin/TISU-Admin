<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Models\Group;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;

use Filament\Tables\Columns\TextColumn;

use App\Filament\Resources\GroupResource\RelationManagers\StudentsRelationManager;
use App\Filament\Resources\GroupResource\RelationManagers\LessonsRelationManager;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationGroup = "Bo'limlar";
    protected static ?string $navigationLabel = "Guruhlar";
    protected static ?string $modelLabel = "Guruh";
    protected static ?string $pluralModelLabel = "Guruhlar";

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['students', 'lessons']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    Grid::make(1)->schema([
                        TextInput::make('name')
                            ->label("Guruh nomi")
                            ->placeholder("Masalan: 1-A")
                            ->required()
                            ->maxLength(255)
                            ->unique(table: Group::class, column: 'name', ignorable: fn ($record) => $record)
                            ->autofocus(),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row')
                    ->label('T/R')
                    ->rowIndex()
                    ->sortable(false),

                TextColumn::make('name')
                    ->label('Nomi')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Yaratildi')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('students_count')
                    ->label("O'quvchilar soni")
                    ->counts('students')
                    ->badge()
                    ->icon('heroicon-o-user-group')
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => "{$state} ta")
                    ->sortable(),

                TextColumn::make('lessons_count')
                    ->label("Darslar soni")
                    ->counts('lessons')
                    ->badge()
                    ->icon('heroicon-o-academic-cap')
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => "{$state} ta")
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Tahrirlash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('O\'chirish'),
                ]),
            ])
            ->paginationPageOptions([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
    }

    public static function getRelations(): array
    {
        return [
            StudentsRelationManager::class,
            LessonsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}