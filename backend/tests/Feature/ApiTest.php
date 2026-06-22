<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // Teams

    public function test_get_teams_returns_four_teams(): void
    {
        $this->getJson('/api/v1/teams')
            ->assertOk()
            ->assertJsonCount(4, 'data');
    }

    public function test_get_teams_response_has_success_envelope(): void
    {
        $this->getJson('/api/v1/teams')
            ->assertOk()
            ->assertJsonStructure(['success', 'message', 'data', 'meta']);
    }

    // Fixtures

    public function test_get_fixtures_returns_empty_before_generate(): void
    {
        $this->getJson('/api/v1/fixtures')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_generate_fixtures_returns_201_with_12_matches(): void
    {
        $this->postJson('/api/v1/fixtures/generate')
            ->assertCreated()
            ->assertJsonCount(12, 'data');
    }

    public function test_generate_fixtures_twice_returns_422(): void
    {
        $this->postJson('/api/v1/fixtures/generate');
        $this->postJson('/api/v1/fixtures/generate')->assertUnprocessable();
    }

    public function test_get_fixtures_returns_12_matches_after_generate(): void
    {
        $this->postJson('/api/v1/fixtures/generate');

        $this->getJson('/api/v1/fixtures')
            ->assertOk()
            ->assertJsonCount(12, 'data');
    }

    // League table

    public function test_league_table_returns_four_standings(): void
    {
        $this->getJson('/api/v1/league-table')
            ->assertOk()
            ->assertJsonCount(4, 'data');
    }

    // Predictions

    public function test_predictions_before_week_4_returns_null_data(): void
    {
        $this->getJson('/api/v1/predictions')
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    // Simulation — middleware guard (no fixture)

    public function test_play_week_without_fixture_returns_422(): void
    {
        $this->postJson('/api/v1/simulation/play-week')->assertUnprocessable();
    }

    public function test_play_all_without_fixture_returns_422(): void
    {
        $this->postJson('/api/v1/simulation/play-all')->assertUnprocessable();
    }

    // Simulation — play-week and play-all

    public function test_play_week_advances_one_week(): void
    {
        $this->postJson('/api/v1/fixtures/generate');

        $this->postJson('/api/v1/simulation/play-week')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_play_all_plays_remaining_matches(): void
    {
        $this->postJson('/api/v1/fixtures/generate');

        $this->postJson('/api/v1/simulation/play-all')
            ->assertOk()
            ->assertJsonCount(12, 'data');
    }

    public function test_play_week_after_all_matches_played_returns_422(): void
    {
        $this->postJson('/api/v1/fixtures/generate');
        $this->postJson('/api/v1/simulation/play-all');

        $this->postJson('/api/v1/simulation/play-week')->assertUnprocessable();
    }

    // Reset

    public function test_reset_clears_played_results(): void
    {
        $this->postJson('/api/v1/fixtures/generate');
        $this->postJson('/api/v1/simulation/play-all');

        $this->postJson('/api/v1/simulation/reset')->assertOk();

        $this->getJson('/api/v1/fixtures')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    // Match update

    public function test_update_nonexistent_match_returns_404(): void
    {
        $this->postJson('/api/v1/fixtures/generate');
        $this->postJson('/api/v1/simulation/play-week');

        $this->patchJson('/api/v1/matches/9999', [
            'home_score' => 2,
            'away_score' => 1,
        ])->assertNotFound();
    }

    public function test_update_played_match_score_returns_success(): void
    {
        $generate = $this->postJson('/api/v1/fixtures/generate');
        $this->postJson('/api/v1/simulation/play-week');

        $matchId = $generate->json('data.0.id');

        $this->patchJson("/api/v1/matches/{$matchId}", [
            'home_score' => 3,
            'away_score' => 0,
        ])->assertOk()
          ->assertJsonPath('data.home_score', 3)
          ->assertJsonPath('data.away_score', 0);
    }

    public function test_update_match_with_negative_score_returns_422(): void
    {
        $generate = $this->postJson('/api/v1/fixtures/generate');
        $this->postJson('/api/v1/simulation/play-week');

        $matchId = $generate->json('data.0.id');

        $this->patchJson("/api/v1/matches/{$matchId}", [
            'home_score' => -1,
            'away_score' => 0,
        ])->assertUnprocessable();
    }
}
