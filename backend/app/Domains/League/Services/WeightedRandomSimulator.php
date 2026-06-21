<?php

declare(strict_types=1);

namespace App\Domains\League\Services;

use App\Core\Constants\LeagueConstants;
use App\Domains\League\Contracts\MatchSimulatorInterface;
use App\Domains\League\Entities\Team;
use App\Domains\League\ValueObjects\MatchScore;

final class WeightedRandomSimulator implements MatchSimulatorInterface
{
    public function simulate(Team $homeTeam, Team $awayTeam): MatchScore
    {
        $homeEffective = $homeTeam->power + LeagueConstants::HOME_ADVANTAGE;
        $total         = $homeEffective + $awayTeam->power;

        $homeChance = $homeEffective / $total;
        $awayChance = $awayTeam->power / $total;

        if (
            abs($homeChance - $awayChance) < LeagueConstants::DRAW_THRESHOLD
            && $this->roll() < LeagueConstants::DRAW_CHANCE
        ) {
            $goals = mt_rand(0, 3);
            return new MatchScore($goals, $goals);
        }

        $homeWins    = $this->roll() < $homeChance;
        $winnerGoals = mt_rand(1, 4);
        $loserGoals  = mt_rand(0, min(2, $winnerGoals - 1));

        return $homeWins
            ? new MatchScore($winnerGoals, $loserGoals)
            : new MatchScore($loserGoals, $winnerGoals);
    }

    private function roll(): float
    {
        return mt_rand(0, 10_000) / 10_000;
    }
}
