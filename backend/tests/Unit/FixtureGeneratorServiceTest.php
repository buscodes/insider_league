<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\League\Entities\Team;
use App\Domains\League\Services\FixtureGeneratorService;
use App\Domains\League\ValueObjects\FixtureEntry;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FixtureGeneratorServiceTest extends TestCase
{
    private FixtureGeneratorService $service;

    /** @var Team[] */
    private array $teams;

    protected function setUp(): void
    {
        $this->service = new FixtureGeneratorService();
        $this->teams   = [
            new Team(1, 'Manchester City', 95),
            new Team(2, 'Liverpool', 90),
            new Team(3, 'Arsenal', 88),
            new Team(4, 'Chelsea', 82),
        ];
    }

    public function test_generates_exactly_12_matches(): void
    {
        $fixtures = $this->service->generate($this->teams);

        $this->assertCount(12, $fixtures);
    }

    public function test_each_team_plays_exactly_6_matches(): void
    {
        $fixtures   = $this->service->generate($this->teams);
        $matchCount = [];

        foreach ($this->teams as $team) {
            $matchCount[$team->name] = 0;
        }

        foreach ($fixtures as $entry) {
            $matchCount[$entry->homeTeam->name]++;
            $matchCount[$entry->awayTeam->name]++;
        }

        foreach ($matchCount as $teamName => $count) {
            $this->assertSame(6, $count, "{$teamName} should play exactly 6 matches.");
        }
    }

    public function test_each_team_plays_3_home_and_3_away_matches(): void
    {
        $fixtures  = $this->service->generate($this->teams);
        $homeCount = [];
        $awayCount = [];

        foreach ($this->teams as $team) {
            $homeCount[$team->name] = 0;
            $awayCount[$team->name] = 0;
        }

        foreach ($fixtures as $entry) {
            $homeCount[$entry->homeTeam->name]++;
            $awayCount[$entry->awayTeam->name]++;
        }

        foreach ($this->teams as $team) {
            $this->assertSame(3, $homeCount[$team->name], "{$team->name} should have 3 home matches.");
            $this->assertSame(3, $awayCount[$team->name], "{$team->name} should have 3 away matches.");
        }
    }

    public function test_week_numbers_are_between_1_and_6(): void
    {
        $fixtures = $this->service->generate($this->teams);

        foreach ($fixtures as $entry) {
            $this->assertGreaterThanOrEqual(1, $entry->week);
            $this->assertLessThanOrEqual(6, $entry->week);
        }
    }

    public function test_each_week_has_exactly_2_matches(): void
    {
        $fixtures      = $this->service->generate($this->teams);
        $matchesPerWeek = [];

        foreach ($fixtures as $entry) {
            $matchesPerWeek[$entry->week] = ($matchesPerWeek[$entry->week] ?? 0) + 1;
        }

        for ($week = 1; $week <= 6; $week++) {
            $this->assertSame(2, $matchesPerWeek[$week], "Week {$week} should have exactly 2 matches.");
        }
    }

    public function test_each_pair_of_teams_plays_home_and_away(): void
    {
        $fixtures = $this->service->generate($this->teams);
        $pairs    = [];

        foreach ($fixtures as $entry) {
            $pairs[] = [$entry->homeTeam->name, $entry->awayTeam->name];
        }

        foreach ($this->teams as $i => $teamA) {
            foreach ($this->teams as $j => $teamB) {
                if ($i >= $j) {
                    continue;
                }

                $homeAway = in_array([$teamA->name, $teamB->name], $pairs, true);
                $awayHome = in_array([$teamB->name, $teamA->name], $pairs, true);

                $this->assertTrue($homeAway, "{$teamA->name} should host {$teamB->name}.");
                $this->assertTrue($awayHome, "{$teamB->name} should host {$teamA->name}.");
            }
        }
    }

    public function test_throws_when_team_count_is_not_four(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->generate([new Team(1, 'Only', 80)]);
    }

    public function test_returns_fixture_entry_instances(): void
    {
        $fixtures = $this->service->generate($this->teams);

        foreach ($fixtures as $fixture) {
            $this->assertInstanceOf(FixtureEntry::class, $fixture);
        }
    }
}
