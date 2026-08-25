<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\AccessToken;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class RevokeAccessToken
{
    public function handle(int $id): Outcome
    {
        try {
            $revoked = AccessToken::where('id', $id)
                ->active()
                ->update(['revoked_at' => now()]);

            if (! $revoked) {
                return Outcome::failure(message: 'Token not found or already revoked.');
            }

            return Outcome::success(message: 'Token revoked successfully.');
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Unable to revoke the token.');
        }
    }
}
