<?php

namespace App\Filament\Resources\MessageCollectionResource\Pages;

use App\Filament\Resources\MessageCollectionResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\ExcelImportService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class CreateMessageCollection extends CreateRecord
{
    protected static string $resource = MessageCollectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Excel file ni alohida saqlash
        if (isset($data['excel_file'])) {
            $this->excelFilePath = $data['excel_file'];
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Saqlangan excel file path dan foydalanish
        $excelFile = $this->excelFilePath ?? $this->record->excel_file ?? null;

        if ($excelFile) {
            try {
                // Use Laravel Storage to get the full path
                $fullPath = Storage::path($excelFile);
                
                // Import messages
                $imported = ExcelImportService::importMessagesFromExcel($this->record, $fullPath);
                
                // Reload messages
                $this->record->load('messages');

                // Show success notification
                Notification::make()
                    ->success()
                    ->title('Import muvaffaqiyatli')
                    ->body("{$imported} ta xabar import qilindi")
                    ->send();

            } catch (\Exception $e) {
                // Show error notification
                Notification::make()
                    ->danger()
                    ->title('Import xatosi')
                    ->body($e->getMessage())
                    ->send();
            }
        }
    }
}