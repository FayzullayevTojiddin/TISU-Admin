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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

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

                                        <div style="margin-left:auto; min-width:160px; text-align:right;">
                                            <div style="font-size:13px; color:#94a3b8;">Faoliyat</div>
                                            <div style="font-weight:700; color:#e2e8f0; font-size:15px;">
                                                ' . ($total > 0 ? round($came / $total * 100) . '%' : '0%') . ' kelgan
                                            </div>
                                        </div>
                                    </div>
                                </div>';

                                return new HtmlString($html);
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
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('details.fakultet')
                    ->label('Fakultet')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->badge()
                    ->color('info'),

                TextColumn::make('group.name')
                    ->label('Guruh')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('room.name')
                    ->label('Xona')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('date')
                    ->label('Sana')
                    ->date('d.m.Y')
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('attendances_total')
                    ->label('Umumiy')
                    ->getStateUsing(fn ($record) => $record?->attendances()->count() ?? 0)
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->sortable(),

                BadgeColumn::make('keldi')
                    ->label('Keldi')
                    ->getStateUsing(fn ($record) => $record?->attendances()->where('came', true)->count() ?? 0)
                    ->colors([
                        'success' => fn ($state) => $state > 0,
                        'gray' => fn ($state) => $state === 0,
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => fn ($state) => $state > 0,
                    ])
                    ->alignCenter()
                    ->sortable(),

                BadgeColumn::make('kelmadi')
                    ->label('Kelmadi')
                    ->getStateUsing(fn ($record) => $record?->attendances()->where('came', false)->count() ?? 0)
                    ->colors([
                        'danger' => fn ($state) => $state > 0,
                        'gray' => fn ($state) => $state === 0,
                    ])
                    ->icons([
                        'heroicon-o-x-circle' => fn ($state) => $state > 0,
                    ])
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                // Fakultet filtri
                SelectFilter::make('fakultet')
                    ->label('🎓 Fakultet')
                    ->options(self::getFakultetOptions())
                    ->placeholder('Barcha fakultetlar')
                    ->query(function (Builder $query, array $data): Builder {
                        if (filled($data['value'])) {
                            return $query->where('details->fakultet', $data['value']);
                        }
                        return $query;
                    })
                    ->native(false)
                    ->columnSpan(1),

                // Guruh filtri
                SelectFilter::make('group_id')
                    ->label('👥 Guruh')
                    ->relationship('group', 'name')
                    ->placeholder('Barcha guruhlar')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->columnSpan(1),

                // O'qituvchi filtri
                SelectFilter::make('teacher_id')
                    ->label('👨‍🏫 O\'qituvchi')
                    ->relationship('teacher', 'full_name')
                    ->placeholder('Barcha o\'qituvchilar')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->columnSpan(1),

                // Sana oralig'i filtri
                Filter::make('date_range')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('date_from')
                                    ->label('📅 Dan')
                                    ->placeholder('Boshlanish sanasi')
                                    ->native(false)
                                    ->displayFormat('d.m.Y'),
                                DatePicker::make('date_to')
                                    ->label('📅 Gacha')
                                    ->placeholder('Tugash sanasi')
                                    ->native(false)
                                    ->displayFormat('d.m.Y'),
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['date_from'] ?? null) {
                            $indicators[] = 'Sana dan: ' . \Carbon\Carbon::parse($data['date_from'])->format('d.m.Y');
                        }
                        if ($data['date_to'] ?? null) {
                            $indicators[] = 'Sana gacha: ' . \Carbon\Carbon::parse($data['date_to'])->format('d.m.Y');
                        }
                        return $indicators;
                    })
                    ->columnSpan(1),
            ], layout: Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(4)
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ko\'rish')
                    ->color('info')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->label('Tahrirlash')
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('O\'chirish')
                        ->icon('heroicon-o-trash'),
                ]),
            ])
            ->defaultSort('date', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    /**
     * Fakultet opsiyalarini qaytarish uchun yordamchi metod
     */
    protected static function getFakultetOptions(): array
    {
        return Lesson::query()
            ->select('details')
            ->get()
            ->pluck('details.fakultet')
            ->filter()
            ->unique()
            ->sort()
            ->mapWithKeys(fn ($fakultet) => [$fakultet => $fakultet])
            ->toArray();
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