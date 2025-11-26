<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageCollectionResource\Pages;
use App\Filament\Resources\MessageCollectionResource\RelationManagers;
use App\Models\MessageCollection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Facades\URL;
use Filament\Forms\Components\Grid;
use Illuminate\Support\HtmlString;
use App\Filament\Resources\MessageCollectionResoruceResource\RelationManagers\MessagesRelationManager;
use Filament\Resources\Pages\CreateRecord;

class MessageCollectionResource extends Resource
{
    protected static ?string $model = MessageCollection::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationLabel = 'Xabar to‘plamlari';

    protected static ?string $pluralModelLabel = 'Xabar to‘plamlari';

    protected static ?string $modelLabel = 'Xabar to‘plami';

    protected static ?string $navigationGroup = 'Xabarlar boshqaruvi';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Template tanlash')
                        ->schema([
                            Select::make('template_id')
                                ->label('Shablon')
                                ->relationship('template', 'title')
                                ->required()
                                ->disabled(fn ($get, $livewire) => ! ($livewire instanceof CreateRecord)),

                            Placeholder::make('template_preview')
                                ->label('Template oldindan ko\'rish')
                                ->content(fn ($get) => $get('template.title') ? nl2br(e($get('template.message') ?? '')) : 'Template tanlang')
                                ->columnSpan('full'),
                        ]),

                    Step::make('Ma\'lumotlar')
                        ->schema([
                            TextInput::make('title')
                                ->label('Sarlavha')
                                ->required()
                                ->columnSpan('full'),

                            Textarea::make('description')
                                ->label('Izoh')
                                ->rows(4)
                                ->columnSpan('full'),
                        ]),

                Step::make('Excel yuklash')
                    ->schema([
                        Grid::make()
                            ->columns(4)
                            ->schema([
                                Placeholder::make('example_download')
                                    ->label('')
                                    ->content(function ($get) {
                                        $templateId = $get('template_id');
                                        if (! $templateId) {
                                            return new HtmlString('<div class="h-16 flex items-center">Avval Template tanlang</div>');
                                        }

                                        $url = route('examples.template_excel', ['template' => $templateId]);

                                        $html = '<div class="h-16 flex items-center">'
                                            . '<a href="' . e($url) . '" target="_blank"'
                                            . ' class="inline-flex items-center justify-center w-full h-14 gap-2 px-4 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white">'
                                            . '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">'
                                            . '<path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2" />'
                                            . '<path stroke-linecap="round" stroke-linejoin="round" d="M7 10l5 5 5-5M12 15V3" />'
                                            . '</svg>'
                                            . '<span class="font-medium">Example Excel yuklab olish</span>'
                                            . '</a>'
                                            . '</div>';

                                        return new HtmlString($html);
                                    })
                                    ->columnSpan(1),

                                FileUpload::make('excel_file')
                                    ->label('Excel faylni yuklash (ixtiyoriy)')
                                    ->acceptedFileTypes([
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'application/vnd.ms-excel',
                                    ])
                                    ->disk('local')
                                    ->directory('imports')
                                    ->getUploadedFileNameForStorageUsing(function ($file) {
                                        return uniqid() . '_' . $file->getClientOriginalName();
                                    })
                                    ->maxSize(10240)
                                    ->hint('Template example ga mos Excel file yuklang. Majburiy emas.')
                                    ->extraAttributes(['class' => 'custom-excel-upload'])
                                    ->columnSpan(3)
                            ]),
                    ]),
                ])->skippable(false),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Sarlavha')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('template.title')
                    ->label('Shablon')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('messages_count')
                    ->label('Umumiy TA')
                    ->counts('messages')
                    ->sortable(),

                Tables\Columns\TextColumn::make('success_count')
                    ->label('✅')
                    ->sortable(),

                Tables\Columns\TextColumn::make('failed_count')
                    ->label('❌')
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Holat')
                    ->formatStateUsing(function ($state) {
                        return $state
                            ? 'Yakunlangan'
                            : 'Jarayonda';
                    })
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => $state !== null,
                        'warning' => fn ($state) => $state === null,
                    ])
                    ->sortable(),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label("Ko'rish"),
                    Tables\Actions\EditAction::make()->label("Tahrirlash"),
                ])
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
            MessagesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMessageCollections::route('/'),
            'create' => Pages\CreateMessageCollection::route('/create'),
            'edit' => Pages\EditMessageCollection::route('/{record}/edit'),
        ];
    }
}
