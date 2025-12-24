<?php

namespace App\Services\Sms;

use App\Models\Client;
use App\Models\Lead;
use App\Models\MaintenanceContract;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Services\LeadSummary;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class SmsAutomation
{
    public function __construct(
        protected SmsService $smsService,
        protected LeadSummary $leadSummary,
    ) {
    }

    public function sendLeadWelcome(Lead $lead): void
    {
        $this->sendTemplate('lead_welcome', $lead, $lead->phone, [
            'name' => $lead->name,
        ], $lead);
    }

    public function sendQuoteStatusMessage(Quote $quote, string $status): void
    {
        $quote->loadMissing('lead');

        $lead = $quote->lead;

        $template = match ($status) {
            'sent' => 'quote_sent',
            'accepted' => 'quote_accepted',
            'rejected' => 'quote_rejected',
            default => null,
        };

        if (! $template || ! $lead) {
            return;
        }

        $this->sendTemplate($template, $lead, $lead->phone, [
            'name' => $lead->name,
            'quote_no' => $quote->quote_no,
            'valid_until' => $this->formatDate($quote->valid_until),
        ], $quote);
    }

    public function sendAdvanceOrProgressPayment(Payment $payment): void
    {
        $payment->loadMissing('lead');
        $lead = $payment->lead;

        if (! $lead) {
            return;
        }

        $summary = $this->leadSummary->forLead($lead);
        $remaining = max($summary['pending'], 0);
        $template = $payment->type === 'advance' ? 'payment_advance' : 'payment_progress';

        $this->sendTemplate($template, $lead, $lead->phone, [
            'name' => $lead->name,
            'amount' => $this->formatAmount($payment->amount),
            'pending' => $this->formatAmount($remaining),
        ], $payment);

        if ($summary['pending'] <= 0) {
            $this->sendBalanceCleared($lead);
        }
    }

    public function sendBalanceCleared(Lead $lead): void
    {
        $this->sendTemplate('payment_balance_cleared', $lead, $lead->phone, [
            'name' => $lead->name,
        ], $lead);
    }

    public function sendPaymentReminder(Lead $lead): void
    {
        $summary = $this->leadSummary->forLead($lead);
        $remaining = max($summary['pending'], 0);

        if ($remaining <= 0) {
            return;
        }

        $cooldown = (int) config('sms.reminders.payment_reminder_cooldown_days', 7);

        if ($cooldown > 0 && $this->sentWithin('payment_reminder', $lead, $cooldown)) {
            return;
        }

        $this->sendTemplate('payment_reminder', $lead, $lead->phone, [
            'name' => $lead->name,
            'pending' => $this->formatAmount($remaining),
        ], $lead, true);
    }

    public function sendQuoteExpiring(Quote $quote): void
    {
        $quote->loadMissing('lead');
        $lead = $quote->lead;

        if (! $lead) {
            return;
        }

        $this->sendTemplate('quote_expiring', $lead, $lead->phone, [
            'name' => $lead->name,
            'quote_no' => $quote->quote_no,
            'valid_until' => $this->formatDate($quote->valid_until),
        ], $quote);
    }

    public function sendQuoteExpired(Quote $quote): void
    {
        $quote->loadMissing('lead');
        $lead = $quote->lead;

        if (! $lead) {
            return;
        }

        $this->sendTemplate('quote_expired', $lead, $lead->phone, [
            'name' => $lead->name,
            'quote_no' => $quote->quote_no,
            'valid_until' => $this->formatDate($quote->valid_until),
        ], $quote);
    }

    public function sendProjectScheduled(Client $client): void
    {
        $client->loadMissing('lead');
        $lead = $client->lead;

        if (! $lead) {
            return;
        }

        $this->sendTemplate('project_scheduled', $lead, $lead->phone, [
            'name' => $lead->name,
            'schedule_date' => $this->formatDate($client->onboarded_at ?? now()),
        ], $client);
    }

    public function sendSatisfactionCheck(Client $client): void
    {
        $client->loadMissing('lead');

        $lead = $client->lead;

        if (! $lead) {
            return;
        }

        $this->sendTemplate('post_delivery_feedback', $lead, $lead->phone, [
            'name' => $lead->name,
        ], $client);
    }

    public function sendSupportWelcome(MaintenanceContract $contract): void
    {
        $contract->loadMissing('lead');
        $lead = $contract->lead;

        if (! $lead) {
            return;
        }

        $this->sendTemplate('support_welcome', $lead, $lead->phone, [
            'name' => $lead->name,
            'start_date' => $this->formatDate($contract->start_date),
        ], $contract);
    }

    protected function sendTemplate(string $templateKey, ?Lead $lead, ?string $phone, array $data, ?EloquentModel $context = null, bool $allowDuplicates = false): void
    {
        if (! $phone || ! $this->smsService->isEnabled()) {
            return;
        }

        $message = $this->renderTemplate($templateKey, $data);
        $contextModel = $context ?? $lead;

        if (! $allowDuplicates && $contextModel && $this->alreadySent($templateKey, $contextModel)) {
            return;
        }

        if ($message === null || $message === '') {
            return;
        }

        $this->smsService->send($message, $phone, [
            'first_name' => $lead?->name ?? $data['name'] ?? null,
            'email' => $lead?->email,
            'lead_id' => $lead?->id,
        ], [
            'template' => $templateKey,
            'context_type' => $contextModel?->getMorphClass(),
            'context_id' => $contextModel?->getKey(),
        ]);
    }

    protected function renderTemplate(string $templateKey, array $data): ?string
    {
        $template = $this->resolveTemplateBody($templateKey);

        if ($template === null) {
            return null;
        }

        $replacements = [];

        foreach ($data as $key => $value) {
            if ($value instanceof Carbon) {
                $value = $value->format('M j, Y');
            }

            $replacements[':'.$key] = (string) $value;
        }

        return strtr($template, $replacements);
    }

    protected function resolveTemplateBody(string $templateKey): ?string
    {
        $template = SmsTemplate::query()->where('key', $templateKey)->first();

        if ($template) {
            if (! $template->is_enabled) {
                return null;
            }

            return $template->content();
        }

        $definition = config("sms.templates.$templateKey");

        if (is_array($definition)) {
            return (string) ($definition['default'] ?? '');
        }

        if (is_string($definition)) {
            return $definition;
        }

        throw new \InvalidArgumentException("Missing SMS template [{$templateKey}].");
    }

    protected function formatAmount(float $amount): string
    {
        return number_format($amount, 2);
    }

    protected function formatDate($date): string
    {
        if (! $date) {
            return 'soon';
        }

        if (! $date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        return $date->format('M j, Y');
    }

    protected function alreadySent(string $templateKey, EloquentModel $context): bool
    {
        return SmsLog::query()
            ->where('template', $templateKey)
            ->where('context_type', $context->getMorphClass())
            ->where('context_id', $context->getKey())
            ->exists();
    }

    protected function sentWithin(string $templateKey, EloquentModel $context, int $days): bool
    {
        $threshold = now()->subDays($days);

        return SmsLog::query()
            ->where('template', $templateKey)
            ->where('context_type', $context->getMorphClass())
            ->where('context_id', $context->getKey())
            ->where('sent_at', '>=', $threshold)
            ->exists();
    }
}
