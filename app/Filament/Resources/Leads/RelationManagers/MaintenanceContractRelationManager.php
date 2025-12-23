<?php

namespace App\Filament\Resources\Leads\RelationManagers;

use App\Filament\Resources\MaintenanceContracts\MaintenanceContractResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MaintenanceContractRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenanceContract';

    protected static ?string $relatedResource = MaintenanceContractResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\TextInput::make('monthly_fee')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                Forms\Components\TextInput::make('billing_day')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(28)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('active'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull()
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monthly_fee')
                    ->money('usd')
                    ->label('Monthly fee'),
                Tables\Columns\TextColumn::make('billing_day')
                    ->label('Billing day'),
                Tables\Columns\TextColumn::make('statusInfo.next_due_date')
                    ->label('Next due')
                    ->state(fn ($record) => optional($record->statusInfo()['next_due_date'])->toDateString())
                    ->badge()
                    ->color(fn ($record) => $record->statusInfo()['is_overdue'] ? 'danger' : ($record->statusInfo()['is_due_soon'] ? 'warning' : 'success')),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn (): bool => blank($this->getOwnerRecord()->maintenanceContract)),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
