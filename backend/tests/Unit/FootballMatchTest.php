<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Exceptions\DomainException;
use App\Domains\League\AggregateRoots\FootballMatch;
use App\Domains\League\Entities\Team;
use App\Domains\League\ValueObjects\MatchScore;
use PHPUnit\Framework\TestCase;

class FootballMatchTest extends TestCase
{
    private Team $home;
    private Team $away;

    protected function setUp(): void
    {
        $this->home = new Team(1, 'Manchester City', 95);
        $this->away = new Team(2, 'Liverpool', 90);
    }

    public function test_throws_when_home_and_away_are_the_same_team(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('A team cannot play against itself.');

        new FootballMatch(1, $this->home, $this->home, 1);
    }

    public function test_constructs_successfully_with_different_teams(): void
    {
        $match = new FootballMatch(1, $this->home, $this->away, 1);

        $this->assertFalse($match->isPlayed());
        $this->assertNull($match->getScore());
    }

    public function test_play_marks_match_as_played_with_score(): void
    {
        $match = new FootballMatch(1, $this->home, $this->away, 1);
        $match->play(new MatchScore(2, 1));

        $this->assertTrue($match->isPlayed());
        $this->assertSame(2, $match->getScore()->homeScore);
        $this->assertSame(1, $match->getScore()->awayScore);
    }

    public function test_update_score_throws_when_match_not_played(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot edit score of a match that has not been played yet.');

        $match = new FootballMatch(1, $this->home, $this->away, 1);
        $match->updateScore(new MatchScore(3, 0));
    }

    public function test_update_score_succeeds_on_played_match(): void
    {
        $match = new FootballMatch(1, $this->home, $this->away, 1);
        $match->play(new MatchScore(1, 0));
        $match->updateScore(new MatchScore(0, 2));

        $this->assertSame(0, $match->getScore()->homeScore);
        $this->assertSame(2, $match->getScore()->awayScore);
    }
}
