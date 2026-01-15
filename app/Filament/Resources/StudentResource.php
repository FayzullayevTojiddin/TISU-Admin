<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\Student;
use App\Models\Group;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationGroup = "Bo'limlar";
    protected static ?string $navigationLabel = "O'quvchilar";
    protected static ?string $pluralModelLabel = "O'quvchilar";
    protected static ?string $modelLabel = "O'quvchi";

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('group');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('full_name')
                    ->label("F.I.Sh")
                    ->required()
                    ->maxLength(255),

                BelongsToSelect::make('group_id')
                    ->label('Guruh')
                    ->relationship('group', 'name')
                    ->searchable()
                    ->lazy()
                    ->nullable()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Guruh nomi')
                            ->required()
                            ->maxLength(255),
                    ]),

                TextInput::make('details.JSHSHIR')
                    ->label('JSHSHIR')
                    ->required()
                    ->numeric()
                    ->minLength(14)
                    ->maxLength(14)
                    ->rule('regex:/^\d{14}$/')
                    ->helperText('14 ta raqamdan iborat bo‘lishi kerak (faqat raqamlar).'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('full_name')
                    ->label('F.I.Sh')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('group.name')
                    ->label('Guruh')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('details.JSHSHIR')
                    ->label('JSHSHIR')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state ?? '-')
                    ->toggleable()
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Yaratildi')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group_id')
                    ->label('Guruh bo‘yicha')
                    ->options(fn () => 
                        Cache::remember('groups_filter', 3600, fn () =>
                            Group::pluck('name', 'id')->toArray()
                        )
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Tahrirlash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('O‘chirish'),
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}