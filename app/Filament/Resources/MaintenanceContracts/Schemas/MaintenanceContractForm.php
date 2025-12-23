<?php

namespace App\Filament\Resources\MaintenanceContracts\Schemas;

use App\Filament\Resources\MaintenanceContracts\MaintenanceContractResource;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MaintenanceContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Contract')
                    ->columns(1)
                    ->components([
                        Forms\Components\Select::make('lead_id')
                            ->relationship('lead', 'name')
                            ->searchable()
                            ->required()
                            ->preload()
                            ->native(false),
                        Forms\Components\DatePicker::make('start_date')
                            ->required(),
                        Forms\Components\TextInput::make('monthly_fee')
                            ->numeric()
                            ->required()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('billing_day')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(28)
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options(MaintenanceContractResource::statuses())
                            ->required()
                            ->default('active'),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }
}
