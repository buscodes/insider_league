<?php

declare(strict_types=1);

namespace App\Domains\League\ValueObjects;

use App\Domains\League\Entities\Team;

final readonly class FixtureEntry
{
    public function __construct(
        public Team $homeTeam,
        public Team $awayTeam,
        public int $week,
    ) {}
}
