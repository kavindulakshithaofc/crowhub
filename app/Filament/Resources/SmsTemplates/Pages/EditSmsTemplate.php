<?php

namespace App\Filament\Resources\SmsTemplates\Pages;

use App\Filament\Resources\SmsTemplates\SmsTemplateResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSmsTemplate extends EditRecord
{
    protected static string $resource = SmsTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reset_to_default')
                ->label('Reset to default')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->resetToDefault();
                    $this->fillForm();

                    Notification::make()
                        ->title('Template reset')
                        ->success()
                        ->send();
                }),
        ];
    }
}
