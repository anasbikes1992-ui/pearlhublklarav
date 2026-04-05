<?php

namespace App\Services;

use App\Events\ChatMessageSent;
use App\Jobs\ProcessVoiceMessageJob;
use App\Models\Booking;
use App\Models\Listing;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

class MessageService
{
    public function __construct(
        private readonly TranslationService $translationService,
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function history(string $listingId, string $userId): Collection
    {
        $this->assertCanParticipateInListingChat($listingId, $userId);

        return Message::query()
            ->where('listing_id', $listingId)
            ->where(function ($query) use ($userId): void {
                $query->where('sender_id', $userId)->orWhere('receiver_id', $userId);
            })
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function sendText(string $senderId, array $payload): Message
    {
        $listing = Listing::query()->findOrFail($payload['listing_id']);
        $receiverId = (string) $payload['receiver_id'];

        $this->assertCanParticipateInListingChat($listing->id, $senderId, $receiverId);
        $this->ensurePreBookingOnly($listing->id, $senderId, $receiverId);

        $sourceLocale = (string) ($payload['source_locale'] ?? 'en');
        $targetLocale = (string) ($payload['target_locale'] ?? 'en');
        $text = (string) $payload['message'];
        $translated = $this->translationService->translate($text, $sourceLocale, $targetLocale);

        $message = Message::query()->create([
            'listing_id' => $listing->id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $text,
            'is_voice' => false,
            'original_text' => $text,
            'translated_text' => $translated,
        ]);

        $this->auditLogService->log($senderId, 'chat.message.sent', Message::class, $message->id, [
            'listing_id' => $listing->id,
            'receiver_id' => $receiverId,
            'is_voice' => false,
        ]);

        event(new ChatMessageSent($message));

        return $message;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function sendVoice(string $senderId, array $payload): Message
    {
        $listing = Listing::query()->findOrFail($payload['listing_id']);
        $receiverId = (string) $payload['receiver_id'];

        $this->assertCanParticipateInListingChat($listing->id, $senderId, $receiverId);
        $this->ensurePreBookingOnly($listing->id, $senderId, $receiverId);

        $message = Message::query()->create([
            'listing_id' => $listing->id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => null,
            'is_voice' => true,
            'original_text' => null,
            'translated_text' => null,
        ]);

        ProcessVoiceMessageJob::dispatch($message->id, (string) $payload['audio_url'], (string) ($payload['target_locale'] ?? 'en'))
            ->onQueue('translations');

        $this->auditLogService->log($senderId, 'chat.voice_message.queued', Message::class, $message->id, [
            'listing_id' => $listing->id,
            'receiver_id' => $receiverId,
        ]);

        event(new ChatMessageSent($message));

        return $message;
    }

    private function assertCanParticipateInListingChat(string $listingId, string $userId, ?string $counterpartyId = null): void
    {
        $listing = Listing::query()->findOrFail($listingId);

        $allowed = $listing->provider_id === $userId;

        if (! $allowed && $counterpartyId !== null) {
            $allowed = $counterpartyId === $listing->provider_id;
        }

        if (! $allowed) {
            $allowed = Message::query()
                ->where('listing_id', $listingId)
                ->where(function ($query) use ($userId): void {
                    $query->where('sender_id', $userId)->orWhere('receiver_id', $userId);
                })
                ->exists();
        }

        if (! $allowed) {
            $allowed = Booking::query()
                ->where('listing_id', $listingId)
                ->where('customer_id', $userId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();
        }

        if (! $allowed) {
            throw new RuntimeException('Only listing participants can access this chat.');
        }
    }

    private function ensurePreBookingOnly(string $listingId, string $senderId, string $receiverId): void
    {
        $bookingExists = Booking::query()
            ->where('listing_id', $listingId)
            ->whereIn('status', ['confirmed', 'completed'])
            ->where(function ($query) use ($senderId, $receiverId): void {
                $query->whereIn('customer_id', [$senderId, $receiverId]);
            })
            ->exists();

        if ($bookingExists) {
            throw new RuntimeException('Pre-booking chat is no longer available after booking confirmation.');
        }
    }
}
