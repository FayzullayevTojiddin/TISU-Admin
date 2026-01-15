<?php

namespace App\Filament\Resources\LessonResource\RelationManagers;

use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    protected static ?string $recordTitleAttribute = 'student.full_name';

    
    protected function getTableQuery()
    {
        return parent::getTableQuery()
            ->with('student');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->label("O'quvchi")
                    ->relationship('student', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(
                        table: 'attendances',
                        column: 'student_id',
                        ignorable: fn ($record) => $record,
                        modifyRuleUsing: function ($rule, $get, RelationManager $livewire) {
                            return $rule->where('lesson_id', $livewire->ownerRecord->id);
                        }
                    ),

                Forms\Components\Toggle::make('came')
                    ->label('Kelganmi')
                    ->onIcon('heroicon-s-check')
                    ->offIcon('heroicon-s-x-mark')
                    ->default(false)
                    ->inline(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->recordTitleAttribute('student.full_name')
            ->columns([
                Tables\Columns\TextColumn::make('row')
                    ->label('T/R')
                    ->rowIndex()
                    ->sortable(false),

                Tables\Columns\TextColumn::make('student.full_name')
                    ->label("O'quvchi")
                    ->searchable()
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('came')
                    ->label('Davomat')
                    ->formatStateUsing(fn ($state) => $state ? 'Keldi' : 'Kelmadi')
                    ->colors([
                        'success' => fn ($state) => $state === 1 || $state === true,
                        'danger'  => fn ($state) => $state === 0 || $state === false,
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Davomat kiritilgan vaqti')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Yangilangan vaqti')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label("Davomat qo'shish"),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Tahrirlash'),
                Tables\Actions\DeleteAction::make()->label('O‘chirish'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('O‘chirish'),
                ]),
            ]);
    }
}
