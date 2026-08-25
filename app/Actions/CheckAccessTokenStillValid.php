<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\AccessToken;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class CheckAccessTokenStillValid
{
    public function handle(int $id): Outcome
    {
        try {
            $active = AccessToken::where('id', $id)
                ->active()
                ->exists();

            if (! $active) {
                return Outcome::failure(message: 'Invalid or revoked token.');
            }

            return Outcome::noViewMessage();
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Unable to validate the token.');
        }
    }
}
