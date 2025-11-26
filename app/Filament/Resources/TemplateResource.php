<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplateResource\Pages;
use App\Filament\Resources\TemplateResource\RelationManagers;
use App\Models\Template;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Textarea;

class TemplateResource extends Resource
{
    protected static ?string $model = Template::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Shablonlar';
    protected static ?string $pluralLabel = 'Shablonlar';
    protected static ?string $label = 'Shablon';

    protected static ?string $navigationGroup = 'Xabarlar boshqaruvi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Placeholder::make('title_top')
                    ->label('')
                    ->content(fn ($get) => $get('title') ? $get('title') : 'Yangi Template')
                    ->columnSpan('full'),

                Card::make()
                    ->schema([
                        TextInput::make('title')
                            ->label('Sarlavha (Title)')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columnSpan('full'),

                Textarea::make('message')
                    ->label('Xabar (Message)')
                    ->rows(3)
                    ->required()
                    ->maxLength(500)
                    ->columnSpan('full'),

                Repeater::make('variables')
                    ->label("O'zgaruvchilar (Variables)")
                    ->schema([
                        TextInput::make('key')
                            ->label('Kalit (key)')
                            ->required()
                            ->maxLength(191),
                        TextInput::make('value')
                            ->label('Qiymat (value)')
                            ->required()
                            ->maxLength(191),
                    ])
                    ->columns(2)
                    ->createItemButtonLabel("Yangi o'zgaruvchi qo'shish")
                    ->orderable()
                    ->columnSpan('full')
                    ->default([]),

                Select::make('status')
                    ->label('Holat (Status)')
                    ->options([
                        'draft'    => 'Qoralama',
                        'active'   => 'Aktiv',
                        'inactive' => 'NoAktiv',
                    ])
                    ->required()
                    ->default('draft')
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Sarlavha')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Holat')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn ($state) => [
                        'draft'    => 'Qoralama',
                        'active'   => 'Aktiv',
                        'inactive' => 'NoAktiv',
                    ][$state] ?? $state)
                    ->colors([
                        'primary' => 'draft',
                        'success' => 'active',
                        'danger'  => 'inactive',
                    ]),


                TextColumn::make('created_at')
                    ->label('Yaratilgan')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemplates::route('/'),
            'create' => Pages\CreateTemplate::route('/create'),
            'edit' => Pages\EditTemplate::route('/{record}/edit'),
        ];
    }
}
