<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use App\Services\CourierService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService      $payment,
        private CourierService      $courier,
        private NotificationService $notify,
    ) {}


    public function initiate(Order $order)
    {

        if ($order->payment_method === 'cod') {

            try {
                $order->update([
                    'payment_status'     => 'pending', 
                    'courier_name'       => null,      
                    'tracking_number'    => null,      
                    'estimated_delivery' => null,     
                ]);

               
                try {
                    $this->notify->orderPlaced($order->fresh());
                    $this->notify->newOrderForArtisan($order->fresh());
                } catch (\Exception $e) {
                    Log::info('[Payment] COD notification skipped: ' . $e->getMessage());
                }

            } catch (\Exception $e) {
                Log::error('[Payment] COD initiate error: ' . $e->getMessage());
            }

            return redirect()->route('orders.confirmation', $order->id)
                ->with('success', 'Order placed! Pay cash when your package arrives.');
        }

       
        try {
            $result = $this->payment->createPaymentIntent($order);

            if (!$result['success']) {
                return redirect()->route('cart.index')
                    ->with('error', 'Payment could not be initiated. Please try again.');
            }

            
            $order->update([
                'payment_intent_id' => $result['payment_intent_id'] ?? null,
            ]);

            return redirect($result['checkout_url']);

        } catch (\Exception $e) {
            Log::error('[Payment] Initiate error: ' . $e->getMessage());

            
            $intentId    = 'pi_sim_' . strtolower(uniqid());
            $checkoutUrl = route('payments.simulate', [
                'order'     => $order->id,
                'intent_id' => $intentId,
            ]);

            return redirect($checkoutUrl);
        }
    }

   
    public function simulatePage(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $methodInfo = $this->payment->getMethodInfo($order->payment_method);

        return view('payments.simulate', compact('order', 'methodInfo'));
    }


    public function processSimulated(Request $request, string $order)
    {
        $orderModel = Order::findOrFail($order);

        
        if ($orderModel->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            
            $orderModel->update([
                'payment_status'     => 'paid',
                'payment_intent_id'  => $request->input('intent_id'),
                'courier_name'       => null, 
                'tracking_number'    => null, 
                'estimated_delivery' => null, 
            ]);

            Log::info('[Payment] GCash/Bank payment confirmed — awaiting courier assignment', [
                'order'  => $orderModel->order_number,
                'method' => $orderModel->payment_method,
            ]);

            
            try {
                $this->notify->orderPlaced($orderModel->fresh());
                $this->notify->newOrderForArtisan($orderModel->fresh());
            } catch (\Exception $e) {
                Log::info('[Payment] Notification skipped: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            Log::error('[Payment] processSimulated error: ' . $e->getMessage());
            
        }

        
        return redirect()
            ->route('orders.confirmation', $orderModel->id)
            ->with('success', 'Payment successful! Your order is confirmed.');
    }


    public function callback(Request $request, Order $order)
    {
        $intentId = $request->query('payment_intent_id')
            ?? $order->payment_intent_id;

        if (!$intentId) {
            return redirect()->route('orders.track', $order->id)
                ->with('error', 'Payment status unclear. Please check your order.');
        }

        try {
            $result = $this->payment->verifyPayment($intentId);

            if ($result['success']) {
                $order->update([
                    'payment_status'     => 'paid',
                    'payment_intent_id'  => $intentId,
                    'courier_name'       => null,
                    'tracking_number'    => null,
                    'estimated_delivery' => null,
                ]);

                try {
                    $this->notify->orderPlaced($order->fresh());
                    $this->notify->newOrderForArtisan($order->fresh());
                } catch (\Exception $e) {
                    Log::info('[Payment] Callback notification skipped: ' . $e->getMessage());
                }

                return redirect()->route('orders.confirmation', $order->id)
                    ->with('success', 'Payment confirmed!');
            }
        } catch (\Exception $e) {
            Log::error('[Payment] Callback error: ' . $e->getMessage());
        }

        return redirect()->route('checkout')
            ->with('error', 'Payment was not completed. Please try again.');
    }

 
    public function webhook(Request $request)
    {
        $signature = $request->header('Paymongo-Signature');
        $payload   = $request->getContent();

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



    private function finalizeOrder(Order $order, ?string $intentId = null): void
    {
        try {
            DB::transaction(function () use ($order, $intentId) {
                $order->update([
                    'payment_status'     => 'paid',
                    'payment_intent_id'  => $intentId,
                    'courier_name'       => null,
                    'tracking_number'    => null,
                    'estimated_delivery' => null,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('[Payment] finalizeOrder error: ' . $e->getMessage());
        }

        try {
            $this->notify->orderPlaced($order->fresh());
            $this->notify->newOrderForArtisan($order->fresh());
        } catch (\Exception $e) {
            Log::info('[Payment] finalizeOrder notification skipped: ' . $e->getMessage());
        }
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