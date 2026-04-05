<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ChatController extends BaseApiController
{
    public function __construct(private readonly MessageService $messageService)
    {
    }

    public function history(Request $request, string $listingId): JsonResponse
    {
        try {
            $messages = $this->messageService->history($listingId, $request->user()->id);

            return $this->success($messages);
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage(), [], 403);
        }
    }

    public function sendText(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'listing_id' => ['required', 'uuid', 'exists:listings,id'],
            'receiver_id' => ['required', 'uuid', 'exists:users,id'],
            'message' => ['required', 'string', 'max:5000'],
            'source_locale' => ['nullable', 'string', 'size:2'],
            'target_locale' => ['nullable', 'string', 'size:2'],
        ]);

        try {
            $message = $this->messageService->sendText($request->user()->id, $validated);

            return $this->success($message, 'Message sent', 201);
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage(), [], 422);
        }
    }

    public function sendVoice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'listing_id' => ['required', 'uuid', 'exists:listings,id'],
            'receiver_id' => ['required', 'uuid', 'exists:users,id'],
            'audio_url' => ['required', 'string', 'max:300000'],
            'target_locale' => ['nullable', 'string', 'size:2'],
        ]);

        try {
            $message = $this->messageService->sendVoice($request->user()->id, $validated);

            return $this->success($message, 'Voice message queued', 202);
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage(), [], 422);
        }
    }
}
