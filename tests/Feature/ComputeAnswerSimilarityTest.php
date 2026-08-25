<?php

namespace Tests\Feature;

use App\Actions\ComputeAnswerSimilarity;
use Tests\TestCase;

class ComputeAnswerSimilarityTest extends TestCase
{
    public function test_identical_word_lists_score_100(): void
    {
        $score = (new ComputeAnswerSimilarity)->handle(
            ['deus', 'é', 'amor'],
            ['deus', 'é', 'amor'],
        )->data;

        $this->assertSame(100, $score);
    }

    public function test_completely_different_word_lists_score_zero(): void
    {
        $score = (new ComputeAnswerSimilarity)->handle(
            ['deus', 'é', 'amor'],
            ['gato', 'azul', 'correu'],
        )->data;

        $this->assertSame(0, $score);
    }

    public function test_two_empty_lists_score_100(): void
    {
        $score = (new ComputeAnswerSimilarity)->handle([], [])->data;

        $this->assertSame(100, $score);
    }

    public function test_a_partial_overlap_scores_between_zero_and_a_hundred(): void
    {
        $score = (new ComputeAnswerSimilarity)->handle(
            ['deus', 'é', 'amor', 'leal'],
            ['deus', 'é', 'bondade'],
        )->data;

        $this->assertGreaterThan(0, $score);
        $this->assertLessThan(100, $score);
    }
}
