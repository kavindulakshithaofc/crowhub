<?php

namespace App\Filament\Resources\Clients\Tables;

use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('lead.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lead.company')
                    ->label('Company')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('onboarded_at')
                    ->label('Onboarded')
                    ->date()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ClientResource::statuses()),
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
