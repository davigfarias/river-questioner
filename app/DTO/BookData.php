<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class BookData
{
    public function __construct(
        public string $title,
        public ?string $author,
        public ?string $coverPath,
    ) {}
}
