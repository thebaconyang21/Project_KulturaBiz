<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * NotificationService
 *
 * Simulates:
 *   - Twilio SMS API  (https://api.twilio.com/2010-04-01/)
 *   - Mailgun Email API (https://api.mailgun.net/v3/)
 *
 * To go live: set TWILIO_SID, TWILIO_AUTH_TOKEN, TWILIO_FROM_NUMBER,
 * MAILGUN_SECRET, MAILGUN_DOMAIN in .env and USE_REAL_NOTIFICATIONS=true.
 */
class NotificationService
{
    // ─────────────────────────────────────────
    // ORDER NOTIFICATION TRIGGERS
    // Call these from OrderController / Admin actions.
    // ─────────────────────────────────────────

    /**
     * Notify customer that their order was placed successfully.
     */
    public function orderPlaced(Order $order): void
    {
        $customer = $order->customer;

        $this->sendSms(
            $order->contact_number,
            "Hi {$order->recipient_name}! Your KulturaBiz order #{$order->order_number} has been placed. " .
            "Total: ₱" . number_format($order->total_amount, 2) . ". " .
            "Track it at: " . route('orders.track', $order->id)
        );

        $this->sendEmail(
            $customer->email,
            $customer->name,
            "Order Confirmed — #{$order->order_number}",
            $this->buildOrderPlacedEmailBody($order)
        );
    }

    /**
     * Notify customer their order is being processed by the artisan.
     */
    public function orderProcessing(Order $order): void
    {
        $this->sendSms(
            $order->contact_number,
            "KulturaBiz: Your order #{$order->order_number} is now being prepared by the artisan. " .
            "We'll notify you once it ships!"
        );

        $this->sendEmail(
            $order->customer->email,
            $order->customer->name,
            "Your order is being prepared — #{$order->order_number}",
            $this->buildStatusUpdateEmailBody($order, 'processing',
                'The artisan is carefully preparing your handcrafted items.')
        );
    }

    /**
     * Notify customer their order has shipped with tracking details.
     */
    public function orderShipped(Order $order): void
    {
        $this->sendSms(
            $order->contact_number,
            "KulturaBiz: Great news! Order #{$order->order_number} has shipped via {$order->courier_name}. " .
            "Tracking: {$order->tracking_number}. " .
            "Est. delivery: " . optional($order->estimated_delivery)->format('M d, Y') . ". " .
            "Track: " . route('orders.track', $order->id)
        );

        $this->sendEmail(
            $order->customer->email,
            $order->customer->name,
            "Your order is on its way! — #{$order->order_number}",
            $this->buildShippedEmailBody($order)
        );
    }

    /**
     * Notify customer their order has been delivered.
     */
    public function orderDelivered(Order $order): void
    {
        $this->sendSms(
            $order->contact_number,
            "KulturaBiz: Your order #{$order->order_number} has been delivered! " .
            "We hope you love your handcrafted item. Please leave a review: " .
            route('orders.track', $order->id)
        );

        $this->sendEmail(
            $order->customer->email,
            $order->customer->name,
            "Delivered! Your order #{$order->order_number} arrived",
            $this->buildDeliveredEmailBody($order)
        );
    }

    /**
     * Notify customer their order was cancelled.
     */
    public function orderCancelled(Order $order, string $reason = ''): void
    {
        $this->sendSms(
            $order->contact_number,
            "KulturaBiz: Your order #{$order->order_number} has been cancelled. " .
            ($reason ? "Reason: {$reason}. " : '') .
            "Contact us if you have questions."
        );

        $this->sendEmail(
            $order->customer->email,
            $order->customer->name,
            "Order Cancelled — #{$order->order_number}",
            $this->buildStatusUpdateEmailBody($order, 'cancelled',
                $reason ?: 'Your order has been cancelled.')
        );
    }

    /**
     * Notify artisan they have a new order to fulfil.
     */
    public function newOrderForArtisan(Order $order): void
    {
        $order->load('items.product.artisan');

        $artisans = $order->items
            ->map(fn($item) => $item->product?->artisan)
            ->filter()
            ->unique('id');

        foreach ($artisans as $artisan) {
            $artisanItems = $order->items->filter(
                fn($item) => $item->product?->artisan?->id === $artisan->id
            );

            $itemList = $artisanItems->map(
                fn($i) => "{$i->product_name} x{$i->quantity}"
            )->join(', ');

            $this->sendEmail(
                $artisan->email,
                $artisan->name,
                "New Order — #{$order->order_number}",
                $this->buildArtisanNewOrderEmailBody($order, $artisan, $itemList)
            );
        }
    }

    /**
     * Notify artisan their account was approved.
     */
    public function artisanApproved(User $artisan): void
    {
        $this->sendSms(
            $artisan->phone ?? '',
            "KulturaBiz: Congratulations {$artisan->name}! Your artisan account has been approved. " .
            "You can now start selling your crafts at " . config('app.url')
        );

        $this->sendEmail(
            $artisan->email,
            $artisan->name,
            "Your artisan account is approved!",
            $this->buildArtisanApprovedEmailBody($artisan)
        );
    }

    // ─────────────────────────────────────────
    // CORE SEND METHODS
    // ─────────────────────────────────────────

    /**
     * Send an SMS via Twilio (or simulate it).
     */
    public function sendSms(string $to, string $message): bool
    {
        if (empty($to)) {
            return false;
        }

        if (config('services.twilio.use_real')) {
            return $this->sendSmsReal($to, $message);
        }

        return $this->sendSmsSimulated($to, $message);
    }

    /**
     * Send an email via Mailgun (or simulate it).
     */
    public function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        if (config('services.mailgun.use_real')) {
            return $this->sendEmailReal($toEmail, $toName, $subject, $htmlBody);
        }

        return $this->sendEmailSimulated($toEmail, $toName, $subject, $htmlBody);
    }

    // ─────────────────────────────────────────
    // SIMULATED IMPLEMENTATIONS
    // ─────────────────────────────────────────

    private function sendSmsSimulated(string $to, string $message): bool
    {
        // Log to Laravel log file so you can see the SMS content during development
        Log::channel('stack')->info('[NotificationService] SMS (simulated)', [
            'to'      => $to,
            'message' => $message,
            'chars'   => strlen($message),
            'segments'=> ceil(strlen($message) / 160),
        ]);

        // Store in session flash so it appears in the UI for demo purposes
        if (session()->isStarted()) {
            $existing = session()->get('simulated_sms', []);
            $existing[] = ['to' => $to, 'message' => $message, 'sent_at' => now()->format('H:i:s')];
            session()->flash('simulated_sms', array_slice($existing, -3)); // keep last 3
        }

        return true;
    }

    private function sendEmailSimulated(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        Log::channel('stack')->info('[NotificationService] Email (simulated)', [
            'to'      => "{$toName} <{$toEmail}>",
            'subject' => $subject,
            'preview' => substr(strip_tags($htmlBody), 0, 200),
        ]);

        return true;
    }

    // ─────────────────────────────────────────
    // REAL API STUBS
    // ─────────────────────────────────────────

    private function sendSmsReal(string $to, string $message): bool
    {
        // Twilio REST API
        // Docs: https://www.twilio.com/docs/sms/api
        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.auth_token');
        $from  = config('services.twilio.from_number');

        // Normalize Philippine number to E.164 format
        $to = $this->normalizePhoneNumber($to);

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To'   => $to,
                'Body' => $message,
            ]);

        if (!$response->successful()) {
            Log::error('[NotificationService] Twilio SMS failed', [
                'to'    => $to,
                'error' => $response->json(),
            ]);
            return false;
        }

        Log::info('[NotificationService] SMS sent via Twilio', [
            'to'  => $to,
            'sid' => $response->json('sid'),
        ]);

        return true;
    }

    private function sendEmailReal(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        // Mailgun REST API
        // Docs: https://documentation.mailgun.com/en/latest/api-sending.html
        $domain    = config('services.mailgun.domain');
        $apiKey    = config('services.mailgun.secret');
        $fromEmail = config('mail.from.address', 'noreply@kulturabiz.com');
        $fromName  = config('mail.from.name', 'KulturaBiz');

        $response = Http::withBasicAuth('api', $apiKey)
            ->asForm()
            ->post("https://api.mailgun.net/v3/{$domain}/messages", [
                'from'    => "{$fromName} <{$fromEmail}>",
                'to'      => "{$toName} <{$toEmail}>",
                'subject' => $subject,
                'html'    => $htmlBody,
                'text'    => strip_tags($htmlBody),
                'o:tag'   => ['transactional', 'kulturabiz'],
            ]);

        if (!$response->successful()) {
            Log::error('[NotificationService] Mailgun email failed', [
                'to'    => $toEmail,
                'error' => $response->json(),
            ]);
            return false;
        }

        return true;
    }

    // ─────────────────────────────────────────
    // EMAIL BODY BUILDERS
    // ─────────────────────────────────────────

    private function buildOrderPlacedEmailBody(Order $order): string
    {
        $items    = $order->items->map(fn($i) => "<tr>
            <td style='padding:8px 0;border-bottom:1px solid #f0ebe3'>{$i->product_name}</td>
            <td style='padding:8px 0;border-bottom:1px solid #f0ebe3;text-align:center'>×{$i->quantity}</td>
            <td style='padding:8px 0;border-bottom:1px solid #f0ebe3;text-align:right'>₱" . number_format($i->subtotal, 2) . "</td>
        </tr>")->join('');
        $trackUrl = route('orders.track', $order->id);

        return $this->wrapEmail("
            <h2 style='color:#8B4513;margin:0 0 16px'>Order Confirmed! 🎉</h2>
            <p>Hi {$order->recipient_name},</p>
            <p>Thank you for supporting Mindanaoan artisans! Your order has been placed successfully.</p>
            <div style='background:#fdf8f0;border-radius:8px;padding:16px;margin:16px 0'>
                <strong>Order #:</strong> {$order->order_number}<br>
                <strong>Payment:</strong> " . strtoupper($order->payment_method) . "<br>
                <strong>Courier:</strong> {$order->courier_name}<br>
                <strong>Tracking:</strong> {$order->tracking_number}<br>
                <strong>Est. Delivery:</strong> " . optional($order->estimated_delivery)->format('F d, Y') . "
            </div>
            <table style='width:100%;border-collapse:collapse'>{$items}</table>
            <div style='text-align:right;margin:12px 0'>
                <strong>Total: ₱" . number_format($order->total_amount, 2) . "</strong>
            </div>
            <a href='{$trackUrl}' style='display:inline-block;background:#8B4513;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;margin-top:8px'>
                Track Your Order →
            </a>
        ");
    }

    private function buildShippedEmailBody(Order $order): string
    {
        $trackUrl = route('orders.track', $order->id);

        return $this->wrapEmail("
            <h2 style='color:#8B4513;margin:0 0 16px'>Your order is on its way! 🚚</h2>
            <p>Hi {$order->recipient_name},</p>
            <p>Great news! Your KulturaBiz order has shipped.</p>
            <div style='background:#fdf8f0;border-radius:8px;padding:16px;margin:16px 0'>
                <strong>Order #:</strong> {$order->order_number}<br>
                <strong>Courier:</strong> {$order->courier_name}<br>
                <strong>Tracking Number:</strong> <span style='font-family:monospace;font-size:14px'>{$order->tracking_number}</span><br>
                <strong>Est. Delivery:</strong> " . optional($order->estimated_delivery)->format('F d, Y') . "
            </div>
            <a href='{$trackUrl}' style='display:inline-block;background:#8B4513;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;'>
                Track Live Status →
            </a>
        ");
    }

    private function buildDeliveredEmailBody(Order $order): string
    {
        $reviewUrl = route('orders.track', $order->id);

        return $this->wrapEmail("
            <h2 style='color:#8B4513;margin:0 0 16px'>Your order has arrived! ✅</h2>
            <p>Hi {$order->recipient_name},</p>
            <p>Your handcrafted Mindanaoan items have been delivered. We hope you love them!</p>
            <p>Every purchase directly supports indigenous artisans and helps preserve cultural heritage.</p>
            <a href='{$reviewUrl}' style='display:inline-block;background:#D4AF37;color:#4a240b;font-weight:bold;padding:12px 24px;border-radius:8px;text-decoration:none;margin-top:8px'>
                ⭐ Leave a Review
            </a>
        ");
    }

    private function buildStatusUpdateEmailBody(Order $order, string $status, string $message): string
    {
        return $this->wrapEmail("
            <h2 style='color:#8B4513;margin:0 0 16px'>Order Update — #{$order->order_number}</h2>
            <p>Hi {$order->recipient_name},</p>
            <p>{$message}</p>
            <div style='background:#fdf8f0;border-radius:8px;padding:16px;margin:16px 0'>
                <strong>Status:</strong> " . ucfirst($status) . "
            </div>
        ");
    }

    private function buildArtisanNewOrderEmailBody(Order $order, User $artisan, string $items): string
    {
        return $this->wrapEmail("
            <h2 style='color:#8B4513;margin:0 0 16px'>New Order #{$order->order_number} 🎉</h2>
            <p>Hi {$artisan->name},</p>
            <p>You have a new order! Please prepare the following items:</p>
            <div style='background:#fdf8f0;border-radius:8px;padding:16px;margin:16px 0'>
                <strong>Items:</strong> {$items}<br>
                <strong>Ship to:</strong> {$order->city}, {$order->province}<br>
                <strong>Courier:</strong> {$order->courier_name}
            </div>
            <a href='" . route('artisan.orders') . "' style='display:inline-block;background:#8B4513;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;'>
                View Order →
            </a>
        ");
    }

    private function buildArtisanApprovedEmailBody(User $artisan): string
    {
        return $this->wrapEmail("
            <h2 style='color:#8B4513;margin:0 0 16px'>Welcome to KulturaBiz! 🏺</h2>
            <p>Hi {$artisan->name},</p>
            <p>Your artisan account for <strong>{$artisan->shop_name}</strong> has been approved by our team.</p>
            <p>You can now log in and start adding your handcrafted products to the marketplace.</p>
            <a href='" . route('artisan.dashboard') . "' style='display:inline-block;background:#8B4513;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;'>
                Go to Your Dashboard →
            </a>
        ");
    }

    private function wrapEmail(string $content): string
    {
        return "<!DOCTYPE html><html><body style='font-family:sans-serif;max-width:600px;margin:0 auto;padding:24px;color:#333'>
            <div style='border-bottom:3px solid #8B4513;padding-bottom:16px;margin-bottom:24px'>
                <span style='font-size:22px;font-weight:bold;color:#8B4513'>⬡ KulturaBiz</span>
                <span style='font-size:12px;color:#999;margin-left:8px'>Mindanaoan Heritage Marketplace</span>
            </div>
            {$content}
            <div style='border-top:1px solid #f0ebe3;margin-top:32px;padding-top:16px;font-size:12px;color:#999;text-align:center'>
                © " . date('Y') . " KulturaBiz — Preserving Mindanaoan Heritage
            </div>
        </body></html>";
    }

    // ─────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────

    private function normalizePhoneNumber(string $phone): string
    {
        // Convert 09XXXXXXXXX → +639XXXXXXXXX (Philippine E.164 format)
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            $clean = '63' . substr($clean, 1);
        }
        return '+' . $clean;
    }
}