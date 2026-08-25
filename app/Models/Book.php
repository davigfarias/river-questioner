<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\{Fillable, Scope, UseFactory};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property int $access_token_id
 * @property string $title
 * @property string|null $author
 * @property string|null $cover_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[UseFactory(BookFactory::class)]
#[Fillable(['access_token_id', 'title', 'author', 'cover_path'])]
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    use Searchable;

    /**
     * @return BelongsTo<AccessToken, $this>
     */
    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(AccessToken::class);
    }

    /**
     * @return HasMany<Chapter, $this>
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('position');
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path === null ? null : Storage::url($this->cover_path);
    }

    /**
     * Scope a query to only include books belonging to the given access token.
     */
    #[Scope]
    protected function forAccessToken(Builder $query, AccessToken $accessToken): void
    {
        $query->where('access_token_id', $accessToken->id);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
        ];
    }
}
