<?php

namespace App\Jobs;

use App\Models\Message;
use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ProcessVoiceMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public string $messageId, public string $audioUrl, public string $targetLocale)
    {
    }

    public function handle(TranslationService $translationService): void
    {
        $message = Message::query()->findOrFail($this->messageId);

        $transcription = $this->transcribe($this->audioUrl);
        $translated = $translationService->translate($transcription, 'auto', $this->targetLocale);

        $message->update([
            'original_text' => $transcription,
            'translated_text' => $translated,
            'message' => $translated,
            'is_voice' => true,
        ]);
    }

    private function transcribe(string $audioUrl): string
    {
        if (str_starts_with($audioUrl, 'data:audio')) {
            return $this->transcribeWithWhisper($audioUrl);
        }

        $provider = (string) config('services.voice.provider', 'deepgram');

        if ($provider === 'whisper') {
            return $this->transcribeWithWhisper($audioUrl);
        }

        return $this->transcribeWithDeepgram($audioUrl);
    }

    private function transcribeWithDeepgram(string $audioUrl): string
    {
        $apiKey = (string) config('services.deepgram.key');

        $response = Http::withToken($apiKey)
            ->timeout(20)
            ->post('https://api.deepgram.com/v1/listen', [
                'url' => $audioUrl,
            ])
            ->throw()
            ->json();

        return (string) data_get($response, 'results.channels.0.alternatives.0.transcript', '');
    }

    private function transcribeWithWhisper(string $audioUrl): string
    {
        $apiKey = (string) config('services.openai.key');
        $audioContent = str_starts_with($audioUrl, 'data:')
            ? $this->extractBinaryFromDataUrl($audioUrl)
            : Http::timeout(20)->get($audioUrl)->throw()->body();

        $response = Http::withToken($apiKey)
            ->attach('file', $audioContent, 'voice-message.webm')
            ->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1',
            ])
            ->throw()
            ->json();

        return (string) data_get($response, 'text', '');
    }

    private function extractBinaryFromDataUrl(string $dataUrl): string
    {
        $parts = explode(',', $dataUrl, 2);
        if (count($parts) !== 2) {
            return '';
        }

        return base64_decode($parts[1], true) ?: '';
    }
}
