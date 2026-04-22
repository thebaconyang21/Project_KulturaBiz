<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use App\Services\CourierService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $payment,
        private CourierService $courier,
        private NotificationService $notify,
    ) {}

    /**
     * Start payment process
     */
    public function initiate(Order $order)
    {
        // COD = no online payment
        if ($order->payment_method === 'cod') {
            $this->finalizeOrder($order);

            return redirect()->route('orders.confirmation', $order->id)
                ->with('success', 'Order placed! Pay when your package arrives.');
        }

        // Create payment intent
        $result = $this->payment->createPaymentIntent($order);

        if (!$result['success']) {
            return redirect()->route('cart.index')
                ->with('error', 'Payment could not be initiated.');
        }

        // Save intent ID
        $order->update([
            'payment_intent_id' => $result['payment_intent_id'] ?? null
        ]);

        return redirect($result['checkout_url']);
    }

    /**
     * Simulated payment page
     */
    public function simulatePage(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $methodInfo = $this->payment->getMethodInfo($order->payment_method);

        return view('payments.simulate', compact('order', 'methodInfo'));
    }

    /**
     * Simulated payment process
     */
    public function processSimulated(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $intentId = $request->input('intent_id', 'sim_' . uniqid());

        $result = $this->payment->verifyPayment($intentId);

        if ($result['success']) {
            $this->finalizeOrder($order, $intentId);

            return redirect()->route('orders.confirmation', $order->id)
                ->with('success', '✅ Payment successful!');
        }

        return redirect()->route('payments.simulate', [
            'order' => $order->id,
            'intent_id' => $intentId,
        ])->with('error', 'Payment failed.');
    }

    /**
     * PayMongo callback
     */
    public function callback(Request $request, Order $order)
    {
        $intentId = $request->query('payment_intent_id')
            ?? $order->payment_intent_id;

        if (!$intentId) {
            return redirect()->route('orders.track', $order->id)
                ->with('error', 'Payment unclear.');
        }

        $result = $this->payment->verifyPayment($intentId);

        if ($result['success']) {
            $this->finalizeOrder($order, $intentId);

            return redirect()->route('orders.confirmation', $order->id)
                ->with('success', '✅ Payment confirmed!');
        }

        return redirect()->route('checkout')
            ->with('error', 'Payment not completed.');
    }

    /**
     * PayMongo webhook
     */
    public function webhook(Request $request)
    {
        $signature = $request->header('Paymongo-Signature');
        $payload = $request->getContent();

        if (config('services.paymongo.use_real') &&
            !$this->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = $request->json('data.attributes.type');
        $data  = $request->json('data.attributes.data');

        match ($event) {
            'payment.paid'   => $this->handlePaymentPaid($data),
            'payment.failed' => $this->handlePaymentFailed($data),
            default          => null,
        };

        return response()->json(['received' => true]);
    }

    // ================================
    // PRIVATE METHODS
    // ================================

    private function finalizeOrder(Order $order, ?string $intentId = null): void
    {
        DB::transaction(function () use ($order, $intentId) {

            $booking = $this->courier->book($order);

            $order->update([
                'payment_status'     => 'paid',
                'payment_intent_id'  => $intentId,
                'courier_name'       => $booking['courier_name'],
                'tracking_number'    => $booking['tracking_number'],
                'estimated_delivery' => $booking['estimated_delivery'],
            ]);
        });

        $this->notify->orderPlaced($order->fresh());
        $this->notify->newOrderForArtisan($order->fresh());
    }

    private function handlePaymentPaid(array $data): void
    {
        $intentId = $data['id'] ?? null;

        if (!$intentId) return;

        $order = Order::where('payment_intent_id', $intentId)->first();

        if ($order && $order->payment_status !== 'paid') {
            $this->finalizeOrder($order, $intentId);
        }
    }

    private function handlePaymentFailed(array $data): void
    {
        $intentId = $data['id'] ?? null;

        if (!$intentId) return;

        Order::where('payment_intent_id', $intentId)
            ->update(['payment_status' => 'failed']);
    }

    private function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        if (!$signature) return false;

        $expectedSig = hash_hmac(
            'sha256',
            $payload,
            config('services.paymongo.webhook_secret')
        );

        return hash_equals($expectedSig, $signature);
    }
}