<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TranslationService
{
    /**
     * @var string[]
     */
    private array $supportedLocales = ['en', 'si', 'ta', 'hi', 'ar', 'zh', 'fr', 'de', 'es', 'ja'];

    /**
     * @return array<string, string>
     */
    public function translateAll(string $text, string $sourceLocale = 'en'): array
    {
        $result = [];
        foreach ($this->supportedLocales as $locale) {
            if ($locale === $sourceLocale) {
                $result[$locale] = $text;
                continue;
            }

            $result[$locale] = $this->translate($text, $sourceLocale, $locale);
        }

        return $result;
    }

    public function translate(string $text, string $sourceLocale, string $targetLocale): string
    {
        if (trim($text) === '') {
            return '';
        }

        $provider = (string) config('services.translation.provider', 'libretranslate');

        return $provider === 'google'
            ? $this->translateWithGoogle($text, $sourceLocale, $targetLocale)
            : $this->translateWithLibreTranslate($text, $sourceLocale, $targetLocale);
    }

    private function translateWithGoogle(string $text, string $sourceLocale, string $targetLocale): string
    {
        $apiKey = (string) config('services.translation.google_api_key');

        if ($apiKey === '') {
            throw new RuntimeException('Google Translate key is not configured.');
        }

        $response = Http::timeout(10)->post('https://translation.googleapis.com/language/translate/v2', [
            'q' => $text,
            'source' => $sourceLocale,
            'target' => $targetLocale,
            'format' => 'text',
            'key' => $apiKey,
        ])->throw()->json();

        return (string) data_get($response, 'data.translations.0.translatedText', $text);
    }

    private function translateWithLibreTranslate(string $text, string $sourceLocale, string $targetLocale): string
    {
        $url = (string) config('services.translation.base_url', 'https://libretranslate.com/translate');
        $apiKey = (string) config('services.translation.api_key', '');

        $payload = [
            'q' => $text,
            'source' => $sourceLocale,
            'target' => $targetLocale,
            'format' => 'text',
        ];

        if ($apiKey !== '') {
            $payload['api_key'] = $apiKey;
        }

        $response = Http::timeout(10)->post($url, $payload)->throw()->json();

        return (string) data_get($response, 'translatedText', $text);
    }
}
