<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Filament\Resources\Clients\ClientResource;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Client details')
                    ->columns(1)
                    ->components([
                        Forms\Components\Select::make('lead_id')
                            ->relationship('lead', 'name', fn ($query) => $query->doesntHave('client'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->helperText('Choose an existing lead to convert into a client.'),
                        Forms\Components\DatePicker::make('onboarded_at')
                            ->label('Onboarded on')
                            ->default(now()),
                        Forms\Components\Select::make('status')
                            ->options(ClientResource::statuses())
                            ->default('active')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Engagement notes')
                            ->rows(4),
                    ]),
            ]);
    }
}
