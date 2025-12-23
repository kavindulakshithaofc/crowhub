<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Payment details')
                    ->columns(1)
                    ->components([
                        Forms\Components\Select::make('lead_id')
                            ->relationship('lead', 'name')
                            ->searchable()
                            ->required()
                            ->preload()
                            ->native(false),
                        Forms\Components\Select::make('quote_id')
                            ->label('Related quote')
                            ->relationship('quote', 'quote_no')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Optional'),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->prefix('$'),
                        Forms\Components\Select::make('type')
                            ->options(PaymentResource::types())
                            ->required()
                            ->default('other'),
                        Forms\Components\DatePicker::make('paid_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('method')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('note')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }
}
