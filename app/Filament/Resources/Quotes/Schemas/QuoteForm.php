<?php

namespace App\Filament\Resources\Quotes\Schemas;

use App\Filament\Resources\Quotes\QuoteResource;
use App\Models\Product;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Quote details')
                    ->columns(1)
                    ->components([
                        Forms\Components\Select::make('lead_id')
                            ->relationship('lead', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('quote_no')
                            ->label('Quote #')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('status')
                            ->options(QuoteResource::statuses())
                            ->required(),
                        Forms\Components\DatePicker::make('valid_until')
                            ->label('Valid until'),
                    ]),
                Section::make('Totals')
                    ->columns(1)
                    ->components([
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('discount')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(),
                    ]),
                Section::make('Items')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->columnSpanFull()
                            ->columns(4)
                            ->minItems(1)
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->nullable()
                                    ->columnSpan(2)
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, ?int $state): void {
                                        $name = $state ? Product::find($state)?->name : null;
                                        $set('product_name', $name);
                                    }),
                                Forms\Components\TextInput::make('product_name')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                                Forms\Components\Textarea::make('description')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('qty')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                                Forms\Components\TextInput::make('unit_price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$'),
                                Forms\Components\TextInput::make('line_total')
                                    ->label('Line total')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),
            ]);
    }
}
