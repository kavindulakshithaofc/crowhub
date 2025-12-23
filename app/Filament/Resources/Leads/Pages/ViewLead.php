<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Quotes\QuoteResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('change_status')
                ->label('Change status')
                ->icon('heroicon-o-arrows-right-left')
                ->form([
                    Forms\Components\Select::make('status')
                        ->options(LeadResource::statuses())
                        ->required()
                        ->default(fn () => $this->record->status),
                ])
                ->action(function (array $data): void {
                    $this->record->update(['status' => $data['status']]);
                }),
            Action::make('quick_quote')
                ->label('Create quote')
                ->icon('heroicon-o-document-plus')
                ->action(function (): void {
                    $quote = $this->record->quotes()->create([
                        'status' => 'draft',
                        'valid_until' => now()->addDays(14),
                        'discount' => 0,
                        'subtotal' => 0,
                        'total' => 0,
                    ]);

                    $this->redirect(QuoteResource::getUrl('edit', ['record' => $quote]));
                })
                ->requiresConfirmation()
                ->modalHeading('Create draft quote')
                ->modalDescription('Generate a draft quote for this lead using the next available quote number.'),
            Action::make('quick_payment')
                ->label('Add payment')
                ->icon('heroicon-o-credit-card')
                ->form([
                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->required()
                        ->minValue(0.01),
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
                        ->options(fn () => $this->record->quotes()->pluck('quote_no', 'id')->toArray())
                        ->searchable()
                        ->native(false)
                        ->placeholder('Optional'),
                    Forms\Components\TextInput::make('method')
                        ->maxLength(255),
                    Forms\Components\Textarea::make('note')
                        ->columnSpanFull()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->record->payments()->create($data);
                })
                ->successNotificationTitle('Payment recorded'),
            Action::make('convert_to_client')
                ->label('Convert to client')
                ->icon('heroicon-o-user-plus')
                ->visible(fn () => $this->record->client === null)
                ->form([
                    Forms\Components\DatePicker::make('onboarded_at')
                        ->label('Onboarded on')
                        ->default(now()),
                    Forms\Components\Select::make('status')
                        ->label('Client status')
                        ->options(ClientResource::statuses())
                        ->default('active')
                        ->required(),
                    Forms\Components\Select::make('lead_status')
                        ->label('Update lead status')
                        ->options(LeadResource::statuses())
                        ->default('won'),
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data): void {
                    $this->record->client()->create([
                        'onboarded_at' => $data['onboarded_at'] ?? now(),
                        'status' => $data['status'],
                        'notes' => $data['notes'] ?? null,
                    ]);

                    $this->record->update([
                        'status' => $data['lead_status'] ?? $this->record->status,
                    ]);
                })
                ->successNotificationTitle('Lead converted to client'),
        ];
    }
}
