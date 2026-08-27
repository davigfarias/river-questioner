<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\DTO\QuestionData;
use App\Models\Question;
use Livewire\Form;

class QuestionForm extends Form
{
    public string $prompt = '';

    public string $referenceAnswer = '';

    public string $keywords = '';

    public bool $isCloze = false;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'max:2000'],
            'referenceAnswer' => ['required', 'string', 'max:5000'],
            'keywords' => ['nullable', 'string', 'max:1000'],
            'isCloze' => ['boolean'],
        ];
    }

    public function fillFromQuestion(Question $question): void
    {
        $this->prompt = $question->prompt;
        $this->referenceAnswer = $question->reference_answer;
        $this->keywords = $question->keywords ?? '';
        $this->isCloze = $question->is_cloze;
    }

    public function toData(): QuestionData
    {
        return new QuestionData(
            prompt: $this->prompt,
            referenceAnswer: $this->referenceAnswer,
            keywords: $this->keywords !== '' ? $this->keywords : null,
            isCloze: $this->isCloze,
        );
    }
}
