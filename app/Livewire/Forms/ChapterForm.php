<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\DTO\ChapterData;
use App\Models\Chapter;
use Livewire\Form;

class ChapterForm extends Form
{
    public string $title = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
        ];
    }

    public function fillFromChapter(Chapter $chapter): void
    {
        $this->title = $chapter->title;
    }

    public function toData(): ChapterData
    {
        return new ChapterData(
            title: $this->title,
        );
    }
}
