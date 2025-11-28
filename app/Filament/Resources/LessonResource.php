<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonResource\Pages;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Models\Group;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;
use App\Filament\Resources\LessonResource\RelationManagers\AttendancesRelationManager;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static ?string $navigationGroup = 'Davomatlar';
    protected static ?string $navigationLabel = 'Darslar monitoringi';
    protected static ?string $modelLabel = "Darslar ro'yxati";
    protected static ?string $pluralModelLabel = "Darslar";

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Placeholder::make('image_preview')
                            ->label('Rasm')
                            ->content(function ($get, $record) {
                                if (empty($record?->image)) {
                                    return '';
                                }

                                $imageUrl = $record->image;
                                if (! str_starts_with($imageUrl, 'http')) {
                                    $imageUrl = Storage::url($imageUrl);
                                }

                                $html = '
                                <div style="display:flex; justify-content:center;">
                                    <div style="
                                        width: 100%;
                                        max-width: 900px;
                                        aspect-ratio: 16/9;
                                        overflow: hidden;
                                        border-radius: 10px;
                                    ">
                                        <img src="' . e($imageUrl) . '" 
                                            style="width:100%; height:100%; object-fit:cover;" 
                                            alt="Lesson image" />
                                    </div>
                                </div>';

                                return new HtmlString($html);
                            })
                            ->visible(fn ($get, $record) => (bool) ($record?->image ?? false))
                    ])
                    ->visible(fn ($get, $record) => (bool) ($record?->image ?? false)),

                Grid::make(3)
                    ->schema([
                        Card::make()
                            ->schema([
                                Placeholder::make('teacher_name')
                                    ->label("O'qituvchi")
                                    ->content(fn () => auth()->check() ? '-' : '-') // placeholder for layout
                                    ->content(fn ($get, $record) => $record?->teacher?->full_name ?? '-'),
                            ])
                            ->columnSpan(1),

                        Card::make()
                            ->schema([
                                DatePicker::make('date')
                                    ->label('Sana')
                                    ->disabled()
                                    ->dehydrated(false),
                            ])
                            ->columnSpan(1),

                        Card::make()
                            ->schema([
                                TextInput::make('details.subject_name')
                                    ->label('Fan nomi')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('-'),
                            ])
                            ->columnSpan(1),

                        Card::make()
                            ->schema([
                                Placeholder::make('group_name')
                                    ->label('Guruh')
                                    ->content(fn ($get, $record) => $record?->group?->name ?? '-'),
                            ])
                            ->columnSpan(1),

                        Card::make()
                            ->schema([
                                Placeholder::make('room_id')
                                    ->label('Xona')
                                    ->content(fn ($get, $record) => $record?->room?->name ?? '-'),
                            ])
                            ->columnSpan(1),

                        Card::make()
                            ->schema([
                                TextInput::make('details.time_at')
                                    ->label('Vaqt')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('09:00 - 10:30'),
                            ])
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                Card::make()
                    ->schema([
                        Placeholder::make('meta')
                            ->label("Davomat statistikasi")
                            ->content(function ($get, $record) {
                                if (! $record) {
                                    return '-';
                                }

                                $total = $record->attendances()->count();
                                $came = $record->attendances()->where('came', true)->count();
                                $notCame = $record->attendances()->where('came', false)->count();

                                $html = '
                                <div style="
                                    width: 100%;
                                    display: flex;
                                    justify-content: center;
                                    margin-top: 8px;
                                ">
                                    <div style="
                                        width: 100%;
                                        max-width: 1200px;
                                        background: transparent;
                                        padding: 12px 18px;
                                        border-radius: 10px;
                                        display: flex;
                                        gap: 14px;
                                        align-items: center;
                                        justify-content: flex-start;
                                        flex-wrap: wrap;
                                    ">
                                        <div style="
                                            display:flex;
                                            align-items:center;
                                            gap:10px;
                                            min-width: 220px;
                                        ">
                                            <div style="
                                                font-size:13px;
                                                color: #94a3b8;
                                            ">Umumiy</div>
                                            <div style="
                                                padding:10px 18px;
                                                background: linear-gradient(90deg,#06b6d4,#0ea5e9);
                                                color: #fff;
                                                border-radius: 12px;
                                                font-weight:600;
                                                font-size:16px;
                                                box-shadow: 0 4px 10px rgba(14,165,233,0.12);
                                            ">' . $total . ' ta</div>
                                        </div>

                                        <div style="
                                            display:flex;
                                            align-items:center;
                                            gap:10px;
                                            min-width: 200px;
                                        ">
                                            <div style="
                                                font-size:13px;
                                                color: #94a3b8;
                                            ">Keldi</div>
                                            <div style="
                                                padding:10px 18px;
                                                background: linear-gradient(90deg,#10b981,#34d399);
                                                color:#fff;
                                                border-radius:12px;
                                                font-weight:600;
                                                font-size:16px;
                                                box-shadow: 0 4px 10px rgba(16,185,129,0.12);
                                            ">' . $came . ' ta</div>
                                        </div>

                                        <div style="
                                            display:flex;
                                            align-items:center;
                                            gap:10px;
                                            min-width: 200px;
                                        ">
                                            <div style="
                                                font-size:13px;
                                                color: #94a3b8;
                                            ">Kelmadi</div>
                                            <div style="
                                                padding:10px 18px;
                                                background: linear-gradient(90deg,#ef4444,#fb7185);
                                                color:#fff;
                                                border-radius:12px;
                                                font-weight:600;
                                                font-size:16px;
                                                box-shadow: 0 4px 10px rgba(239,68,68,0.12);
                                            ">' . $notCame . ' ta</div>
                                        </div>

                                        <!-- optional: percent display -->
                                        <div style="margin-left:auto; min-width:160px; text-align:right;">
                                            <div style="font-size:13px; color:#94a3b8;">Faoliyat</div>
                                            <div style="font-weight:700; color:#e2e8f0; font-size:15px;">
                                                ' . ($total > 0 ? round($came / $total * 100) . '%' : '0%') . ' kelgan
                                            </div>
                                        </div>
                                    </div>
                                </div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('details.fakultet')
                    ->label('Fakultet')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('group.name')
                    ->label('Guruh')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('room.name')
                    ->label('Xona')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Sana')
                    ->date()
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
                    ])
                    ->sortable(),

                BadgeColumn::make('kelmadi')
                    ->label('Kelmadi')
                    ->getStateUsing(fn ($record) => $record?->attendances()->where('came', false)->count() ?? 0)
                    ->colors([
                        'danger' => fn ($state) => $state > 0,
                    ])
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group_id')
                    ->label('Guruh bo‘yicha')
                    ->options(fn () => Group::pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label("O'qituvchi bo'yicha")
                    ->options(fn () => Teacher::pluck('full_name', 'id')->toArray()),
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
            AttendancesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit'   => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}