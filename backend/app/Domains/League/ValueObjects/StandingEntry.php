<?php

declare(strict_types=1);

namespace App\Domains\League\ValueObjects;

final readonly class StandingEntry
{
    public function __construct(
        public string $teamName,
        public int $points,
        public int $played,
        public int $won,
        public int $drawn,
        public int $lost,
        public int $goalsFor,
        public int $goalsAgainst,
        public int $goalDifference,
    ) {}
}
