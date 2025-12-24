<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsLogResource\Pages;
use App\Models\SmsLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use UnitEnum;

class SmsLogResource extends Resource
{
    protected static ?string $model = SmsLog::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-inbox';

    protected static string | UnitEnum | null $navigationGroup = 'Messaging';

    protected static ?string $navigationLabel = 'SMS Logs';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sent_at', 'desc')
            ->columns([
                TextColumn::make('lead.name')
                    ->label('Lead')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('recipient_name')
                    ->label('Recipient name')
                    ->toggleable(),
                TextColumn::make('recipient_number')
                    ->label('Phone number')
                    ->searchable(),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'sent',
                        'danger' => 'failed',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'sent',
                        'heroicon-o-x-circle' => 'failed',
                    ])
                    ->label('Status'),
                TextColumn::make('sent_at')
                    ->dateTime()
                    ->label('Sent at')
                    ->sortable(),
                TextColumn::make('message')
                    ->label('Message')
                    ->wrap()
                    ->limit(70)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('provider_response')
                    ->label('Provider response')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsLogs::route('/'),
        ];
    }
}
