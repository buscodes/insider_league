<?php

declare(strict_types=1);

namespace App\Domains\League\Contracts;

use App\Domains\League\Entities\Team;
use App\Domains\League\ValueObjects\MatchScore;

interface MatchSimulatorInterface
{
    public function simulate(Team $homeTeam, Team $awayTeam): MatchScore;
}
