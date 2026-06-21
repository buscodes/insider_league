<?php

declare(strict_types=1);

namespace App\Domains\League\AggregateRoots;

use App\Core\Constants\Value;
use App\Core\Exceptions\DomainException;
use App\Domains\League\Entities\Team;
use App\Domains\League\ValueObjects\MatchScore;

class FootballMatch
{
    private ?MatchScore $score;
    private bool $isPlayed;

    public function __construct(
        public readonly int $id,
        public readonly Team $homeTeam,
        public readonly Team $awayTeam,
        public readonly int $week,
        ?MatchScore $score = null,
        bool $isPlayed = Value::FALSE,
    ) {
        if ($homeTeam->id === $awayTeam->id) {
            throw new DomainException('A team cannot play against itself.');
        }

        $this->score = $score;
        $this->isPlayed = $isPlayed;
    }

    // Records the match result and marks the match as played
    public function play(MatchScore $score): void
    {
        $this->score = $score;
        $this->isPlayed = Value::TRUE;
    }

    // Overwrites the score of an already-played match; throws if the match has not been played
    public function updateScore(MatchScore $score): void
    {
        if (!$this->isPlayed) {
            throw new DomainException('Cannot edit score of a match that has not been played yet.');
        }

        $this->score = $score;
    }

    // Returns whether the match has been simulated or manually scored
    public function isPlayed(): bool
    {
        return $this->isPlayed;
    }

    // Returns the current score, or null if the match has not been played
    public function getScore(): ?MatchScore
    {
        return $this->score;
    }
}
