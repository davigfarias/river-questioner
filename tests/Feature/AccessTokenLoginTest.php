<?php

namespace Tests\Feature;

use App\Models\AccessToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class AccessTokenLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_valid_code_logs_the_visitor_in_and_redirects_to_books(): void
    {
        $token = AccessToken::factory()->create([
            'token' => hash('sha256', '1234'),
        ]);

        Livewire::test('pages::auth-code')
            ->set('code', '1234')
            ->call('joinSession')
            ->assertRedirect(route('books'));

        $this->assertSame($token->id, session('access_token_id'));
    }

    public function test_an_invalid_code_does_not_authenticate(): void
    {
        AccessToken::factory()->create(['token' => hash('sha256', '1234')]);

        Livewire::test('pages::auth-code')
            ->set('code', '9999')
            ->call('joinSession')
            ->assertNoRedirect();

        $this->assertNull(session('access_token_id'));
    }

    public function test_a_revoked_code_cannot_log_in(): void
    {
        AccessToken::factory()->revoked()->create(['token' => hash('sha256', '1234')]);

        Livewire::test('pages::auth-code')
            ->set('code', '1234')
            ->call('joinSession')
            ->assertNoRedirect();

        $this->assertNull(session('access_token_id'));
    }

    public function test_non_digit_or_wrong_length_codes_fail_validation(): void
    {
        Livewire::test('pages::auth-code')
            ->set('code', 'ab')
            ->call('joinSession')
            ->assertHasErrors(['code']);
    }

    public function test_repeated_failed_attempts_are_throttled_per_ip(): void
    {
        AccessToken::factory()->create(['token' => hash('sha256', '1234')]);

        RateLimiter::clear('access-token-login:127.0.0.1');

        for ($i = 0; $i < 8; $i++) {
            Livewire::test('pages::auth-code')
                ->set('code', '9999')
                ->call('joinSession');
        }

        Livewire::test('pages::auth-code')
            ->set('code', '1234')
            ->call('joinSession')
            ->assertNoRedirect();

        $this->assertNull(session('access_token_id'));
    }

    public function test_an_already_authenticated_visitor_is_redirected_away_from_login(): void
    {
        $token = AccessToken::factory()->create();
        session(['access_token_id' => $token->id]);

        Livewire::test('pages::auth-code')
            ->assertRedirect(route('books'));
    }
}
