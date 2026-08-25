<?php

namespace Tests\Feature;

use App\Models\AccessToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class EnsureAccessTokenIsValidMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/livros')->assertRedirect(route('home'));
    }

    public function test_a_plain_guest_visit_does_not_flag_anything(): void
    {
        Log::shouldReceive('warning')->never();

        $this->get('/livros')
            ->assertRedirect(route('home'))
            ->assertSessionMissing('access_token_invalidated');
    }

    public function test_a_valid_session_can_reach_a_protected_route(): void
    {
        $token = AccessToken::factory()->create();

        $this->withSession(['access_token_id' => $token->id])
            ->get('/livros')
            ->assertOk();
    }

    public function test_revoking_the_token_kills_an_already_active_session_on_the_next_request(): void
    {
        $token = AccessToken::factory()->create();

        $this->withSession(['access_token_id' => $token->id])
            ->get('/livros')
            ->assertOk();

        $token->update(['revoked_at' => now()]);

        $this->withSession(['access_token_id' => $token->id])
            ->get('/livros')
            ->assertRedirect(route('home'))
            ->assertSessionHas('access_token_invalidated', true);
    }

    public function test_an_unknown_token_id_in_the_session_is_rejected(): void
    {
        $this->withSession(['access_token_id' => 999999])
            ->get('/livros')
            ->assertRedirect(route('home'))
            ->assertSessionHas('access_token_invalidated', true);
    }
}
