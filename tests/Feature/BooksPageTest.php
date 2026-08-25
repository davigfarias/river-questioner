<?php

namespace Tests\Feature;

use App\Models\{AccessToken, Book};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BooksPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_only_lists_books_belonging_to_the_current_access_token(): void
    {
        $token = AccessToken::factory()->create();
        $otherToken = AccessToken::factory()->create();

        $mine = Book::factory()->for($token)->create(['title' => 'Meu livro']);
        Book::factory()->for($otherToken)->create(['title' => 'Livro alheio']);

        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::books')
            ->assertSee('Meu livro')
            ->assertDontSee('Livro alheio');

        $this->assertTrue(Book::whereKey($mine->id)->exists());
    }

    public function test_it_creates_a_book_with_a_cover_image(): void
    {
        Storage::fake('public');

        $token = AccessToken::factory()->create();
        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::books')
            ->set('form.title', 'Gramática Hebraica')
            ->set('form.author', 'Kelley')
            ->set('form.cover', UploadedFile::fake()->image('cover.jpg'))
            ->call('save');

        $book = Book::where('title', 'Gramática Hebraica')->firstOrFail();

        $this->assertSame($token->id, $book->access_token_id);
        $this->assertSame('Kelley', $book->author);
        $this->assertNotNull($book->cover_path);
        Storage::disk('public')->assertExists($book->cover_path);
    }

    public function test_title_is_required(): void
    {
        $token = AccessToken::factory()->create();
        $this->withSession(['access_token_id' => $token->id]);

        Livewire::test('pages::books')
            ->set('form.title', '')
            ->call('save')
            ->assertHasErrors(['form.title' => 'required']);
    }
}
