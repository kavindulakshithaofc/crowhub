<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\SendSingleSms;
use App\Models\Lead;
use App\Models\User;
use App\Services\Sms\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class SendSingleSmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_send_sms_to_custom_number(): void
    {
        $this->actingAs(User::factory()->create());

        $sms = Mockery::mock(SmsService::class);
        $sms->shouldReceive('isEnabled')->andReturnTrue();
        $sms->shouldReceive('send')
            ->once()
            ->with('Hello custom', '94710000000', Mockery::subset(['first_name' => 'Jane']))
            ->andReturnTrue();

        $this->app->instance(SmsService::class, $sms);

        Livewire::test(SendSingleSms::class)
            ->set('data.recipient_type', 'custom')
            ->set('data.custom_phone', '94710000000')
            ->set('data.custom_name', 'Jane')
            ->set('data.message', 'Hello custom')
            ->call('send');
    }

    public function test_can_send_sms_to_lead_phone(): void
    {
        $this->actingAs(User::factory()->create());

        $lead = Lead::query()->create([
            'name' => 'Lead One',
            'email' => 'lead@example.com',
            'phone' => '94715555555',
            'company' => 'Acme',
            'status' => 'new',
            'source' => 'website',
        ]);

        $sms = Mockery::mock(SmsService::class);
        $sms->shouldReceive('isEnabled')->andReturnTrue();
        $sms->shouldReceive('send')
            ->once()
            ->with('Hello lead', $lead->phone, Mockery::subset([
                'first_name' => $lead->name,
                'email' => $lead->email,
            ]))
            ->andReturnTrue();

        $this->app->instance(SmsService::class, $sms);

        Livewire::test(SendSingleSms::class)
            ->set('data.lead_id', $lead->id)
            ->set('data.message', 'Hello lead')
            ->call('send');
    }
}
