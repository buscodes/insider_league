<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\League\Contracts\MatchSimulatorInterface;
use App\Domains\League\Entities\Team;
use App\Domains\League\Services\WeightedRandomSimulator;
use PHPUnit\Framework\TestCase;

class WeightedRandomSimulatorTest extends TestCase
{
    private WeightedRandomSimulator $simulator;
    private Team $strong;
    private Team $weak;

    protected function setUp(): void
    {
        $this->simulator = new WeightedRandomSimulator();
        $this->strong    = new Team(1, 'Strong', 95);
        $this->weak      = new Team(2, 'Weak', 50);
    }

    public function test_implements_match_simulator_interface(): void
    {
        $this->assertInstanceOf(MatchSimulatorInterface::class, $this->simulator);
    }

    public function test_stronger_team_wins_more_often_in_1000_simulations(): void
    {
        $strongWins = 0;

        for ($i = 0; $i < 1000; $i++) {
            $score = $this->simulator->simulate($this->strong, $this->weak);
            if ($score->homeWon()) {
                $strongWins++;
            }
        }

        $this->assertGreaterThan(500, $strongWins, 'Strong team (power 95) should win more than half of 1000 simulations as home team.');
    }

    public function test_scores_are_never_negative(): void
    {
        $a = new Team(1, 'A', 85);
        $b = new Team(2, 'B', 80);

        for ($i = 0; $i < 200; $i++) {
            $score = $this->simulator->simulate($a, $b);
            $this->assertGreaterThanOrEqual(0, $score->homeScore);
            $this->assertGreaterThanOrEqual(0, $score->awayScore);
        }
    }

    public function test_home_advantage_gives_edge_even_for_equal_teams(): void
    {
        $team1 = new Team(1, 'Equal1', 80);
        $team2 = new Team(2, 'Equal2', 80);

        $homeWins = 0;
        $awayWins = 0;

        for ($i = 0; $i < 1000; $i++) {
            $score = $this->simulator->simulate($team1, $team2);
            if ($score->homeWon()) {
                $homeWins++;
            } elseif ($score->awayWon()) {
                $awayWins++;
            }
        }

        $this->assertGreaterThan($awayWins, $homeWins, 'Home team should win more than away team when power is equal.');
    }

    public function test_draw_produces_equal_scores(): void
    {
        $team1 = new Team(1, 'Team1', 80);
        $team2 = new Team(2, 'Team2', 80);

        for ($i = 0; $i < 200; $i++) {
            $score = $this->simulator->simulate($team1, $team2);
            if ($score->isDraw()) {
                $this->assertSame($score->homeScore, $score->awayScore);
            }
        }
    }
}
