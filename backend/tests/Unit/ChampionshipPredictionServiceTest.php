<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\League\AggregateRoots\FootballMatch;
use App\Domains\League\Contracts\MatchSimulatorInterface;
use App\Domains\League\Entities\Team;
use App\Domains\League\Services\ChampionshipPredictionService;
use App\Domains\League\Services\StandingsCalculationService;
use App\Domains\League\Services\WeightedRandomSimulator;
use App\Domains\League\ValueObjects\MatchScore;
use PHPUnit\Framework\TestCase;

class ChampionshipPredictionServiceTest extends TestCase
{
    private Team $city;
    private Team $liverpool;
    private Team $arsenal;
    private Team $chelsea;

    /** @var Team[] */
    private array $teams;

    protected function setUp(): void
    {
        $this->city      = new Team(1, 'Manchester City', 95);
        $this->liverpool = new Team(2, 'Liverpool', 90);
        $this->arsenal   = new Team(3, 'Arsenal', 88);
        $this->chelsea   = new Team(4, 'Chelsea', 82);
        $this->teams     = [$this->city, $this->liverpool, $this->arsenal, $this->chelsea];
    }

    private function makeService(?MatchSimulatorInterface $simulator = null): ChampionshipPredictionService
    {
        return new ChampionshipPredictionService(
            $simulator ?? new WeightedRandomSimulator(),
            new StandingsCalculationService(),
        );
    }

    private function played(int $id, Team $home, Team $away, int $hg, int $ag, int $week = 1): FootballMatch
    {
        $match = new FootballMatch($id, $home, $away, $week);
        $match->play(new MatchScore($hg, $ag));
        return $match;
    }

    private function unplayed(int $id, Team $home, Team $away, int $week): FootballMatch
    {
        return new FootballMatch($id, $home, $away, $week);
    }

    public function test_returns_null_before_week_4(): void
    {
        $service = $this->makeService();
        $result  = $service->predict($this->teams, [], 3);

        $this->assertNull($result);
    }

    public function test_returns_null_at_week_0(): void
    {
        $service = $this->makeService();

        $this->assertNull($service->predict($this->teams, [], 0));
    }

    /**
     * Week 5 complete, 1 match remaining for Liverpool (max_catchable = 3).
     * City leads by 5 pts → 5 > 3 → dynamic early exit → City 100%.
     */
    public function test_dynamic_early_exit_at_week_5_city_leads_by_4(): void
    {
        // Week 6 remaining: Chelsea vs City and Arsenal vs Liverpool.
        // Liverpool (second place) has 1 match left → max_catchable = 1 * 3 = 3.
        // City leads by 5 pts → early exit triggered.
        $matches = [
            $this->played(1,  $this->city,      $this->liverpool, 2, 0, 1),
            $this->played(2,  $this->arsenal,   $this->chelsea,   1, 0, 1),
            $this->played(3,  $this->city,      $this->arsenal,   2, 0, 2),
            $this->played(4,  $this->liverpool, $this->chelsea,   2, 0, 2),
            $this->played(5,  $this->city,      $this->chelsea,   1, 0, 3),
            $this->played(6,  $this->liverpool, $this->arsenal,   1, 1, 3),
            $this->played(7,  $this->liverpool, $this->city,      0, 2, 4),
            $this->played(8,  $this->chelsea,   $this->arsenal,   0, 1, 4),
            $this->played(9,  $this->arsenal,   $this->city,      0, 1, 5),
            $this->played(10, $this->chelsea,   $this->liverpool, 0, 2, 5),
            $this->unplayed(11, $this->chelsea,  $this->city,      6),
            $this->unplayed(12, $this->arsenal,  $this->liverpool, 6),
        ];

        // City: 5 wins = 15 pts | Liverpool: 3W 1D 1L = 10 pts | diff = 5 > 3 → early exit
        $service    = $this->makeService();
        $prediction = $service->predict($this->teams, $matches, 5);

        $this->assertNotNull($prediction);
        $this->assertSame(100.0, $prediction['Manchester City']);
        $this->assertSame(0.0, $prediction['Liverpool']);
        $this->assertSame(0.0, $prediction['Arsenal']);
        $this->assertSame(0.0, $prediction['Chelsea']);
    }

    /**
     * Week 4 complete, Liverpool has 2 matches remaining (max_catchable = 6).
     * City leads by 3 pts → 3 ≯ 6 → Monte Carlo runs → sum ~100%.
     */
    public function test_monte_carlo_runs_when_lead_is_not_decisive_at_week_4(): void
    {
        // Weeks 5-6 remaining: each team has 2 matches left.
        $matches = [
            $this->played(1, $this->city,      $this->liverpool, 2, 0, 1), // City 3, Liv 0
            $this->played(2, $this->arsenal,   $this->chelsea,   1, 1, 1),
            $this->played(3, $this->city,      $this->arsenal,   1, 0, 2), // City 6
            $this->played(4, $this->liverpool, $this->chelsea,   2, 0, 2), // Liv 3
            $this->played(5, $this->city,      $this->chelsea,   1, 0, 3), // City 9
            $this->played(6, $this->liverpool, $this->arsenal,   2, 0, 3), // Liv 6
            $this->played(7, $this->liverpool, $this->city,      0, 0, 4), // City 10, Liv 7 (draw)
            $this->played(8, $this->chelsea,   $this->arsenal,   1, 0, 4),
            // Weeks 5-6 — unplayed
            $this->unplayed(9,  $this->arsenal,  $this->city,      5),
            $this->unplayed(10, $this->chelsea,  $this->liverpool, 5),
            $this->unplayed(11, $this->chelsea,  $this->city,      6),
            $this->unplayed(12, $this->arsenal,  $this->liverpool, 6),
        ];

        // City: 10 pts, Liverpool: 7 pts — diff 3 does not exceed max_catchable 6 → Monte Carlo runs.
        $service    = $this->makeService();
        $prediction = $service->predict($this->teams, $matches, 4);

        $this->assertNotNull($prediction);

        // City should NOT be 100% — Monte Carlo must have run
        $this->assertLessThan(100.0, $prediction['Manchester City']);
        $this->assertGreaterThan(0.0, $prediction['Liverpool']);
    }

    public function test_sum_of_all_predictions_is_approximately_100(): void
    {
        $matches = [
            $this->played(1, $this->city,      $this->liverpool, 2, 0, 1),
            $this->played(2, $this->arsenal,   $this->chelsea,   1, 1, 1),
            $this->played(3, $this->city,      $this->arsenal,   1, 0, 2),
            $this->played(4, $this->liverpool, $this->chelsea,   2, 0, 2),
            $this->played(5, $this->city,      $this->chelsea,   1, 0, 3),
            $this->played(6, $this->liverpool, $this->arsenal,   2, 0, 3),
            $this->played(7, $this->liverpool, $this->city,      0, 0, 4),
            $this->played(8, $this->chelsea,   $this->arsenal,   1, 0, 4),
            $this->unplayed(9,  $this->arsenal,  $this->city,      5),
            $this->unplayed(10, $this->chelsea,  $this->liverpool, 5),
            $this->unplayed(11, $this->chelsea,  $this->city,      6),
            $this->unplayed(12, $this->arsenal,  $this->liverpool, 6),
        ];

        $service    = $this->makeService();
        $prediction = $service->predict($this->teams, $matches, 4);

        $this->assertNotNull($prediction);

        $sum = array_sum($prediction);
        $this->assertEqualsWithDelta(100.0, $sum, 0.1, 'Sum of all championship predictions must be ~100%.');
    }

    public function test_all_prediction_values_are_non_negative(): void
    {
        $matches = [
            $this->played(1, $this->city,      $this->liverpool, 2, 0, 1),
            $this->played(2, $this->arsenal,   $this->chelsea,   1, 1, 1),
            $this->played(3, $this->city,      $this->arsenal,   1, 0, 2),
            $this->played(4, $this->liverpool, $this->chelsea,   2, 0, 2),
            $this->played(5, $this->city,      $this->chelsea,   1, 0, 3),
            $this->played(6, $this->liverpool, $this->arsenal,   2, 0, 3),
            $this->played(7, $this->liverpool, $this->city,      0, 0, 4),
            $this->played(8, $this->chelsea,   $this->arsenal,   1, 0, 4),
            $this->unplayed(9,  $this->arsenal,  $this->city,      5),
            $this->unplayed(10, $this->chelsea,  $this->liverpool, 5),
            $this->unplayed(11, $this->chelsea,  $this->city,      6),
            $this->unplayed(12, $this->arsenal,  $this->liverpool, 6),
        ];

        $service    = $this->makeService();
        $prediction = $service->predict($this->teams, $matches, 4);

        foreach ($prediction as $teamName => $pct) {
            $this->assertGreaterThanOrEqual(0.0, $pct, "{$teamName} prediction cannot be negative.");
        }
    }

    public function test_returns_100_for_leader_when_all_matches_played(): void
    {
        // All 12 matches played, City has most points
        $matches = [
            $this->played(1,  $this->city,      $this->liverpool, 3, 0, 1),
            $this->played(2,  $this->arsenal,   $this->chelsea,   0, 0, 1),
            $this->played(3,  $this->city,      $this->arsenal,   2, 0, 2),
            $this->played(4,  $this->liverpool, $this->chelsea,   1, 0, 2),
            $this->played(5,  $this->city,      $this->chelsea,   2, 0, 3),
            $this->played(6,  $this->liverpool, $this->arsenal,   0, 0, 3),
            $this->played(7,  $this->liverpool, $this->city,      0, 2, 4),
            $this->played(8,  $this->chelsea,   $this->arsenal,   0, 1, 4),
            $this->played(9,  $this->arsenal,   $this->city,      0, 2, 5),
            $this->played(10, $this->chelsea,   $this->liverpool, 0, 1, 5),
            $this->played(11, $this->chelsea,   $this->city,      0, 3, 6),
            $this->played(12, $this->arsenal,   $this->liverpool, 0, 1, 6),
        ];

        // City wins all 6 → 18 pts, no remaining matches → early exit → 100%
        $service    = $this->makeService();
        $prediction = $service->predict($this->teams, $matches, 6);

        $this->assertNotNull($prediction);
        $this->assertSame(100.0, $prediction['Manchester City']);
    }
}
