<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CameraResource\Pages;
use App\Models\Camera;
use Filament\Forms;                    // for components like Forms\Components\TextInput
use Filament\Forms\Form;               // <-- correct Form class (Filament\Forms\Form)
use Filament\Resources\Resource;
use Filament\Resources\Table as ResourceTable; // optional alias if you prefer
use Filament\Tables;                   // for Table components
use Filament\Tables\Table;             // <-- correct Table class (Filament\Tables\Table)
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions;
use App\Models\Room;

class CameraResource extends Resource
{
    protected static ?string $model = Camera::class;

    protected static ?string $navigationGroup = 'Boshqaruv';

    protected static ?string $navigationIcon = 'heroicon-o-camera';

    protected static ?string $navigationLabel = 'Kameralar';

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

                        BelongsToSelect::make('room_id')
                            ->label('Xona')
                            ->relationship('room', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('status')
                            ->label('Holati')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'offline' => 'Offline',
                                'error' => 'Error',
                            ])
                            ->default('inactive')
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

                TextColumn::make('room.name')
                    ->label('Xona')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->default('-'),

                BadgeColumn::make('status')
                    ->label('Holati')
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'offline' => 'Offline',
                        'error' => 'Error',
                        default => $state ?? '-',
                    })
                    ->colors([
                        'success' => fn ($state): bool => $state === 'active',
                        'secondary' => fn ($state): bool => $state === 'inactive',
                        'warning' => fn ($state): bool => $state === 'offline',
                        'danger' => fn ($state): bool => $state === 'error',
                    ]),

                TextColumn::make('created_at')
                    ->label('Yaratilgan')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Oxirgi o\'zgartirish')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Holat bo\'yicha')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'offline' => 'Offline',
                        'error' => 'Error',
                    ]),
                // Agar room bo'yicha filter kerak bo'lsa, qo'shish mumkin
                // Tables\Filters\SelectFilter::make('room_id')->relationship('room', 'name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('viewDetails')
                    ->label('Details')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Camera $record): string => route('filament.resources.cameras.edit', ['record' => $record->getKey()])),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCameras::route('/'),
            'create' => Pages\CreateCamera::route('/create'),
            'edit' => Pages\EditCamera::route('/{record}/edit'),
        ];
    }
}