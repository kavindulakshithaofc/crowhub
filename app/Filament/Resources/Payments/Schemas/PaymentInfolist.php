<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Details')
                    ->columns(2)
                    ->components([
                        Infolists\Components\TextEntry::make('lead.name')
                            ->label('Lead'),
                        Infolists\Components\TextEntry::make('quote.quote_no')
                            ->label('Quote #')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('amount')
                            ->money('usd')
                            ->label('Amount'),
                        Infolists\Components\TextEntry::make('type')
                            ->badge()
                            ->label('Type'),
                        Infolists\Components\TextEntry::make('paid_date')
                            ->date()
                            ->label('Paid on'),
                        Infolists\Components\TextEntry::make('method')
                            ->label('Method')
                            ->placeholder('-'),
                    ]),
                Section::make('Notes')
                    ->hidden(fn ($record) => blank($record->note))
                    ->components([
                        Infolists\Components\TextEntry::make('note')
                            ->columnSpanFull()
                            ->markdown(),
                    ]),
            ]);
    }
}
