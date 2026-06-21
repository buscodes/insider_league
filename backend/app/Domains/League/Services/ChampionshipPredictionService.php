<?php

declare(strict_types=1);

namespace App\Domains\League\Services;

use App\Core\Constants\LeagueConstants;
use App\Core\Constants\Value;
use App\Domains\League\AggregateRoots\FootballMatch;
use App\Domains\League\Contracts\MatchSimulatorInterface;
use App\Domains\League\Entities\Team;
use App\Domains\League\ValueObjects\StandingEntry;

final class ChampionshipPredictionService
{
    public function __construct(
        private readonly MatchSimulatorInterface $simulator,
        private readonly StandingsCalculationService $standingsService,
    ) {}

    /**
     * @param  Team[]          $teams
     * @param  FootballMatch[] $matches
     * @return array<string, float>|null
     */
    // Returns championship percentages per team from week 4 onward; null before that
    public function predict(array $teams, array $matches, int $currentWeek): ?array
    {
        if ($currentWeek < LeagueConstants::MIN_PREDICTION_WEEK) {
            return null;
        }

        $standings = $this->standingsService->calculate($teams, $matches);
        $remaining = array_values(array_filter($matches, fn(FootballMatch $m) => !$m->isPlayed()));

        if ($this->leaderIsGuaranteed($standings, $remaining)) {
            return $this->buildCertainResult($standings);
        }

        return $this->runMonteCarlo($standings, $remaining);
    }

    /**
     * @param StandingEntry[] $standings
     * @param FootballMatch[] $remaining
     */
    // True when the leader's gap exceeds the maximum points the second-place team can still earn
    private function leaderIsGuaranteed(array $standings, array $remaining): bool
    {
        if (count($standings) < 2) {
            return true;
        }

        $leader = $standings[0];
        $second = $standings[1];

        $secondRemaining = count(array_filter(
            $remaining,
            fn(FootballMatch $m) =>
                $m->homeTeam->name === $second->teamName ||
                $m->awayTeam->name === $second->teamName,
        ));

        return $leader->points - $second->points > $secondRemaining * 3;
    }

    /** @param StandingEntry[] $standings */
    // Returns 100% for the leader and 0% for all others when the title is mathematically decided
    private function buildCertainResult(array $standings): array
    {
        $result = [];
        foreach ($standings as $i => $entry) {
            $result[$entry->teamName] = $i === Value::ZERO ? 100.0 : 0.0;
        }
        return $result;
    }

    /**
     * @param  StandingEntry[] $standings
     * @param  FootballMatch[] $remaining
     * @return array<string, float>
     */
    // Simulates 1000 seasons from current state and returns win-percentage per team
    private function runMonteCarlo(array $standings, array $remaining): array
    {
        $wins = [];
        $base = [];
        foreach ($standings as $entry) {
            $wins[$entry->teamName] = Value::ZERO;
            $base[$entry->teamName] = [
                'points'        => $entry->points,
                'goals_for'     => $entry->goalsFor,
                'goals_against' => $entry->goalsAgainst,
            ];
        }

        for ($i = 0; $i < LeagueConstants::MONTE_CARLO_ITERATIONS; $i++) {
            $sim = $base;

            foreach ($remaining as $match) {
                $score = $this->simulator->simulate($match->homeTeam, $match->awayTeam);
                $home  = $match->homeTeam->name;
                $away  = $match->awayTeam->name;

                $sim[$home]['goals_for']     += $score->homeScore;
                $sim[$home]['goals_against'] += $score->awayScore;
                $sim[$away]['goals_for']     += $score->awayScore;
                $sim[$away]['goals_against'] += $score->homeScore;

                if ($score->homeWon()) {
                    $sim[$home]['points'] += 3;
                } elseif ($score->awayWon()) {
                    $sim[$away]['points'] += 3;
                } else {
                    $sim[$home]['points']++;
                    $sim[$away]['points']++;
                }
            }

            uksort($sim, static function (string $a, string $b) use ($sim): int {
                $aGD = $sim[$a]['goals_for'] - $sim[$a]['goals_against'];
                $bGD = $sim[$b]['goals_for'] - $sim[$b]['goals_against'];

                return [$sim[$b]['points'], $bGD, $sim[$b]['goals_for'], $a]
                    <=> [$sim[$a]['points'], $aGD, $sim[$a]['goals_for'], $b];
            });

            $wins[array_key_first($sim)]++;
        }

        $predictions = [];
        foreach ($standings as $entry) {
            $predictions[$entry->teamName] = round(
                $wins[$entry->teamName] / LeagueConstants::MONTE_CARLO_ITERATIONS * 100,
                1,
            );
        }

        return $predictions;
    }
}
