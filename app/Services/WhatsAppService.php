<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Service - Send notifications via WhatsApp Business API
 * Can use Twilio, Meta Business API, or other providers
 */
class WhatsAppService
{
    private string $apiKey;
    private string $apiUrl;
    private string $fromNumber;
    private bool $enabled;
    private AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->apiKey = config('services.whatsapp.api_key', '');
        $this->apiUrl = config('services.whatsapp.api_url', '');
        $this->fromNumber = config('services.whatsapp.from_number', '');
        $this->enabled = config('services.whatsapp.enabled', false);
        $this->auditLogService = $auditLogService;
    }

    /**
     * Send booking confirmation
     */
    public function sendBookingConfirmation(Booking $booking): bool
    {
        $message = sprintf(
            "Hello %s!\n\nYour booking has been confirmed:\n" .
            "📅 %s to %s\n" .
            "🏠 %s\n" .
            "💰 LKR %s\n\n" .
            "Thank you for choosing PearlHub!",
            $booking->customer->full_name,
            $booking->start_at->format('M d, Y'),
            $booking->end_at->format('M d, Y'),
            $booking->listing->title,
            number_format($booking->total_amount, 2)
        );

        return $this->send($booking->customer->phone, $message);
    }

    /**
     * Send booking reminder
     */
    public function sendBookingReminder(Booking $booking, int $hoursBefore = 24): bool
    {
        $message = sprintf(
            "Reminder: Your booking at %s is coming up!\n\n" .
            "📅 Check-in: %s\n" .
            "📍 %s\n\n" .
            "We look forward to hosting you!",
            $booking->listing->title,
            $booking->start_at->format('M d, Y h:i A'),
            $booking->listing->location ?? 'Location details in app'
        );

        return $this->send($booking->customer->phone, $message);
    }

    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation(User $user, float $amount, string $reference): bool
    {
        $message = sprintf(
            "Payment Received! 💰\n\n" .
            "Amount: LKR %s\n" .
            "Reference: %s\n" .
            "Status: Confirmed\n\n" .
            "Thank you for your payment.",
            number_format($amount, 2),
            $reference
        );

        return $this->send($user->phone, $message);
    }

    /**
     * Send payout notification to provider
     */
    public function sendPayoutNotification(User $provider, float $amount, string $reference): bool
    {
        $message = sprintf(
            "Payout Processed! 🎉\n\n" .
            "Amount: LKR %s\n" .
            "Reference: %s\n" .
            "Status: Sent to your account\n\n" .
            "Keep up the great work!",
            number_format($amount, 2),
            $reference
        );

        return $this->send($provider->phone, $message);
    }

    /**
     * Send KYC verification notification
     */
    public function sendKycNotification(User $user, string $status): bool
    {
        $message = match ($status) {
            'verified' => sprintf(
                "Congratulations %s! 🎉\n\n" .
                "Your KYC verification is complete.\n" .
                "You now have full access to PearlHub features.",
                $user->full_name
            ),
            'rejected' => sprintf(
                "Hello %s,\n\n" .
                "Your KYC verification requires attention.\n" .
                "Please check your email or app for details.",
                $user->full_name
            ),
            'additional_info_needed' => sprintf(
                "Hello %s,\n\n" .
                "We need additional documents for your KYC.\n" .
                "Please check your email or app for details.",
                $user->full_name
            ),
            default => sprintf(
                "Hello %s,\n\n" .
                "Your KYC status has been updated to: %s",
                $user->full_name,
                $status
            ),
        };

        return $this->send($user->phone, $message);
    }

    /**
     * Send referral bonus notification
     */
    public function sendReferralBonusNotification(User $user, float $amount, int $points): bool
    {
        $message = sprintf(
            "Referral Bonus Received! 🎁\n\n" .
            "Cash Bonus: LKR %s\n" .
            "Pearl Points: %d\n\n" .
            "Keep sharing your referral code!",
            number_format($amount, 2),
            $points
        );

        return $this->send($user->phone, $message);
    }

    /**
     * Send OTP for verification
     */
    public function sendOtp(string $phone, string $otp): bool
    {
        $message = sprintf(
            "Your PearlHub verification code is: %s\n" .
            "This code will expire in 5 minutes.\n" .
            "Do not share this code with anyone.",
            $otp
        );

        return $this->send($phone, $message);
    }

    /**
     * Send generic message
     */
    public function sendMessage(string $phone, string $template, array $variables = []): bool
    {
        $message = $this-> interpolateTemplate($template, $variables);
        return $this->send($phone, $message);
    }

    /**
     * Check if service is enabled and configured
     */
    public function isConfigured(): bool
    {
        return $this->enabled && ! empty($this->apiKey) && ! empty($this->fromNumber);
    }

    /**
     * Send WhatsApp message
     */
    private function send(string $to, string $message): bool
    {
        if (! $this->isConfigured()) {
            Log::warning('WhatsApp service not configured');
            return false;
        }

        // Format phone number (remove leading zero, add country code)
        $to = $this->formatPhoneNumber($to);

        try {
            // Implementation depends on provider (Twilio, Meta, etc.)
            // This is a placeholder for the actual API call
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'to' => $to,
                'from' => $this->fromNumber,
                'body' => $message,
            ]);

            if ($response->successful()) {
                $this->auditLogService->log(
                    null,
                    'whatsapp.sent',
                    null,
                    null,
                    ['to' => $to, 'message_length' => strlen($message)]
                );
                return true;
            }

            Log::error('WhatsApp send failed', [
                'to' => $to,
                'response' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp send error', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Format phone number for WhatsApp
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with Sri Lanka country code
        if (str_starts_with($phone, '0')) {
            $phone = '94' . substr($phone, 1);
        }

        // If doesn't start with country code, add Sri Lanka code
        if (! str_starts_with($phone, '94')) {
            $phone = '94' . $phone;
        }

        return $phone;
    }

    /**
     * Interpolate template variables
     */
    private function interpolateTemplate(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }

    /**
     * Get message templates
     */
    public function getTemplates(): array
    {
        return [
            'booking_confirmation' => 'Hello {name}! Your booking at {listing} is confirmed for {dates}.',
            'booking_reminder' => 'Reminder: Your booking at {listing} is tomorrow!',
            'payment_received' => 'Payment of LKR {amount} received. Reference: {reference}',
            'payout_sent' => 'Payout of LKR {amount} sent. Reference: {reference}',
            'kyc_verified' => 'Congratulations! Your KYC verification is complete.',
            'referral_bonus' => 'You received LKR {amount} and {points} points from referrals!',
        ];
    }
}
