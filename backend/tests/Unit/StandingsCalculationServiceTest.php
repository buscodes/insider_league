<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\League\AggregateRoots\FootballMatch;
use App\Domains\League\Entities\Team;
use App\Domains\League\Services\StandingsCalculationService;
use App\Domains\League\ValueObjects\MatchScore;
use App\Domains\League\ValueObjects\StandingEntry;
use PHPUnit\Framework\TestCase;

class StandingsCalculationServiceTest extends TestCase
{
    private StandingsCalculationService $service;
    private Team $city;
    private Team $liverpool;
    private Team $arsenal;
    private Team $chelsea;

    /** @var Team[] */
    private array $teams;

    protected function setUp(): void
    {
        $this->service   = new StandingsCalculationService();
        $this->city      = new Team(1, 'Manchester City', 95);
        $this->liverpool = new Team(2, 'Liverpool', 90);
        $this->arsenal   = new Team(3, 'Arsenal', 88);
        $this->chelsea   = new Team(4, 'Chelsea', 82);
        $this->teams     = [$this->city, $this->liverpool, $this->arsenal, $this->chelsea];
    }

    private function playedMatch(int $id, Team $home, Team $away, int $homeGoals, int $awayGoals): FootballMatch
    {
        $match = new FootballMatch($id, $home, $away, 1);
        $match->play(new MatchScore($homeGoals, $awayGoals));
        return $match;
    }

    public function test_win_awards_3_points_and_loss_awards_0(): void
    {
        $matches   = [$this->playedMatch(1, $this->city, $this->liverpool, 3, 1)];
        $standings = $this->service->calculate($this->teams, $matches);

        $cityRow      = $this->findEntry($standings, 'Manchester City');
        $liverpoolRow = $this->findEntry($standings, 'Liverpool');

        $this->assertSame(3, $cityRow->points);
        $this->assertSame(0, $liverpoolRow->points);
        $this->assertSame(1, $cityRow->won);
        $this->assertSame(1, $liverpoolRow->lost);
    }

    public function test_draw_awards_1_point_to_each_team(): void
    {
        $matches   = [$this->playedMatch(1, $this->city, $this->liverpool, 2, 2)];
        $standings = $this->service->calculate($this->teams, $matches);

        $cityRow      = $this->findEntry($standings, 'Manchester City');
        $liverpoolRow = $this->findEntry($standings, 'Liverpool');

        $this->assertSame(1, $cityRow->points);
        $this->assertSame(1, $liverpoolRow->points);
        $this->assertSame(1, $cityRow->drawn);
        $this->assertSame(1, $liverpoolRow->drawn);
    }

    public function test_goal_difference_tiebreak(): void
    {
        $matches = [
            $this->playedMatch(1, $this->city, $this->arsenal, 3, 0),   // City: +3
            $this->playedMatch(2, $this->liverpool, $this->chelsea, 2, 0), // Liverpool: +2
        ];
        $standings = $this->service->calculate($this->teams, $matches);

        $this->assertSame('Manchester City', $standings[0]->teamName);
        $this->assertSame('Liverpool', $standings[1]->teamName);
        $this->assertSame(3, $standings[0]->goalDifference);
        $this->assertSame(2, $standings[1]->goalDifference);
    }

    public function test_goals_for_tiebreak_when_points_and_gd_equal(): void
    {
        $matches = [
            $this->playedMatch(1, $this->city, $this->arsenal, 3, 1),     // City: 3 pts, +2 GD, 3 GF
            $this->playedMatch(2, $this->liverpool, $this->chelsea, 4, 2), // Liverpool: 3 pts, +2 GD, 4 GF
        ];
        $standings = $this->service->calculate($this->teams, $matches);

        $this->assertSame('Liverpool', $standings[0]->teamName);
        $this->assertSame('Manchester City', $standings[1]->teamName);
    }

    public function test_alphabetical_tiebreak_as_last_resort(): void
    {
        // Arsenal and Chelsea both draw their respective matches → same pts, GD, GF
        $matches = [
            $this->playedMatch(1, $this->arsenal, $this->city, 1, 1),
            $this->playedMatch(2, $this->chelsea, $this->liverpool, 1, 1),
        ];
        $standings = $this->service->calculate($this->teams, $matches);

        $arsenalIdx = $this->findIndex($standings, 'Arsenal');
        $chelseaIdx = $this->findIndex($standings, 'Chelsea');

        $this->assertLessThan($chelseaIdx, $arsenalIdx, 'Arsenal should rank above Chelsea alphabetically when tied.');
    }

    public function test_unplayed_matches_do_not_affect_standings(): void
    {
        $unplayed  = new FootballMatch(1, $this->city, $this->liverpool, 1);
        $standings = $this->service->calculate($this->teams, [$unplayed]);

        foreach ($standings as $entry) {
            $this->assertSame(0, $entry->points);
            $this->assertSame(0, $entry->played);
        }
    }

    public function test_zero_matches_returns_all_teams_with_zero_stats(): void
    {
        $standings = $this->service->calculate($this->teams, []);

        $this->assertCount(4, $standings);
        foreach ($standings as $entry) {
            $this->assertSame(0, $entry->points);
            $this->assertSame(0, $entry->played);
            $this->assertSame(0, $entry->goalsFor);
        }
    }

    public function test_standings_update_correctly_after_score_edit(): void
    {
        $match = new FootballMatch(1, $this->city, $this->liverpool, 1);
        $match->play(new MatchScore(1, 0));

        $before = $this->service->calculate($this->teams, [$match]);
        $this->assertSame(3, $this->findEntry($before, 'Manchester City')->points);

        // Edit: result reversed → Liverpool now wins
        $match->updateScore(new MatchScore(0, 2));

        $after = $this->service->calculate($this->teams, [$match]);
        $this->assertSame(0, $this->findEntry($after, 'Manchester City')->points);
        $this->assertSame(3, $this->findEntry($after, 'Liverpool')->points);
    }

    public function test_returns_standing_entry_instances(): void
    {
        $standings = $this->service->calculate($this->teams, []);

        foreach ($standings as $entry) {
            $this->assertInstanceOf(StandingEntry::class, $entry);
        }
    }

    /** @param StandingEntry[] $standings */
    private function findEntry(array $standings, string $name): StandingEntry
    {
        foreach ($standings as $entry) {
            if ($entry->teamName === $name) {
                return $entry;
            }
        }

        $this->fail("Team '{$name}' not found in standings.");
    }

    /** @param StandingEntry[] $standings */
    private function findIndex(array $standings, string $name): int
    {
        foreach ($standings as $i => $entry) {
            if ($entry->teamName === $name) {
                return $i;
            }
        }

        $this->fail("Team '{$name}' not found in standings.");
    }
}
