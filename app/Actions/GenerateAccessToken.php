<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTO\AccessTokenDTO;
use App\Models\AccessToken;
use App\Support\Outcome;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;

final readonly class GenerateAccessToken
{
    public function __construct(
        #[Config('access_tokens.max_generation_attempts')] private int $maxAttempts = 5,
    ) {}

    /**
     * On success, `Outcome::$data` is `array{plainTextToken: string, token: AccessTokenDTO}`.
     */
    public function handle(string $name): Outcome
    {
        for ($attempt = 1; $attempt <= $this->maxAttempts; $attempt++) {
            try {
                $plainText = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

                $token = AccessToken::create([
                    'name' => $name,
                    'token' => hash('sha256', $plainText),
                ]);

                return Outcome::success(message: 'Token generated successfully.', data: [
                    'plainTextToken' => $plainText,
                    'token' => AccessTokenDTO::fromModel($token),
                ]);
            } catch (UniqueConstraintViolationException) {
                continue;
            } catch (\Throwable $e) {
                Log::error(self::class.': '.$e->getMessage());

                return Outcome::failure(message: 'Unable to generate the token.');
            }
        }

        return Outcome::failure(message: 'Unable to generate a unique code, please try again.');
    }
}
