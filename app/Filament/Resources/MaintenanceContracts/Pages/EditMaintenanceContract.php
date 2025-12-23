<?php

namespace App\Filament\Resources\MaintenanceContracts\Pages;

use App\Filament\Resources\MaintenanceContracts\MaintenanceContractResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMaintenanceContract extends EditRecord
{
    protected static string $resource = MaintenanceContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
