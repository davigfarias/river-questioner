<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTO\BookData;
use App\Models\{AccessToken, Book};
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class CreateBook
{
    public function handle(AccessToken $accessToken, BookData $data): Outcome
    {
        try {
            $book = Book::create([
                'access_token_id' => $accessToken->id,
                'title' => $data->title,
                'author' => $data->author,
                'cover_path' => $data->coverPath,
            ]);

            return Outcome::success(message: 'Livro criado.', data: $book);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível criar o livro.');
        }
    }
}
