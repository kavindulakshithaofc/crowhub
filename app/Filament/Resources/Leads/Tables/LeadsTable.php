<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Filament\Resources\Leads\LeadResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->toggleable()
                    ->searchable()
                    ->label('Email'),
                TextColumn::make('phone')
                    ->toggleable()
                    ->searchable()
                    ->label('Phone'),
                TextColumn::make('company')
                    ->toggleable()
                    ->label('Company'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'new' => 'info',
                        'contacted' => 'warning',
                        'quoted' => 'gray',
                        'won' => 'success',
                        'lost' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('source')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('pending_amount')
                    ->state(fn ($record) => $record->financialSummary()['pending'])
                    ->label('Pending')
                    ->money('usd')
                    ->sortable(false),
                TextColumn::make('created_at')
                    ->date()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(LeadResource::statuses()),
                Tables\Filters\SelectFilter::make('source')
                    ->options(LeadResource::sources()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
