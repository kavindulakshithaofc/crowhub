<?php

namespace App\Filament\Resources\Leads\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Contact')
                    ->columns(2)
                    ->components([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('company')
                            ->label('Company')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Phone')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(static fn (?string $state): string => match ($state) {
                                'new' => 'info',
                                'contacted' => 'warning',
                                'quoted' => 'gray',
                                'won' => 'success',
                                'lost' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('source')
                            ->badge()
                            ->color('info'),
                    ]),
                Section::make('Financial summary')
                    ->columns(3)
                    ->components([
                        Infolists\Components\TextEntry::make('total_quoted')
                            ->label('Quoted')
                            ->state(fn ($record) => $record->financialSummary()['total_quoted'])
                            ->money('usd'),
                        Infolists\Components\TextEntry::make('total_paid')
                            ->label('Paid')
                            ->state(fn ($record) => $record->financialSummary()['total_paid'])
                            ->money('usd'),
                        Infolists\Components\TextEntry::make('pending')
                            ->label('Pending')
                            ->state(fn ($record) => $record->financialSummary()['pending'])
                            ->money('usd'),
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
