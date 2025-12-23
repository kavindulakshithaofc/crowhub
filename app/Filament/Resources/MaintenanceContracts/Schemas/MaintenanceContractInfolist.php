<?php

namespace App\Filament\Resources\MaintenanceContracts\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MaintenanceContractInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Contract')
                    ->columns(2)
                    ->components([
                        Infolists\Components\TextEntry::make('lead.name')
                            ->label('Lead'),
                        Infolists\Components\TextEntry::make('start_date')
                            ->date()
                            ->label('Start date'),
                        Infolists\Components\TextEntry::make('monthly_fee')
                            ->money('usd')
                            ->label('Monthly fee'),
                        Infolists\Components\TextEntry::make('billing_day')
                            ->label('Billing day'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->label('Status'),
                    ]),
                Section::make('Next cycle')
                    ->components([
                        Infolists\Components\TextEntry::make('statusInfo.next_due_date')
                            ->label('Next due date')
                            ->state(fn ($record) => optional($record->statusInfo()['next_due_date'])->toFormattedDateString()),
                        Infolists\Components\TextEntry::make('due_state')
                            ->label('State')
                            ->state(function ($record): string {
                                $info = $record->statusInfo();

                                if ($info['is_overdue']) {
                                    return 'Overdue';
                                }

                                if ($info['is_due_soon']) {
                                    return 'Due soon';
                                }

                                return 'Up to date';
                            })
                            ->badge()
                            ->color(function ($record): string {
                                $info = $record->statusInfo();

                                if ($info['is_overdue']) {
                                    return 'danger';
                                }

                                if ($info['is_due_soon']) {
                                    return 'warning';
                                }

                                return 'success';
                            }),
                    ]),
                Section::make('Notes')
                    ->hidden(fn ($record) => blank($record->notes))
                    ->components([
                        Infolists\Components\TextEntry::make('notes')
                            ->columnSpanFull()
                            ->markdown(),
                    ]),
            ]);
    }
}
