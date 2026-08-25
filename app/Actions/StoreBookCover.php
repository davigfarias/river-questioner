<?php

declare(strict_types=1);

namespace App\Actions;

use App\Support\Outcome;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final readonly class StoreBookCover
{
    public function handle(UploadedFile $file): Outcome
    {
        try {
            $path = $file->store('books');

            throw_if($path === false, new RuntimeException('Failed to store book cover.'));

            return Outcome::noViewMessage(data: $path);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível enviar a capa do livro.');
        }
    }
}
