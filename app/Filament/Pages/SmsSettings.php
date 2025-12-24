<?php

namespace App\Filament\Pages;

use App\Models\SmsSetting;
use BackedEnum;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use UnitEnum;

class SmsSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    protected static ?string $title = 'SMS Settings';

    protected string $view = 'filament.pages.sms-settings';

    protected ?SmsSetting $setting = null;

    public ?array $data = [];

    public function mount(): void
    {
        $this->setting = SmsSetting::current();
        $this->form->fill($this->getFormState());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Notify.lk credentials')
                    ->description('Update the credentials used to send SMS alerts through Notify.lk.')
                    ->schema([
                        Forms\Components\Toggle::make('is_enabled')
                            ->label('Enable SMS sending')
                            ->helperText('Disable this to stop the app from attempting to send SMS messages.')
                            ->live(),
                        Forms\Components\TextInput::make('user_id')
                            ->label('API User ID')
                            ->required(fn (Get $get): bool => (bool) $get('is_enabled'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable()
                            ->required(fn (Get $get): bool => (bool) $get('is_enabled'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sender_id')
                            ->label('Sender ID')
                            ->maxLength(20)
                            ->required(fn (Get $get): bool => (bool) $get('is_enabled')),
                        Forms\Components\TextInput::make('default_country_code')
                            ->label('Default country code')
                            ->prefix('+')
                            ->helperText('Used when a phone number does not include a country code (example: 94).')
                            ->regex('/^\\d{1,5}$/')
                            ->nullable(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $setting = $this->setting ?? SmsSetting::current();
        $setting->fill($this->form->getState());
        $setting->save();

        $this->setting = $setting;

        Notification::make()
            ->title('SMS settings updated')
            ->success()
            ->send();
    }

    protected function getFormState(): array
    {
        $setting = $this->setting ?? SmsSetting::current();

        return Arr::only($setting->toArray(), [
            'is_enabled',
            'user_id',
            'api_key',
            'sender_id',
            'default_country_code',
        ]);
    }
}
