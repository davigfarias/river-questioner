<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Book;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class DeleteBook
{
    public function handle(Book $book): Outcome
    {
        try {
            $book->delete();

            return Outcome::success(message: 'Livro excluído.');
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível excluir o livro.');
        }
    }
}
