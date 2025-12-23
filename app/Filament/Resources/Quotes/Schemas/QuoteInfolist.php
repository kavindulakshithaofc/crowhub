<?php

namespace App\Filament\Resources\Quotes\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuoteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Quote overview')
                    ->columns(3)
                    ->components([
                        Infolists\Components\TextEntry::make('quote_no')
                            ->label('Quote #'),
                        Infolists\Components\TextEntry::make('lead.name')
                            ->label('Lead'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (?string $state) => match ($state) {
                                'draft' => 'gray',
                                'sent' => 'warning',
                                'accepted' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('valid_until')
                            ->date()
                            ->label('Valid until'),
                        Infolists\Components\TextEntry::make('subtotal')
                            ->money('usd')
                            ->label('Subtotal'),
                        Infolists\Components\TextEntry::make('discount')
                            ->money('usd')
                            ->label('Discount'),
                        Infolists\Components\TextEntry::make('total')
                            ->money('usd')
                            ->label('Total'),
                    ]),
                Section::make('Items')
                    ->columnSpanFull()
                    ->components([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->columns(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('product_name')
                                    ->label('Product')
                                    ->columnSpan(2),
                                Infolists\Components\TextEntry::make('qty')
                                    ->label('Qty'),
                                Infolists\Components\TextEntry::make('unit_price')
                                    ->money('usd')
                                    ->label('Unit price'),
                                Infolists\Components\TextEntry::make('line_total')
                                    ->money('usd')
                                    ->label('Line total'),
                                Infolists\Components\TextEntry::make('description')
                                    ->columnSpanFull()
                                    ->label('Description')
                                    ->placeholder('-'),
                            ]),
                    ]),
            ]);
    }
}
