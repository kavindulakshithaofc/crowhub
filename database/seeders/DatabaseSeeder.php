<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Lead;
use App\Models\MaintenanceContract;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use App\Services\QuoteCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@crowhub.test'],
            [
                'name' => 'CrowHub Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->profile()->updateOrCreate(['user_id' => $admin->id], [
            'role' => 'admin',
            'full_name' => 'CrowHub Administrator',
        ]);

        $products = collect([
            [
                'name' => 'Crowd Analytics Suite',
                'slug' => 'crowd-analytics',
                'short_description' => 'Real-time monitoring for large activations.',
                'description' => 'Advanced analytics to understand attendee flow and dwell time.',
                'features' => ['Live heatmaps', 'Event specific dashboards', 'Automated alerts'],
                'price_hint' => 4999,
            ],
            [
                'name' => 'Engage CRM',
                'slug' => 'engage-crm',
                'short_description' => 'Lightweight CRM built for activation teams.',
                'description' => 'Track every interaction and nurture prospects with automated playbooks.',
                'features' => ['Lead scoring', 'Segmented drip campaigns', 'Filament-friendly'],
                'price_hint' => 1899,
            ],
            [
                'name' => 'Insight Sensors',
                'slug' => 'insight-sensors',
                'short_description' => 'Hardware sensors for pop-ups and field teams.',
                'description' => 'Drop-in sensors for dwell tracking and engagement scoring.',
                'features' => ['Battery optimized', 'LTE + WiFi', 'Weather resistant'],
                'price_hint' => 799,
            ],
        ])->map(fn (array $data) => Product::updateOrCreate(['slug' => $data['slug']], $data))
            ->values()
            ->all();

        [$analytics, $crm] = [$products[0], $products[1]];

        $leadOne = Lead::updateOrCreate(
            ['email' => 'ava@novalogistics.com'],
            [
                'name' => 'Ava Carter',
                'company' => 'Nova Logistics',
                'phone' => '+1 415 555 0144',
                'status' => 'quoted',
                'source' => 'manual',
                'notes' => 'Interested in analytics pilot for Q1 launch.',
            ]
        );

        $leadTwo = Lead::updateOrCreate(
            ['email' => 'ethan@stellarretail.com'],
            [
                'name' => 'Ethan Malik',
                'company' => 'Stellar Retail',
                'phone' => '+1 206 555 9902',
                'status' => 'new',
                'source' => 'website',
                'notes' => 'Requested managed maintenance package.',
            ]
        );

        $quote = Quote::updateOrCreate(
            ['quote_no' => 'CROW-DEMO-0001'],
            [
                'lead_id' => $leadOne->id,
                'status' => 'sent',
                'valid_until' => now()->addDays(21),
                'discount' => 200,
            ]
        );

        $quote->items()->delete();
        $quote->items()->createMany([
            [
                'product_id' => $analytics->id,
                'product_name' => $analytics->name,
                'description' => 'Analytics deployment for three flagship locations.',
                'qty' => 1,
                'unit_price' => 4200,
            ],
            [
                'product_id' => $crm->id,
                'product_name' => $crm->name,
                'description' => 'CRM onboarding for ten seats.',
                'qty' => 10,
                'unit_price' => 180,
            ],
        ]);

        app(QuoteCalculator::class)->refreshTotals($quote);

        Payment::updateOrCreate(
            ['note' => 'Initial deposit'],
            [
                'lead_id' => $leadOne->id,
                'quote_id' => $quote->id,
                'amount' => 2500,
                'type' => 'advance',
                'paid_date' => now()->subDays(3),
                'method' => 'Wire',
            ]
        );

        $contract = MaintenanceContract::updateOrCreate(
            ['lead_id' => $leadTwo->id],
            [
                'start_date' => now()->subMonths(1)->startOfMonth(),
                'monthly_fee' => 650,
                'billing_day' => 10,
                'status' => 'active',
                'notes' => 'Includes quarterly on-site calibration.',
            ]
        );

        $contract->payments()->updateOrCreate(
            ['for_month' => now()->startOfMonth()],
            [
                'amount' => 650,
                'paid_date' => now()->startOfMonth()->addDays(9),
                'note' => 'Auto-debited via ACH.',
            ]
        );

        Client::updateOrCreate(
            ['lead_id' => $leadOne->id],
            [
                'onboarded_at' => now()->subDays(1),
                'status' => 'active',
                'notes' => 'Converted from initial quote approval.',
            ]
        );

        $leadOne->update(['status' => 'won']);
    }
}
