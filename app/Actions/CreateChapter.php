<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTO\ChapterData;
use App\Models\Book;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class CreateChapter
{
    public function handle(Book $book, ChapterData $data): Outcome
    {
        try {
            $chapter = $book->chapters()->create([
                'title' => $data->title,
                'position' => $book->chapters()->count(),
            ]);

            return Outcome::success(message: 'Capítulo criado.', data: $chapter);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível criar o capítulo.');
        }
    }
}
