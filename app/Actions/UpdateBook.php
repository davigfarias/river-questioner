<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTO\BookData;
use App\Models\Book;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class UpdateBook
{
    public function handle(Book $book, BookData $data): Outcome
    {
        try {
            $book->update([
                'title' => $data->title,
                'author' => $data->author,
                'cover_path' => $data->coverPath,
            ]);

            return Outcome::success(message: 'Livro atualizado.', data: $book);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível atualizar o livro.');
        }
    }
}
