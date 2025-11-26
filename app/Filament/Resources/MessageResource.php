<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Filament\Resources\MessageResource\RelationManagers;
use App\Models\Message;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\HtmlString;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Xabarlar';
    protected static ?string $pluralLabel = 'Xabarlar';
    protected static ?string $label = 'Xabar';

    protected static ?string $navigationGroup = 'Xabarlar boshqaruvi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Placeholder::make('collection_title')
                    ->label('')
                    ->content(fn ($get) => new HtmlString($get('messageCollection.title') ? '<div class="text-center text-lg font-bold">' . e($get('messageCollection.title')) . '</div>' : '<div class="text-center text-lg font-bold">—</div>'))
                    ->columnSpan('full'),

                TextInput::make('phone')
                    ->label('Telefon raqami')
                    ->disabled()
                    ->columnSpan('full'),

                Card::make()
                    ->schema([
                        Placeholder::make('rendered_message')
                            ->label('Xabar matni')
                            ->content(function ($get) {
                                $templateMessage = $get('messageCollection.template.message') ?? '';

                                if (empty($templateMessage)) {
                                    $recordId = request()->route('record') ?? $get('id') ?? null;
                                    if ($recordId) {
                                        $message = \App\Models\Message::with('messageCollection.template')->find($recordId);
                                        $templateMessage = $message->messageCollection->template->message ?? '';
                                    }
                                }

                                $details = $get('details') ?? [];

                                $rendered = $templateMessage;

                                if (is_array($details) && array_keys($details) === range(0, count($details) - 1)) {
                                    foreach ($details as $item) {
                                        if (is_array($item) && isset($item['key'])) {
                                            $k = $item['key'];
                                            $v = $item['value'] ?? '';
                                            $rendered = str_replace('{' . $k . '}', e((string)$v), $rendered);
                                        }
                                    }
                                } else {
                                    foreach ($details as $k => $v) {
                                        $rendered = str_replace('{' . $k . '}', e((string)$v), $rendered);
                                    }
                                }

                                return new HtmlString('<div class="whitespace-pre-wrap text-sm">' . nl2br($rendered) . '</div>');
                            })
                            ->columnSpan('full'),

                        TextInput::make('status')
                            ->label('Holat')
                            ->disabled(),
                    ])
                    ->columnSpan('full'),
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('messageCollection.title')
                    ->label('Kolleksiya')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Holat')
                    ->formatStateUsing(fn ($state) => [
                        'draft'    => 'Qoralama',
                        'active'   => 'Aktiv',
                        'inactive' => 'NoAktiv',
                    ][$state] ?? $state)
                    ->badge()
                    ->colors([
                        'primary' => 'draft',
                        'success' => 'active',
                        'danger'  => 'inactive',
                    ]),

                TextColumn::make('send_at')
                    ->label('Yetqizilgan vaqti')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Yaratilgan')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListMessages::route('/'),
            'create' => Pages\CreateMessage::route('/create'),
            'edit' => Pages\EditMessage::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
