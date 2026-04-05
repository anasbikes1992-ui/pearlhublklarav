<?php

namespace App\Services;

use App\Models\AiConciergeLog;
use Illuminate\Support\Facades\Http;

class AiConciergeService
{
    public function __construct(private readonly SearchService $searchService)
    {
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function chat(?string $userId, string $query, array $context = []): array
    {
        $searchResults = $this->searchService->search($query, [
            'vertical' => $context['vertical'] ?? null,
        ], 5);

        $prompt = $this->buildPrompt($query, $searchResults->items(), $context);

        [$response, $modelUsed] = $this->callPreferredModel($prompt);

        AiConciergeLog::query()->create([
            'user_id' => $userId,
            'query' => $query,
            'response' => $response,
            'model_used' => $modelUsed,
        ]);

        return [
            'reply' => $response,
            'model_used' => $modelUsed,
            'related_listings' => $searchResults->items(),
        ];
    }

    /**
     * @param array<int, mixed> $listings
     * @param array<string, mixed> $context
     */
    private function buildPrompt(string $query, array $listings, array $context): string
    {
        return implode("\n", [
            'You are PearlHub AI Concierge for Sri Lankan marketplace users.',
            'Answer briefly, include booking safety tips, and offer translation help if needed.',
            'Context: '.json_encode($context),
            'Relevant listings: '.json_encode($listings),
            'User query: '.$query,
        ]);
    }

    /**
     * @return array{0:string,1:string}
     */
    private function callPreferredModel(string $prompt): array
    {
        $xaiKey = (string) config('services.xai.key');
        if ($xaiKey !== '') {
            $response = Http::withToken($xaiKey)
                ->timeout(20)
                ->post('https://api.x.ai/v1/chat/completions', [
                    'model' => (string) config('services.xai.model', 'grok-2-latest'),
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a multilingual marketplace concierge.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                ])
                ->throw()
                ->json();

            return [
                (string) data_get($response, 'choices.0.message.content', 'I could not generate a response right now.'),
                (string) data_get($response, 'model', 'grok-2-latest'),
            ];
        }

        $openAiKey = (string) config('services.openai.key');
        $response = Http::withToken($openAiKey)
            ->timeout(20)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => (string) config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a multilingual marketplace concierge.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
            ])
            ->throw()
            ->json();

        return [
            (string) data_get($response, 'choices.0.message.content', 'I could not generate a response right now.'),
            (string) data_get($response, 'model', 'gpt-4o-mini'),
        ];
    }
}
