<?php

namespace Tests\Unit;

use App\Models\SmsSetting;
use App\Services\Sms\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SmsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function enableSettings(): SmsSetting
    {
        $setting = SmsSetting::current();
        $setting->update([
            'is_enabled' => true,
            'user_id' => 'user',
            'api_key' => 'key',
            'sender_id' => 'NotifyDEMO',
        ]);

        return $setting;
    }

    public function test_logs_successful_sms_send(): void
    {
        $setting = $this->enableSettings();

        Mockery::mock('overload:NotifyLk\\Api\\SmsApi')
            ->shouldReceive('sendSMS')
            ->once();

        $service = new SmsService($setting);

        $this->assertTrue($service->send('Hello world', '94710000000', ['first_name' => 'Tester']));

        $this->assertDatabaseHas('sms_logs', [
            'recipient_number' => '94710000000',
            'status' => 'sent',
        ]);
    }

    public function test_logs_failed_sms_send(): void
    {
        $setting = $this->enableSettings();

        Mockery::mock('overload:NotifyLk\\Api\\SmsApi')
            ->shouldReceive('sendSMS')
            ->andThrow(new \Exception('Gateway down'));

        $service = new SmsService($setting);

        $this->assertFalse($service->send('Hello fail', '94719999999', ['first_name' => 'Tester']));

        $this->assertDatabaseHas('sms_logs', [
            'recipient_number' => '94719999999',
            'status' => 'failed',
            'provider_response' => 'Gateway down',
        ]);
    }
}
