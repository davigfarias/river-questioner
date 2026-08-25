<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\DTO\BookData;
use App\Models\Book;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class BookForm extends Form
{
    public string $title = '';

    public string $author = '';

    public ?TemporaryUploadedFile $cover = null;

    /**
     * Only meaningful while editing: clears an existing cover without
     * replacing it. Ignored when a new `cover` upload is also present.
     */
    public bool $removeCover = false;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'cover' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function fillFromBook(Book $book): void
    {
        $this->title = $book->title;
        $this->author = $book->author ?? '';
        $this->cover = null;
        $this->removeCover = false;
    }

    public function toData(?string $coverPath): BookData
    {
        return new BookData(
            title: $this->title,
            author: $this->author !== '' ? $this->author : null,
            coverPath: $coverPath,
        );
    }
}
