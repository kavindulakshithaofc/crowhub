<?php

namespace App\Filament\Resources\SmsTemplates;

use App\Filament\Resources\SmsTemplates\Pages\EditSmsTemplate;
use App\Filament\Resources\SmsTemplates\Pages\ListSmsTemplates;
use App\Models\SmsTemplate;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class SmsTemplateResource extends Resource
{
    protected static ?string $model = SmsTemplate::class;

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $recordTitleAttribute = 'label';

    public static function getModelLabel(): string
    {
        return 'SMS Template';
    }

    public static function getPluralModelLabel(): string
    {
        return 'SMS Templates';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Template details')
                    ->schema([
                        Forms\Components\Toggle::make('is_enabled')
                            ->label('Enabled')
                            ->helperText('Disable to pause this automation without deleting the template.'),
                        Forms\Components\TextInput::make('label')
                            ->readOnly()
                            ->helperText('Used for navigation only.'),
                        Forms\Components\TextInput::make('key')
                            ->label('Identifier')
                            ->readOnly()
                            ->helperText('Reference slug used by automations.'),
                        Forms\Components\Textarea::make('description')
                            ->readOnly()
                            ->rows(2)
                            ->label('Scenario'),
                    ])
                    ->columns(2),
                Section::make('Message body')
                    ->schema([
                        Forms\Components\Textarea::make('body')
                            ->label('SMS content')
                            ->rows(6)
                            ->helperText('Keep messages under 320 characters and include your brand name.')
                            ->required(),
                        Forms\Components\Placeholder::make('placeholders')
                            ->label('Available placeholders')
                            ->content(fn (?SmsTemplate $record): string => $record && count($record->placeholders())
                                ? implode(', ', $record->placeholders())
                                : 'None'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('label')
                    ->label('Template')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->copyable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Last updated')
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSmsTemplates::route('/'),
            'edit' => EditSmsTemplate::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
