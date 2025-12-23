<?php

namespace App\Filament\Resources\Quotes\Tables;

use App\Filament\Resources\Quotes\QuoteResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('quote_no')
                    ->label('Quote #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lead.name')
                    ->label('Lead')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('total')
                    ->money('usd')
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->date()
                    ->label('Valid until'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->since()
                    ->label('Updated'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(QuoteResource::statuses()),
                Tables\Filters\Filter::make('valid_until')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('valid_until', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('valid_until', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('markSent')
                    ->label('Mark sent')
                    ->color('warning')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(fn ($record) => $record->update(['status' => 'sent'])),
                Action::make('markAccepted')
                    ->label('Mark accepted')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn ($record) => in_array($record->status, ['sent', 'draft'], true))
                    ->action(fn ($record) => $record->update(['status' => 'accepted'])),
                Action::make('markRejected')
                    ->label('Mark rejected')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn ($record) => in_array($record->status, ['sent', 'draft'], true))
                    ->action(fn ($record) => $record->update(['status' => 'rejected'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
