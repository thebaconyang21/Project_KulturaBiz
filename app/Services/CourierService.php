<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CourierService
 *
 * Simulates the J&T Express Philippines API.
 * Real endpoint:  https://jtexpressph.com/api/
 * Real LBC endpoint: https://api.lbcexpress.com/
 *
 * To swap in real credentials, set COURIER_PROVIDER, COURIER_API_KEY,
 * and COURIER_API_URL in your .env and flip USE_REAL_COURIER=true.
 * The method signatures stay identical — only the HTTP call changes.
 */
class CourierService
{
    // Simulated courier options with their code prefixes
    private array $couriers = [
        'JT'  => 'J&T Express',
        'LBC' => 'LBC Express',
        'NV'  => 'Ninja Van',
        'FX'  => 'Flash Express',
        'GO'  => '2GO Express',
    ];

    // Simulated transit days per province region
    private array $transitDays = [
        'Metro Manila'         => [2, 3],
        'Region III'           => [2, 4],
        'Region IV-A'          => [2, 4],
        'Cebu'                 => [3, 5],
        'Davao Region'         => [4, 6],
        'BARMM'                => [5, 8],
        'Lanao del Sur'        => [5, 8],
        'South Cotabato'       => [4, 7],
        'Bukidnon'             => [4, 7],
        'default'              => [5, 10],
    ];

    // ─────────────────────────────────────────
    // PUBLIC API
    // ─────────────────────────────────────────

    /**
     * Book a shipment for an order.
     * Returns courier name, tracking number, and estimated delivery date.
     */
    public function book(Order $order): array
    {
        if (config('services.courier.use_real')) {
            return $this->bookReal($order);
        }

        return $this->bookSimulated($order);
    }

    /**
     * Fetch the live tracking status of an order from the courier.
     * Returns an array of tracking events.
     */
    public function track(Order $order): array
    {
        if (config('services.courier.use_real')) {
            return $this->trackReal($order);
        }

        return $this->trackSimulated($order);
    }

    /**
     * Calculate shipping fee based on province and estimated weight.
     */
    public function calculateFee(string $province, float $weightKg = 1.0): float
    {
        if (config('services.courier.use_real')) {
            return $this->calculateFeeReal($province, $weightKg);
        }

        return $this->calculateFeeSimulated($province, $weightKg);
    }


    private function bookSimulated(Order $order): array
    {
        // Pick a courier based on province (simulate smart routing)
        $courierCode = $this->selectCourier($order->province);
        $courier     = $this->couriers[$courierCode];

        // Generate a realistic tracking number
        $tracking = strtoupper($courierCode)
            . date('ymd')
            . strtoupper(substr(md5($order->id . uniqid()), 0, 8))
            . 'PH';

        // Estimate delivery window
        [$minDays, $maxDays] = $this->transitDays[$order->province]
            ?? $this->transitDays['default'];
        $estimatedDays     = rand($minDays, $maxDays);
        $estimatedDelivery = now()->addDays($estimatedDays);

        Log::info('[CourierService] Booked shipment (simulated)', [
            'order'    => $order->order_number,
            'courier'  => $courier,
            'tracking' => $tracking,
        ]);

        // This mirrors the J&T Express API success response shape
        return [
            'success'            => true,
            'courier_name'       => $courier,
            'courier_code'       => $courierCode,
            'tracking_number'    => $tracking,
            'estimated_delivery' => $estimatedDelivery,
            'booking_reference'  => 'BK-' . strtoupper(uniqid()),
            'fee'                => $this->calculateFeeSimulated($order->province),
        ];
    }

    private function trackSimulated(Order $order): array
    {
        // Build a realistic event timeline based on current order status
        $events = [];

        $events[] = [
            'timestamp'   => $order->created_at->format('Y-m-d H:i:s'),
            'status'      => 'ORDER_PLACED',
            'description' => 'Order received by KulturaBiz.',
            'location'    => 'Online',
        ];

        if (in_array($order->status, ['processing', 'shipped', 'delivered'])) {
            $events[] = [
                'timestamp'   => ($order->processing_at ?? $order->created_at->addHours(2))->format('Y-m-d H:i:s'),
                'status'      => 'PACKAGE_PICKED_UP',
                'description' => 'Package picked up by ' . ($order->courier_name ?? 'courier') . '.',
                'location'    => 'Artisan Hub — Mindanao',
            ];
        }

        if (in_array($order->status, ['shipped', 'delivered'])) {
            $events[] = [
                'timestamp'   => ($order->shipped_at ?? $order->created_at->addDays(2))->format('Y-m-d H:i:s'),
                'status'      => 'IN_TRANSIT',
                'description' => 'Package in transit to ' . $order->city . '.',
                'location'    => 'Sorting Hub — Davao City',
            ];
            $events[] = [
                'timestamp'   => ($order->shipped_at ?? $order->created_at->addDays(2))->addHours(6)->format('Y-m-d H:i:s'),
                'status'      => 'OUT_FOR_DELIVERY',
                'description' => 'Package out for delivery.',
                'location'    => $order->city . ', ' . $order->province,
            ];
        }

        if ($order->status === 'delivered') {
            $events[] = [
                'timestamp'   => ($order->delivered_at ?? now())->format('Y-m-d H:i:s'),
                'status'      => 'DELIVERED',
                'description' => 'Package delivered to ' . $order->recipient_name . '.',
                'location'    => $order->delivery_address,
            ];
        }

        return [
            'success'         => true,
            'tracking_number' => $order->tracking_number,
            'courier'         => $order->courier_name,
            'events'          => $events,
            'current_status'  => end($events)['status'] ?? 'UNKNOWN',
        ];
    }

    private function calculateFeeSimulated(string $province, float $weightKg = 1.0): float
    {
        // Tiered flat-rate simulation (mirrors LBC rate table structure)
        $baseRates = [
            'Metro Manila'  => 100,
            'Region III'    => 120,
            'Region IV-A'   => 120,
            'Cebu'          => 150,
            'Davao Region'  => 150,
            'BARMM'         => 200,
            'Lanao del Sur' => 200,
            'South Cotabato'=> 180,
            'Bukidnon'      => 180,
        ];

        $base        = $baseRates[$province] ?? 200;
        $weightExtra = max(0, ($weightKg - 1)) * 30; // ₱30/kg after first kg

        return round($base + $weightExtra, 2);
    }



    private function bookReal(Order $order): array
    {
      
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.courier.api_key'),
            'Content-Type'  => 'application/json',
        ])->post(config('services.courier.api_url') . '/orders/create', [
            'sender'    => [
                'name'    => config('app.name'),
                'phone'   => config('services.courier.sender_phone'),
                'address' => config('services.courier.sender_address'),
            ],
            'recipient' => [
                'name'    => $order->recipient_name,
                'phone'   => $order->contact_number,
                'address' => $order->delivery_address . ', ' . $order->city . ', ' . $order->province,
            ],
            'parcel'    => [
                'weight'      => 1.0,
                'description' => 'Handcrafted products',
                'value'       => $order->total_amount,
            ],
            'service_type' => 'standard',
            'payment_type' => $order->payment_method === 'cod' ? 'COD' : 'PREPAID',
            'cod_amount'   => $order->payment_method === 'cod' ? $order->total_amount : 0,
        ]);

        if (!$response->successful()) {
            Log::error('[CourierService] Real API booking failed', $response->json());
            // Fall back to simulation on error
            return $this->bookSimulated($order);
        }

        $data = $response->json();

        return [
            'success'            => true,
            'courier_name'       => 'J&T Express',
            'courier_code'       => 'JT',
            'tracking_number'    => $data['tracking_number'],
            'estimated_delivery' => now()->addDays($data['estimated_days'] ?? 7),
            'booking_reference'  => $data['booking_id'],
            'fee'                => $data['shipping_fee'],
        ];
    }

    private function trackReal(Order $order): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.courier.api_key'),
        ])->get(config('services.courier.api_url') . '/tracking/' . $order->tracking_number);

        if (!$response->successful()) {
            return $this->trackSimulated($order);
        }

        return $response->json();
    }

    private function calculateFeeReal(string $province, float $weightKg = 1.0): float
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.courier.api_key'),
        ])->post(config('services.courier.api_url') . '/rates/calculate', [
            'destination_province' => $province,
            'weight_kg'            => $weightKg,
        ]);

        return $response->successful()
            ? (float) $response->json('rate')
            : $this->calculateFeeSimulated($province, $weightKg);
    }

    // ─────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────

    private function selectCourier(string $province): string
    {
        // Mindanao provinces → prefer couriers with Mindanao coverage
        $mindanaoProvinces = [
            'BARMM', 'Lanao del Sur', 'South Cotabato', 'Bukidnon',
            'Davao Region', 'Davao del Sur', 'Davao del Norte',
            'Cotabato', 'Sultan Kudarat', 'Maguindanao',
        ];

        if (in_array($province, $mindanaoProvinces)) {
            return collect(['JT', 'LBC', 'GO'])->random();
        }

        return collect(array_keys($this->couriers))->random();
    }
}