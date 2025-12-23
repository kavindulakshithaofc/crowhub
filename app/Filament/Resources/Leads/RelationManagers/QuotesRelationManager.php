<?php

namespace App\Filament\Resources\Leads\RelationManagers;

use App\Filament\Resources\Quotes\QuoteResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class QuotesRelationManager extends RelationManager
{
    protected static string $relationship = 'quotes';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('quote_no')
            ->recordUrl(fn ($record) => QuoteResource::getUrl('view', ['record' => $record]))
            ->recordAction(null)
            ->columns([
                Tables\Columns\TextColumn::make('quote_no')
                    ->label('Quote #')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->money('usd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->date()
                    ->label('Valid until'),
            ])
            ->headerActions([]);
    }
}
