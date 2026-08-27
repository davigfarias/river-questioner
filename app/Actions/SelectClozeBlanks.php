<?php

declare(strict_types=1);

namespace App\Actions;

use App\Support\{Outcome, PortugueseStopwords, TextNormalizer};
use Illuminate\Support\Facades\Log;

final readonly class SelectClozeBlanks
{
    private const BLANK_RATIO = 0.30;

    private const MIN_BLANKS = 1;

    private const MIN_WORD_LENGTH = 2;

    /**
     * Pick which word indices to turn into cloze blanks. Eligible words are
     * long enough, non-numeric, and not Portuguese function words. Roughly
     * BLANK_RATIO of the eligible words are chosen at random (at least
     * MIN_BLANKS). Returns a sorted list of word indices, or an empty list
     * when nothing is eligible.
     *
     * @param  array<int, array{word: bool, text: string, index: int|null}>  $tokens
     * @return Outcome data: array<int, int>
     */
    public function handle(array $tokens, ?float $ratio = null): Outcome
    {
        try {
            $ratio ??= self::BLANK_RATIO;

            $eligible = [];

            foreach ($tokens as $token) {
                if (! $token['word']) {
                    continue;
                }

                $folded = TextNormalizer::fold($token['text']);

                if (mb_strlen($folded) < self::MIN_WORD_LENGTH) {
                    continue;
                }

                if (ctype_digit($folded)) {
                    continue;
                }

                if (PortugueseStopwords::contains($token['text'])) {
                    continue;
                }

                $eligible[] = $token['index'];
            }

            if ($eligible === []) {
                return Outcome::noViewMessage(data: []);
            }

            $count = min(
                count($eligible),
                max(self::MIN_BLANKS, (int) round(count($eligible) * $ratio)),
            );

            shuffle($eligible);
            $chosen = array_slice($eligible, 0, $count);
            sort($chosen);

            return Outcome::noViewMessage(data: $chosen);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível selecionar as lacunas do Cloze.');
        }
    }
}
