<?php

namespace App\Filament\Resources\MessageCollectionResoruceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\Message;


class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    public function form(Form $form): Form
    {
        $owner = $this->getOwnerRecord();
        $template = $owner->template ?? null;
        $variables = $template->variables ?? [];

        $schema = [];

        $schema[] = TextInput::make('phone')
            ->label('Telefon raqami')
            ->required()
            ->columnSpan('full');

        foreach ($variables as $var) {
            $key = $var['key'] ?? null;
            $label = $var['value'] ?? $key;

            if (! $key) {
                continue;
            }

            $schema[] = TextInput::make("details.{$key}")
                ->label($label)
                ->reactive()
                ->debounce(800)
                ->columnSpan('full');
        }

        $schema[] = Placeholder::make('template_message')
            ->label('Shablon matni')
            ->content(function ($get) use ($template, $variables) {
                $msg = $template->message ?? '';

                foreach ($variables as $var) {
                    $k = is_array($var) ? ($var['key'] ?? null) : $var;
                    if (! $k) {
                        continue;
                    }

                    $userValue = $get("details.{$k}") ?? null;

                    if ($userValue && trim($userValue) !== '') {
                        $msg = str_replace('{' . $k . '}', $userValue, $msg);
                    }
                }

                return nl2br(e($msg));
            })
            ->columnSpan('full');

        return $form->schema($schema)->columns(1);
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('phone')->label('Telefon')->sortable(),
                TextColumn::make('details')->label('Details')->formatStateUsing(function ($state) {
                    if (is_array($state)) {
                        $parts = [];
                        foreach ($state as $k => $v) {
                            $parts[] = "{$k}: {$v}";
                        }
                        return implode(' — ', $parts);
                    }
                    return (string) $state;
                })->wrap()->limit(120),
                TextColumn::make('status')->label('Holat')->sortable(),
                TextColumn::make('created_at')->label('Yaratilgan')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label("Ko'rish"),
                    Tables\Actions\EditAction::make()->label("Tahrirlash"),
                    Tables\Actions\DeleteAction::make()->label("O'chirish"),
                ]),
                Tables\Actions\Action::make('send_or_resend')
                    ->button()
                    ->label(fn (Message $record): string => $record->status === 'created' ? 'Yuborish' : 'Qayta yuborish')
                    ->color(fn (Message $record): ?string => $record->status === 'created' ? 'success' : 'secondary')
                    ->icon(fn (Message $record): string => $record->status === 'created' ? 'heroicon-o-paper-airplane' : 'heroicon-o-refresh')
                    ->requiresConfirmation()
                    ->action(function (Message $record): void {
                        try {
                            $record->update(['status' => 'sending']);
                            Log::info("Message #{$record->id} send/resend triggered by Filament.");

                            $record->update(['status' => 'sent']);

                            Notification::make()
                                ->title('Xabar yuborildi')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Log::error('Message send failed: '.$e->getMessage());

                            $record->update(['status' => 'failed']);

                            Notification::make()
                                ->title('Xabar yuborilmadi')
                                ->danger()
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['details']) && is_array($data['details'])) {
            $assoc = [];
            foreach ($data['details'] as $k => $v) {
                if (is_string($k)) {
                    $assoc[$k] = $v;
                } else {
                    $assoc[$k] = $v;
                }
            }
            $data['details'] = $assoc;
        } else {
            $data['details'] = [];
        }
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['details']) && is_array($data['details'])) {
            $assoc = [];
            foreach ($data['details'] as $k => $v) {
                if (is_string($k)) {
                    $assoc[$k] = $v;
                } else {
                    $assoc[$k] = $v;
                }
            }
            $data['details'] = $assoc;
        } else {
            $data['details'] = [];
        }
        return $data;
    }
}