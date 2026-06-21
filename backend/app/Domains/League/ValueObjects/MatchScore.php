<?php

declare(strict_types=1);

namespace App\Domains\League\ValueObjects;

use InvalidArgumentException;

class MatchScore
{
    public readonly int $homeScore;
    public readonly int $awayScore;

    public function __construct(int $homeScore, int $awayScore)
    {
        if ($homeScore < 0 || $awayScore < 0) {
            throw new InvalidArgumentException('Match scores cannot be negative.');
        }

        $this->homeScore = $homeScore;
        $this->awayScore = $awayScore;
    }

    public function homeWon(): bool
    {
        return $this->homeScore > $this->awayScore;
    }

    public function awayWon(): bool
    {
        return $this->awayScore > $this->homeScore;
    }

    public function isDraw(): bool
    {
        return $this->homeScore === $this->awayScore;
    }

    public function equals(self $other): bool
    {
        return $this->homeScore === $other->homeScore
            && $this->awayScore === $other->awayScore;
    }
}
