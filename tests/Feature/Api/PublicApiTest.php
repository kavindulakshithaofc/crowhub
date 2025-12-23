<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_endpoint_returns_only_active_products(): void
    {
        Product::create([
            'name' => 'Active Product',
            'slug' => 'active-product',
            'short_description' => 'Active',
            'description' => 'Active',
            'features' => ['feature one'],
            'price_hint' => 100,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Inactive Product',
            'slug' => 'inactive-product',
            'short_description' => 'Inactive',
            'description' => 'Inactive',
            'features' => ['legacy'],
            'price_hint' => 200,
            'is_active' => false,
        ]);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['slug' => 'active-product'])
            ->assertJsonMissing(['slug' => 'inactive-product']);
    }

    public function test_inquiry_endpoint_reuses_lead_by_email(): void
    {
        $product = Product::create([
            'name' => 'Engage',
            'slug' => 'engage',
            'short_description' => 'CRM',
            'description' => 'CRM',
            'features' => ['automation'],
            'price_hint' => 1500,
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Jamie Velez',
            'email' => 'jamie@example.com',
            'phone' => '555-1010',
            'company' => 'Northwind Expeditions',
            'product_slug' => $product->slug,
            'message' => 'Please send the capabilities deck.',
        ];

        $firstResponse = $this->postJson('/api/inquiries', $payload)
            ->assertCreated()
            ->json();

        $this->assertDatabaseHas('leads', [
            'id' => $firstResponse['lead_id'],
            'email' => 'jamie@example.com',
            'source' => 'product',
        ]);

        $secondResponse = $this->postJson('/api/inquiries', [
            'name' => 'Jamie Velez',
            'email' => 'jamie@example.com',
            'phone' => '555-1010',
            'message' => 'Following up on timeline.',
        ])->assertCreated()
            ->json();

        $this->assertSame($firstResponse['lead_id'], $secondResponse['lead_id']);
        $this->assertDatabaseCount('inquiries', 2);
    }
}
