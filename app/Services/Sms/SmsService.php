<?php

namespace App\Services\Sms;

use App\Models\SmsLog;
use App\Models\SmsSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NotifyLk\Api\SmsApi;
use NotifyLk\ApiException;
use Throwable;

class SmsService
{
    public function __construct(protected ?SmsSetting $settings = null)
    {
        $this->settings = $settings ?? SmsSetting::current();
    }

    public function isEnabled(): bool
    {
        return $this->settings->hasActiveCredentials();
    }

    /**
     * @param  array<string, mixed>  $contact
     * @param  array<string, mixed>  $meta
     */
    public function send(string $message, string $recipient, array $contact = [], array $meta = []): bool
    {
        if (! $this->isEnabled()) {
            Log::warning('SMS sending attempted without configured Notify.lk credentials.');

            return false;
        }

        $api = new SmsApi();

        try {
            $api->sendSMS(
                $this->settings->user_id,
                $this->settings->api_key,
                $message,
                $this->formatRecipient($recipient),
                $this->settings->sender_id,
                $contact['first_name'] ?? '',
                $contact['last_name'] ?? '',
                $contact['email'] ?? '',
                $contact['address'] ?? '',
                (int) ($contact['group_id'] ?? 0),
                $contact['type'] ?? null,
            );

            $this->recordLog($message, $recipient, $contact, 'sent', null, $meta);

            return true;
        } catch (ApiException $exception) {
            $this->recordFailure('Notify.lk rejected SMS dispatch.', $message, $recipient, $contact, $exception, $meta);
        } catch (Throwable $exception) {
            $this->recordFailure('Unable to communicate with Notify.lk.', $message, $recipient, $contact, $exception, $meta);
        }

        return false;
    }

    protected function formatRecipient(string $recipient): string
    {
        $recipient = preg_replace('/[^0-9\+]/', '', $recipient) ?? $recipient;
        $recipient = ltrim($recipient, '+');

        if (Str::startsWith($recipient, '00')) {
            $recipient = substr($recipient, 2);
        }

        if ($this->settings->default_country_code) {
            $countryCode = ltrim($this->settings->default_country_code, '+');

            if (! Str::startsWith($recipient, $countryCode)) {
                $recipient = $countryCode.ltrim($recipient, '0');
            }
        }

        return $recipient;
    }

    protected function recordLog(string $message, string $recipient, array $contact, string $status, ?string $response = null, array $meta = []): void
    {
        SmsLog::create([
            'lead_id' => $contact['lead_id'] ?? null,
            'recipient_name' => $contact['first_name'] ?? null,
            'recipient_number' => $recipient,
            'message' => $message,
            'template' => $meta['template'] ?? null,
            'status' => $status,
            'provider_response' => $response,
            'sent_at' => now(),
            'context_type' => $meta['context_type'] ?? null,
            'context_id' => $meta['context_id'] ?? null,
        ]);
    }

    protected function recordFailure(string $logMessage, string $message, string $recipient, array $contact, Throwable $exception, array $meta = []): void
    {
        Log::error($logMessage, [
            'message' => $exception->getMessage(),
        ]);

        $this->recordLog($message, $recipient, $contact, 'failed', $exception->getMessage(), $meta);
    }
}
