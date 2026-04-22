<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PaymentService
 *
 * Simulates the PayMongo Philippines API (GCash, Maya, Cards).
 * Real endpoint:  https://api.paymongo.com/v1/
 * Docs:           https://developers.paymongo.com/
 *
 * PayPal integration stub also included.
 *
 * To go live: set PAYMONGO_SECRET_KEY in .env and USE_REAL_PAYMENT=true.
 * Method signatures are identical — only the HTTP layer changes.
 */
class PaymentService
{
    private string $baseUrl = 'https://api.paymongo.com/v1';

    // Payment method display info
    private array $methodInfo = [
        'cod'           => ['label' => 'Cash on Delivery',  'icon' => '💵', 'provider' => 'internal'],
        'gcash'         => ['label' => 'GCash',             'icon' => '📱', 'provider' => 'paymongo'],
        'maya'          => ['label' => 'Maya',              'icon' => '💳', 'provider' => 'paymongo'],
        'card'          => ['label' => 'Credit/Debit Card', 'icon' => '💳', 'provider' => 'paymongo'],
        'bank_transfer' => ['label' => 'Bank Transfer',     'icon' => '🏦', 'provider' => 'paymongo'],
        'paypal'        => ['label' => 'PayPal',            'icon' => '🅿️',  'provider' => 'paypal'],
    ];

    // ─────────────────────────────────────────
    // PUBLIC API
    // ─────────────────────────────────────────

    /**
     * Create a payment intent for an order.
     * Returns payment_intent_id, checkout_url, and status.
     */
    public function createPaymentIntent(Order $order): array
    {
        // COD needs no payment gateway
        if ($order->payment_method === 'cod') {
            return [
                'success'           => true,
                'payment_intent_id' => null,
                'checkout_url'      => null,
                'status'            => 'cod_pending',
                'message'           => 'Pay upon delivery.',
            ];
        }

        if (config('services.paymongo.use_real')) {
            return $this->createPaymentIntentReal($order);
        }

        return $this->createPaymentIntentSimulated($order);
    }

    /**
     * Confirm / verify a payment after the user completes checkout.
     * Returns updated payment status.
     */
    public function verifyPayment(string $paymentIntentId): array
    {
        if (config('services.paymongo.use_real')) {
            return $this->verifyPaymentReal($paymentIntentId);
        }

        return $this->verifyPaymentSimulated($paymentIntentId);
    }

    /**
     * Issue a refund for an order.
     */
    public function refund(Order $order, float $amount = null): array
    {
        $amount = $amount ?? $order->total_amount;

        if (config('services.paymongo.use_real')) {
            return $this->refundReal($order, $amount);
        }

        return $this->refundSimulated($order, $amount);
    }

    /**
     * Get human-readable info about a payment method.
     */
    public function getMethodInfo(string $method): array
    {
        return $this->methodInfo[$method] ?? ['label' => ucfirst($method), 'icon' => '💳', 'provider' => 'unknown'];
    }

    // ─────────────────────────────────────────
    // SIMULATED IMPLEMENTATION
    // Mirrors PayMongo API response shapes exactly.
    // ─────────────────────────────────────────

    private function createPaymentIntentSimulated(Order $order): array
    {
        // Simulate a 5% failure rate (realistic for testing)
        $willSucceed = rand(1, 100) <= 95;

        if (!$willSucceed) {
            return [
                'success' => false,
                'status'  => 'failed',
                'message' => 'Payment declined. Please try again or use a different method.',
                'error'   => ['code' => 'insufficient_funds', 'detail' => 'Simulated decline.'],
            ];
        }

        // Mirrors PayMongo PaymentIntent object structure
        $intentId = 'pi_sim_' . strtolower(uniqid()) . '_' . rand(1000, 9999);
        $clientKey = $intentId . '_client_' . md5($order->id . $order->total_amount);

        $checkoutUrl = route('payments.simulate', [
            'order'     => $order->id,
            'intent_id' => $intentId,
            'method'    => $order->payment_method,
        ]);

        Log::info('[PaymentService] Payment intent created (simulated)', [
            'order'      => $order->order_number,
            'intent_id'  => $intentId,
            'amount'     => $order->total_amount,
            'method'     => $order->payment_method,
        ]);

        return [
            'success'           => true,
            'payment_intent_id' => $intentId,
            'client_key'        => $clientKey,
            'checkout_url'      => $checkoutUrl,
            'status'            => 'awaiting_payment_method',
            'amount'            => $order->total_amount * 100, // PayMongo uses centavos
            'currency'          => 'PHP',
            'method'            => $order->payment_method,
        ];
    }

    private function verifyPaymentSimulated(string $paymentIntentId): array
    {
        // Always succeed on verify in simulation (user already saw success page)
        return [
            'success'           => true,
            'payment_intent_id' => $paymentIntentId,
            'status'            => 'succeeded',
            'paid_at'           => now()->toIso8601String(),
            'receipt_url'       => '#simulated-receipt',
        ];
    }

    private function refundSimulated(Order $order, float $amount): array
    {
        $refundId = 're_sim_' . strtolower(uniqid());

        Log::info('[PaymentService] Refund issued (simulated)', [
            'order'     => $order->order_number,
            'refund_id' => $refundId,
            'amount'    => $amount,
        ]);

        return [
            'success'   => true,
            'refund_id' => $refundId,
            'amount'    => $amount,
            'status'    => 'succeeded',
            'message'   => "Refund of ₱" . number_format($amount, 2) . " processed successfully.",
        ];
    }

    // ─────────────────────────────────────────
    // REAL API STUBS — PayMongo
    // Uncomment and configure when you have a PayMongo account.
    // ─────────────────────────────────────────

    private function createPaymentIntentReal(Order $order): array
    {
        // PayMongo uses Base64-encoded secret key as Basic auth
        $encodedKey = base64_encode(config('services.paymongo.secret_key') . ':');

        $paymongoMethod = match ($order->payment_method) {
            'gcash'         => 'gcash',
            'maya'          => 'paymaya',
            'card'          => 'card',
            'bank_transfer' => 'dob',
            default         => 'gcash',
        };

        // Step 1: Create PaymentIntent
        $intentResponse = Http::withHeaders([
            'Authorization' => 'Basic ' . $encodedKey,
            'Content-Type'  => 'application/json',
        ])->post($this->baseUrl . '/payment_intents', [
            'data' => [
                'attributes' => [
                    'amount'                  => (int) ($order->total_amount * 100),
                    'payment_method_allowed'  => [$paymongoMethod],
                    'payment_method_options'  => ['card' => ['request_three_d_secure' => 'any']],
                    'currency'                => 'PHP',
                    'capture_type'            => 'automatic',
                    'description'             => 'KulturaBiz Order #' . $order->order_number,
                    'metadata'                => ['order_id' => $order->id],
                ],
            ],
        ]);

        if (!$intentResponse->successful()) {
            Log::error('[PaymentService] PayMongo intent creation failed', $intentResponse->json());
            return $this->createPaymentIntentSimulated($order);
        }

        $intent    = $intentResponse->json('data');
        $intentId  = $intent['id'];
        $clientKey = $intent['attributes']['client_key'];

        // Step 2: Create PaymentMethod
        $methodResponse = Http::withHeaders([
            'Authorization' => 'Basic ' . $encodedKey,
            'Content-Type'  => 'application/json',
        ])->post($this->baseUrl . '/payment_methods', [
            'data' => [
                'attributes' => [
                    'type'     => $paymongoMethod,
                    'billing'  => [
                        'name'  => $order->recipient_name,
                        'phone' => $order->contact_number,
                        'address' => [
                            'line1'   => $order->delivery_address,
                            'city'    => $order->city,
                            'state'   => $order->province,
                            'country' => 'PH',
                        ],
                    ],
                ],
            ],
        ]);

        $methodId = $methodResponse->json('data.id');

        // Step 3: Attach PaymentMethod to Intent to get checkout URL
        $attachResponse = Http::withHeaders([
            'Authorization' => 'Basic ' . $encodedKey,
            'Content-Type'  => 'application/json',
        ])->post($this->baseUrl . "/payment_intents/{$intentId}/attach", [
            'data' => [
                'attributes' => [
                    'payment_method'  => $methodId,
                    'client_key'      => $clientKey,
                    'return_url'      => route('payments.callback', ['order' => $order->id]),
                ],
            ],
        ]);

        $checkoutUrl = $attachResponse->json('data.attributes.next_action.redirect.url');

        return [
            'success'           => true,
            'payment_intent_id' => $intentId,
            'client_key'        => $clientKey,
            'checkout_url'      => $checkoutUrl,
            'status'            => 'awaiting_payment_method',
            'amount'            => (int) ($order->total_amount * 100),
            'currency'          => 'PHP',
            'method'            => $order->payment_method,
        ];
    }

    private function verifyPaymentReal(string $paymentIntentId): array
    {
        $encodedKey = base64_encode(config('services.paymongo.secret_key') . ':');

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $encodedKey,
        ])->get($this->baseUrl . '/payment_intents/' . $paymentIntentId);

        if (!$response->successful()) {
            return ['success' => false, 'status' => 'failed', 'message' => 'Could not verify payment.'];
        }

        $status = $response->json('data.attributes.status');

        return [
            'success'           => $status === 'succeeded',
            'payment_intent_id' => $paymentIntentId,
            'status'            => $status,
            'paid_at'           => $response->json('data.attributes.paid_at'),
            'receipt_url'       => $response->json('data.attributes.payments.0.attributes.billing.receipt_url'),
        ];
    }

    private function refundReal(Order $order, float $amount): array
    {
        $encodedKey = base64_encode(config('services.paymongo.secret_key') . ':');

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $encodedKey,
            'Content-Type'  => 'application/json',
        ])->post($this->baseUrl . '/refunds', [
            'data' => [
                'attributes' => [
                    'amount'     => (int) ($amount * 100),
                    'payment_id' => $order->payment_intent_id,
                    'reason'     => 'others',
                    'notes'      => 'KulturaBiz refund for order #' . $order->order_number,
                ],
            ],
        ]);

        if (!$response->successful()) {
            return ['success' => false, 'message' => 'Refund failed. Contact support.'];
        }

        return [
            'success'   => true,
            'refund_id' => $response->json('data.id'),
            'amount'    => $amount,
            'status'    => $response->json('data.attributes.status'),
            'message'   => "Refund of ₱" . number_format($amount, 2) . " processed.",
        ];
    }
}