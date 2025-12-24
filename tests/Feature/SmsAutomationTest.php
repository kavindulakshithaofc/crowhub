<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Lead;
use App\Models\MaintenanceContract;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\SmsSetting;
use App\Models\SmsTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SmsAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function enableSms(): void
    {
        SmsSetting::current()->update([
            'is_enabled' => true,
            'user_id' => 'user',
            'api_key' => 'key',
            'sender_id' => 'CrowHub',
            'default_country_code' => '94',
        ]);

        SmsTemplate::syncFromConfig();
    }

    public function test_lead_creation_triggers_welcome_sms(): void
    {
        $this->enableSms();

        Mockery::mock('overload:NotifyLk\\Api\\SmsApi')
            ->shouldReceive('sendSMS')
            ->andReturnTrue();

        Lead::create([
            'name' => 'Welcome Lead',
            'email' => 'welcome@example.com',
            'phone' => '94710000000',
            'company' => 'Acme',
            'status' => 'new',
            'source' => 'manual',
        ]);

        $this->assertDatabaseHas('sms_logs', [
            'template' => 'lead_welcome',
            'recipient_number' => '94710000000',
        ]);
    }

    public function test_custom_template_is_used_when_overridden(): void
    {
        $this->enableSms();

        $customBody = 'Custom hello :name, welcome aboard!';

        SmsTemplate::query()
            ->where('key', 'lead_welcome')
            ->first()
            ?->update(['body' => $customBody]);

        Mockery::mock('overload:NotifyLk\\Api\\SmsApi')
            ->shouldReceive('sendSMS')
            ->andReturnTrue();

        Lead::create([
            'name' => 'Custom Lead',
            'email' => 'custom@example.com',
            'phone' => '94716667777',
            'company' => 'Acme',
            'status' => 'new',
            'source' => 'manual',
        ]);

        $this->assertDatabaseHas('sms_logs', [
            'recipient_number' => '94716667777',
            'message' => 'Custom hello Custom Lead, welcome aboard!',
        ]);
    }

    public function test_disabled_template_prevents_sms_dispatch(): void
    {
        $this->enableSms();

        SmsTemplate::query()
            ->where('key', 'lead_welcome')
            ->first()
            ?->update(['is_enabled' => false]);

        Mockery::mock('overload:NotifyLk\\Api\\SmsApi')
            ->shouldReceive('sendSMS')
            ->never();

        Lead::create([
            'name' => 'Disabled Lead',
            'email' => 'disabled@example.com',
            'phone' => '94717777777',
            'company' => 'Acme',
            'status' => 'new',
            'source' => 'manual',
        ]);

        $this->assertDatabaseMissing('sms_logs', [
            'recipient_number' => '94717777777',
        ]);
    }

    public function test_payment_flow_sends_acknowledgements_and_balance_close_sms(): void
    {
        $this->enableSms();

        Mockery::mock('overload:NotifyLk\\Api\\SmsApi')
            ->shouldReceive('sendSMS')
            ->andReturnTrue();

        $lead = Lead::withoutEvents(fn () => Lead::create([
            'name' => 'Payment Lead',
            'email' => 'payment@example.com',
            'phone' => '94715550000',
            'company' => 'Acme',
            'status' => 'quoted',
            'source' => 'manual',
        ]));

        $quote = Quote::withoutEvents(fn () => Quote::create([
            'quote_no' => 'Q-2001',
            'lead_id' => $lead->id,
            'status' => 'accepted',
            'valid_until' => now()->addDays(5),
            'subtotal' => 1000,
            'discount' => 0,
            'total' => 1000,
        ]));

        Payment::create([
            'lead_id' => $lead->id,
            'quote_id' => $quote->id,
            'amount' => 600,
            'type' => 'advance',
            'paid_date' => now(),
        ]);

        Payment::create([
            'lead_id' => $lead->id,
            'quote_id' => $quote->id,
            'amount' => 400,
            'type' => 'final',
            'paid_date' => now(),
        ]);

        $this->assertDatabaseHas('sms_logs', [
            'template' => 'payment_advance',
        ]);

        $this->assertDatabaseHas('sms_logs', [
            'template' => 'payment_progress',
        ]);

        $this->assertDatabaseHas('sms_logs', [
            'template' => 'payment_balance_cleared',
            'context_type' => Lead::class,
            'context_id' => $lead->id,
        ]);
    }

    public function test_quote_status_updates_send_sms(): void
    {
        $this->enableSms();

        Mockery::mock('overload:NotifyLk\\Api\\SmsApi')
            ->shouldReceive('sendSMS')
            ->andReturnTrue();

        $lead = Lead::withoutEvents(fn () => Lead::create([
            'name' => 'Quote Lead',
            'email' => 'quote@example.com',
            'phone' => '94712223344',
            'company' => 'Acme',
            'status' => 'contacted',
            'source' => 'manual',
        ]));

        $quote = Quote::withoutEvents(fn () => Quote::create([
            'quote_no' => 'Q-3001',
            'lead_id' => $lead->id,
            'status' => 'draft',
            'valid_until' => now()->addDays(10),
            'subtotal' => 500,
            'discount' => 0,
            'total' => 500,
        ]));

        $quote->update(['status' => 'sent']);
        $quote->update(['status' => 'accepted']);

        $this->assertDatabaseHas('sms_logs', [
            'template' => 'quote_sent',
            'context_type' => Quote::class,
            'context_id' => $quote->id,
        ]);

        $this->assertDatabaseHas('sms_logs', [
            'template' => 'quote_accepted',
            'context_type' => Quote::class,
            'context_id' => $quote->id,
        ]);
    }

    public function test_client_and_contract_events_send_followups(): void
    {
        $this->enableSms();

        Mockery::mock('overload:NotifyLk\\Api\\SmsApi')
            ->shouldReceive('sendSMS')
            ->andReturnTrue();

        $lead = Lead::withoutEvents(fn () => Lead::create([
            'name' => 'Client Lead',
            'email' => 'client@example.com',
            'phone' => '94713335555',
            'company' => 'Acme',
            'status' => 'won',
            'source' => 'manual',
        ]));

        $client = Client::create([
            'lead_id' => $lead->id,
            'onboarded_at' => now(),
            'status' => 'active',
        ]);

        $client->update(['status' => 'completed']);

        MaintenanceContract::create([
            'lead_id' => $lead->id,
            'start_date' => now(),
            'monthly_fee' => 100,
            'billing_day' => 5,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('sms_logs', [
            'template' => 'project_scheduled',
            'context_type' => Client::class,
            'context_id' => $client->id,
        ]);

        $this->assertDatabaseHas('sms_logs', [
            'template' => 'post_delivery_feedback',
            'context_type' => Client::class,
            'context_id' => $client->id,
        ]);

        $this->assertDatabaseHas('sms_logs', [
            'template' => 'support_welcome',
        ]);
    }

    public function test_command_dispatches_quote_and_payment_reminders(): void
    {
        $this->enableSms();

        Mockery::mock('overload:NotifyLk\\Api\\SmsApi')
            ->shouldReceive('sendSMS')
            ->andReturnTrue();

        $lead = Lead::withoutEvents(fn () => Lead::create([
            'name' => 'Reminder Lead',
            'email' => 'reminder@example.com',
            'phone' => '94719998888',
            'company' => 'Acme',
            'status' => 'quoted',
            'source' => 'manual',
        ]));

        Quote::withoutEvents(fn () => Quote::create([
            'quote_no' => 'Q-4001',
            'lead_id' => $lead->id,
            'status' => 'sent',
            'valid_until' => now()->addDay(),
            'subtotal' => 1200,
            'discount' => 0,
            'total' => 1200,
        ]));

        Quote::withoutEvents(fn () => Quote::create([
            'quote_no' => 'Q-4002',
            'lead_id' => $lead->id,
            'status' => 'sent',
            'valid_until' => now()->subDay(),
            'subtotal' => 800,
            'discount' => 0,
            'total' => 800,
        ]));

        Payment::withoutEvents(fn () => Payment::create([
            'lead_id' => $lead->id,
            'amount' => 200,
            'type' => 'other',
            'paid_date' => now(),
        ]));

        $this->artisan('sms:run-automations')
            ->assertExitCode(0);

        $this->assertDatabaseHas('sms_logs', [
            'template' => 'quote_expiring',
        ]);

        $this->assertDatabaseHas('sms_logs', [
            'template' => 'quote_expired',
        ]);

        $this->assertDatabaseHas('sms_logs', [
            'template' => 'payment_reminder',
            'context_type' => Lead::class,
            'context_id' => $lead->id,
        ]);
    }
}
