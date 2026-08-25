<?php

declare(strict_types=1);

namespace App\DTO;

use App\Models\Book;

final readonly class LibraryBookRow
{
    public function __construct(
        public Book $book,
        public BookProgress $progress,
    ) {}
}
