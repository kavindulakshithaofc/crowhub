<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInquiryRequest;
use App\Models\Lead;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InquiryController extends Controller
{
    public function store(StoreInquiryRequest $request): JsonResponse
    {
        [$lead, $inquiry] = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $product = $this->resolveProduct($data['product_slug'] ?? null);
            $source = $product ? 'product' : 'website';

            $lead = $this->findExistingLead($data['email'] ?? null, $data['phone'] ?? null);

            if ($lead) {
                $this->syncLeadDetails($lead, $data, $source);
            } else {
                $lead = Lead::create([
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'company' => $data['company'] ?? null,
                    'status' => 'new',
                    'source' => $source,
                ]);
            }

            $inquiry = $lead->inquiries()->create([
                'product_id' => $product?->id,
                'message' => $data['message'],
            ]);

            return [$lead, $inquiry];
        });

        return response()->json([
            'lead_id' => $lead->id,
            'inquiry_id' => $inquiry->id,
        ], 201);
    }

    protected function resolveProduct(?string $slug): ?Product
    {
        if (! $slug) {
            return null;
        }

        return Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    protected function findExistingLead(?string $email, ?string $phone): ?Lead
    {
        if ($email) {
            $lead = Lead::where('email', $email)->first();

            if ($lead) {
                return $lead;
            }
        }

        if ($phone) {
            return Lead::where('phone', $phone)->first();
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function syncLeadDetails(Lead $lead, array $data, string $source): void
    {
        $updates = [];

        if (($data['name'] ?? null) && blank($lead->name)) {
            $updates['name'] = $data['name'];
        }

        if (($data['company'] ?? null)) {
            $updates['company'] = $data['company'];
        }

        if (($data['email'] ?? null) && blank($lead->email)) {
            $updates['email'] = $data['email'];
        }

        if (($data['phone'] ?? null) && blank($lead->phone)) {
            $updates['phone'] = $data['phone'];
        }

        if ($source === 'product' && $lead->source !== 'product') {
            $updates['source'] = 'product';
        } elseif ($source === 'website' && $lead->source === 'manual') {
            $updates['source'] = 'website';
        }

        if (! in_array($lead->status, ['won', 'lost'], true) && $lead->status === 'new') {
            $updates['status'] = 'contacted';
        }

        if (! empty($updates)) {
            $lead->fill($updates)->save();
        }
    }
}
