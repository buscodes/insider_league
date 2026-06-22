<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\League\ValueObjects\MatchScore;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MatchScoreTest extends TestCase
{
    public function test_constructs_with_valid_scores(): void
    {
        $score = new MatchScore(2, 1);

        $this->assertSame(2, $score->homeScore);
        $this->assertSame(1, $score->awayScore);
    }

    public function test_zero_zero_is_valid(): void
    {
        $score = new MatchScore(0, 0);

        $this->assertSame(0, $score->homeScore);
        $this->assertSame(0, $score->awayScore);
    }

    public function test_throws_on_negative_home_score(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MatchScore(-1, 0);
    }

    public function test_throws_on_negative_away_score(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MatchScore(0, -1);
    }

    public function test_home_won_returns_true_when_home_leads(): void
    {
        $score = new MatchScore(3, 1);

        $this->assertTrue($score->homeWon());
        $this->assertFalse($score->awayWon());
        $this->assertFalse($score->isDraw());
    }

    public function test_away_won_returns_true_when_away_leads(): void
    {
        $score = new MatchScore(0, 2);

        $this->assertTrue($score->awayWon());
        $this->assertFalse($score->homeWon());
        $this->assertFalse($score->isDraw());
    }

    public function test_is_draw_returns_true_for_equal_scores(): void
    {
        $score = new MatchScore(1, 1);

        $this->assertTrue($score->isDraw());
        $this->assertFalse($score->homeWon());
        $this->assertFalse($score->awayWon());
    }

    public function test_zero_zero_is_a_draw(): void
    {
        $this->assertTrue((new MatchScore(0, 0))->isDraw());
    }

    public function test_equals_returns_true_for_identical_scores(): void
    {
        $this->assertTrue((new MatchScore(2, 1))->equals(new MatchScore(2, 1)));
    }

    public function test_equals_returns_false_when_home_score_differs(): void
    {
        $this->assertFalse((new MatchScore(2, 1))->equals(new MatchScore(3, 1)));
    }

    public function test_equals_returns_false_when_away_score_differs(): void
    {
        $this->assertFalse((new MatchScore(2, 1))->equals(new MatchScore(2, 0)));
    }

    public function test_equals_returns_false_for_reversed_scores(): void
    {
        $this->assertFalse((new MatchScore(2, 1))->equals(new MatchScore(1, 2)));
    }
}
