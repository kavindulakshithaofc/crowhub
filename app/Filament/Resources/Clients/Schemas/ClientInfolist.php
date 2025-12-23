<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Client')
                    ->columns(2)
                    ->components([
                        Infolists\Components\TextEntry::make('lead.name')
                            ->label('Lead name'),
                        Infolists\Components\TextEntry::make('lead.company')
                            ->label('Company')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('onboarded_at')
                            ->date()
                            ->label('Onboarded'),
                    ]),
                Section::make('Notes')
                    ->hidden(fn ($record) => blank($record->notes))
                    ->components([
                        Infolists\Components\TextEntry::make('notes')
                            ->columnSpanFull()
                            ->markdown(),
                    ]),
            ]);
    }
}
