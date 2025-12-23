<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Filament\Resources\Leads\LeadResource;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Lead details')
                    ->columns(1)
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(50),
                        Forms\Components\Select::make('status')
                            ->options(LeadResource::statuses())
                            ->required()
                            ->default('new'),
                        Forms\Components\Select::make('source')
                            ->options(LeadResource::sources())
                            ->required()
                            ->default('manual'),
                        Forms\Components\Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
