<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Models\Room;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\RoomResource\RelationManagers\LessonsRelationManager;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationGroup = 'Boshqaruv';

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Xonalar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nomi')
                            ->required()
                            ->maxLength(255),

                        Select::make('fakultet')
                            ->label('Fakultet')
                            ->options(\App\FakultetEnum::options())
                            ->required()
                            ->searchable(),

                        Select::make('build')
                            ->label('Bino')
                            ->options(\App\BuildEnum::options())
                            ->required()
                            ->searchable(),
                            
                        Select::make('status')
                            ->label('Holati')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'archived' => 'Archived',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Nomi')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('fakultet')
                    ->label('Fakultet')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                BadgeColumn::make('status')
                    ->label('Holati')
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'archived' => 'Archived',
                        default => $state ?? '-',
                    })
                    ->colors([
                        'success' => fn ($state): bool => $state === 'active',
                        'secondary' => fn ($state): bool => $state === 'inactive',
                        'warning' => fn ($state): bool => $state === 'archived',
                    ]),

                TextColumn::make('created_at')
                    ->label('Yaratilgan')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Holat bo\'yicha')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'archived' => 'Archived',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            LessonsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
