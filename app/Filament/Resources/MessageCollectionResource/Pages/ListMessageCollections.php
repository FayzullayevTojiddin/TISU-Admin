<?php

namespace App\Filament\Resources\MessageCollectionResource\Pages;

use App\Filament\Resources\MessageCollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMessageCollections extends ListRecords
{
    protected static string $resource = MessageCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
