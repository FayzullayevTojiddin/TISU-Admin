<?php

namespace App\Filament\Resources\MessageCollectionResource\Pages;

use App\Filament\Resources\MessageCollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMessageCollection extends EditRecord
{
    protected static string $resource = MessageCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
