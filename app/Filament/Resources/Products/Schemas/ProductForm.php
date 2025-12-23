<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Product Details')
                    ->columns(1)
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state, Forms\Get $get): void {
                                if ($get('slug')) {
                                    return;
                                }

                                $set('slug', Str::slug($state ?? ''));
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Used in public APIs.'),
                        Forms\Components\TextInput::make('price_hint')
                            ->label('Price hint')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('$')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),
                        Forms\Components\Textarea::make('short_description')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('description')
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'italic',
                                'link',
                                'orderedList',
                            ]),
                        Forms\Components\TagsInput::make('features')
                            ->label('Key features')
                            ->placeholder('Add a feature')
                            ->columnSpanFull()
                            ->helperText('List the major selling points.'),
                    ]),
            ]);
    }
}
