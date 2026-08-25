<?php

declare(strict_types=1);

namespace App\Actions;

use App\Support\Outcome;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Normalizer;

final readonly class NormalizeAnswerText
{
    public function handle(string $text): Outcome
    {
        try {
            // Fold Latin accents (á → a, ç → c, ...) so a Portuguese answer
            // typed without diacritics isn't penalized against a reference
            // answer that has them. NFD splits each accented letter into
            // base + combining mark, which \p{Mn} then strips; non-Latin
            // scripts (e.g. Hebrew, Greek) don't decompose this way and are
            // left untouched.
            $decomposed = Normalizer::normalize($text, Normalizer::FORM_D) ?: $text;

            $normalized = (string) Str::of($decomposed)
                ->lower()
                ->replaceMatches('/\p{Mn}/u', '')
                ->replaceMatches('/[^\p{L}\p{N}\s]/u', '')
                ->squish();

            return Outcome::noViewMessage(data: $normalized === '' ? [] : explode(' ', $normalized));
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível normalizar o texto da resposta.');
        }
    }
}
