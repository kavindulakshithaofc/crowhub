<?php

namespace App\Filament\Pages;

use App\Models\Lead;
use App\Services\Sms\SmsService;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use UnitEnum;

class SendSingleSms extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-paper-airplane';

    protected static string | UnitEnum | null $navigationGroup = 'Messaging';

    protected static ?string $title = 'Send SMS';

    protected string $view = 'filament.pages.send-single-sms';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(array_merge([
            'recipient_type' => 'lead',
        ], $this->data ?? []));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Recipient')
                    ->schema([
                        Forms\Components\Radio::make('recipient_type')
                            ->inline()
                            ->live()
                            ->required()
                            ->options([
                                'lead' => 'Existing client/lead',
                                'custom' => 'Custom phone number',
                            ]),
                        Forms\Components\Select::make('lead_id')
                            ->label('Lead')
                            ->options(fn (): array => $this->getLeadOptions())
                            ->searchable()
                            ->native(false)
                            ->visible(fn (Get $get): bool => $get('recipient_type') === 'lead')
                            ->required(fn (Get $get): bool => $get('recipient_type') === 'lead')
                            ->helperText('Search by name to load the saved phone number.'),
                        Forms\Components\Placeholder::make('lead_phone_preview')
                            ->label('Lead phone number')
                            ->content(function (Get $get): string {
                                $leadId = $get('lead_id');
                                if (! $leadId) {
                                    return 'Select a lead to use their saved phone number.';
                                }

                                $lead = Lead::query()->find($leadId);

                                return $lead?->phone ?: 'This lead does not have a phone number on record.';
                            })
                            ->visible(fn (Get $get): bool => $get('recipient_type') === 'lead'),
                        Forms\Components\TextInput::make('custom_name')
                            ->label('Recipient name')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => $get('recipient_type') === 'custom')
                            ->helperText('Optional label stored with the SMS log.')
                            ->nullable(),
                        Forms\Components\TextInput::make('custom_phone')
                            ->label('Phone number')
                            ->tel()
                            ->visible(fn (Get $get): bool => $get('recipient_type') === 'custom')
                            ->required(fn (Get $get): bool => $get('recipient_type') === 'custom')
                            ->helperText('Use the international format, e.g. 9471XXXXXXX.'),
                    ])->columns(2),
                Section::make('Message')
                    ->schema([
                        Forms\Components\Textarea::make('message')
                            ->label('SMS content')
                            ->required()
                            ->rows(6)
                            ->maxLength(320)
                            ->helperText('320 characters max. Include your brand name for better delivery rates.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function send(SmsService $smsService): void
    {
        if (! $smsService->isEnabled()) {
            Notification::make()
                ->danger()
                ->title('SMS disabled')
                ->body('Enable and configure credentials in SMS Settings before sending messages.')
                ->send();

            return;
        }

        $state = $this->form->getState();
        $payload = $this->buildPayload($state);

        if (! $payload) {
            Notification::make()
                ->danger()
                ->title('Unable to send message')
                ->body('Please select a recipient with a phone number.')
                ->send();

            return;
        }

        [$number, $contact] = $payload;

        if ($smsService->send($state['message'], $number, $contact)) {
            $this->reset('data');
            $this->mount();

            Notification::make()
                ->success()
                ->title('Message sent')
                ->body('Your SMS was handed off to Notify.lk.')
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title('Failed to send SMS')
                ->body('Notify.lk rejected the message or the gateway was unreachable.')
                ->send();
        }
    }

    protected function buildPayload(array $state): ?array
    {
        if (($state['recipient_type'] ?? 'lead') === 'custom') {
            $phone = trim($state['custom_phone'] ?? '');

            if ($phone === '') {
                return null;
            }

            return [
                $phone,
                [
                    'first_name' => $state['custom_name'] ?? '',
                    'lead_id' => null,
                ],
            ];
        }

        $lead = isset($state['lead_id'])
            ? Lead::query()->find($state['lead_id'])
            : null;

        if (! $lead?->phone) {
            return null;
        }

        return [
            $lead->phone,
            [
                'first_name' => $lead->name,
                'email' => $lead->email,
                'lead_id' => $lead->id,
            ],
        ];
    }

    protected function getLeadOptions(): array
    {
        return Lead::query()
            ->orderBy('name')
            ->limit(1000)
            ->pluck('name', 'id')
            ->all();
    }
}
