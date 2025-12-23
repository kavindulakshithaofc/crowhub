<?php

namespace App\Filament\Resources\MaintenanceContracts\Tables;

use App\Filament\Resources\MaintenanceContracts\MaintenanceContractResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MaintenanceContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lead.name')
                    ->label('Lead')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('monthly_fee')
                    ->money('usd')
                    ->label('Monthly fee')
                    ->sortable(),
                TextColumn::make('billing_day')
                    ->label('Billing day'),
                TextColumn::make('statusInfo.next_due_date')
                    ->label('Next due')
                    ->state(fn ($record) => optional($record->statusInfo()['next_due_date'])->toFormattedDateString())
                    ->badge()
                    ->color(fn ($record) => $record->statusInfo()['is_overdue'] ? 'danger' : ($record->statusInfo()['is_due_soon'] ? 'warning' : 'success')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(MaintenanceContractResource::statuses()),
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
