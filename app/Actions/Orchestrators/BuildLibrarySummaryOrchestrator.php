<?php

declare(strict_types=1);

namespace App\Actions\Orchestrators;

use App\Actions\ComputeBookProgress;
use App\DTO\LibraryBookRow;
use App\Models\{AccessToken, Book};
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class BuildLibrarySummaryOrchestrator
{
    public function __construct(private ComputeBookProgress $computeBookProgress) {}

    public function handle(AccessToken $accessToken, string $search = ''): Outcome
    {
        try {
            $books = $search !== ''
                ? Book::search($search)->where('access_token_id', $accessToken->id)->get()
                : Book::forAccessToken($accessToken)->get();

            $books = $books->sortByDesc('created_at')->values();

            $rows = [];

            foreach ($books as $book) {
                $progressOutcome = $this->computeBookProgress->handle($book, $accessToken);

                if (! $progressOutcome->success) {
                    return $progressOutcome;
                }

                $rows[] = new LibraryBookRow(book: $book, progress: $progressOutcome->data);
            }

            return Outcome::noViewMessage(data: collect($rows));
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível montar o resumo da biblioteca.');
        }
    }
}
