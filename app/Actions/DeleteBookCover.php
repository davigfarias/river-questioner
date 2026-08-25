<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Book;
use App\Support\Outcome;
use Illuminate\Support\Facades\{Log, Storage};

final readonly class DeleteBookCover
{
    public function handle(Book $book): Outcome
    {
        try {
            if ($book->cover_path !== null) {
                Storage::delete($book->cover_path);
            }

            return Outcome::noViewMessage();
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível remover a capa do livro.');
        }
    }
}
