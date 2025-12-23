<?php

namespace App\Filament\Resources\Leads\RelationManagers;

use App\Filament\Resources\Leads\LeadResource;
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

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->minValue(0.01)
                    ->required()
                    ->prefix('$'),
                Forms\Components\Select::make('type')
                    ->options([
                        'advance' => 'Advance',
                        'final' => 'Final',
                        'other' => 'Other',
                    ])
                    ->required()
                    ->default('other'),
                Forms\Components\DatePicker::make('paid_date')
                    ->required()
                    ->default(now()),
                Forms\Components\Select::make('quote_id')
                    ->label('Related quote')
                    ->searchable()
                    ->preload()
                    ->relationship('quote', 'quote_no')
                    ->native(false)
                    ->placeholder('Optional'),
                Forms\Components\TextInput::make('method')
                    ->maxLength(255),
                Forms\Components\Textarea::make('note')
                    ->columnSpanFull()
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->money('usd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quote.quote_no')
                    ->label('Quote #')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('method')
                    ->label('Method')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('paid_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'advance' => 'Advance',
                        'final' => 'Final',
                        'other' => 'Other',
                    ]),
            ])
            ->headerActions([
                CreateAction::make(),
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
