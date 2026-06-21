<?php

declare(strict_types=1);

namespace App\Domains\League\Services;

use App\Domains\League\AggregateRoots\FootballMatch;
use App\Domains\League\Entities\Team;
use App\Domains\League\ValueObjects\StandingEntry;

final class StandingsCalculationService
{
    /**
     * @param  Team[]          $teams
     * @param  FootballMatch[] $matches
     * @return StandingEntry[]
     */
    // Builds the league table from played matches and sorts by points, goal difference, goals scored
    public function calculate(array $teams, array $matches): array
    {
        $table = [];
        foreach ($teams as $team) {
            $table[$team->name] = [
                'points'        => 0,
                'played'        => 0,
                'won'           => 0,
                'drawn'         => 0,
                'lost'          => 0,
                'goals_for'     => 0,
                'goals_against' => 0,
            ];
        }

        foreach ($matches as $match) {
            if (!$match->isPlayed()) {
                continue;
            }

            $score = $match->getScore();
            $home  = $match->homeTeam->name;
            $away  = $match->awayTeam->name;

            $table[$home]['played']++;
            $table[$away]['played']++;
            $table[$home]['goals_for']     += $score->homeScore;
            $table[$home]['goals_against'] += $score->awayScore;
            $table[$away]['goals_for']     += $score->awayScore;
            $table[$away]['goals_against'] += $score->homeScore;

            if ($score->homeWon()) {
                $table[$home]['won']++;
                $table[$home]['points'] += 3;
                $table[$away]['lost']++;
            } elseif ($score->awayWon()) {
                $table[$away]['won']++;
                $table[$away]['points'] += 3;
                $table[$home]['lost']++;
            } else {
                $table[$home]['drawn']++;
                $table[$home]['points']++;
                $table[$away]['drawn']++;
                $table[$away]['points']++;
            }
        }

        $entries = [];
        foreach ($table as $teamName => $s) {
            $entries[] = new StandingEntry(
                teamName:       $teamName,
                points:         $s['points'],
                played:         $s['played'],
                won:            $s['won'],
                drawn:          $s['drawn'],
                lost:           $s['lost'],
                goalsFor:       $s['goals_for'],
                goalsAgainst:   $s['goals_against'],
                goalDifference: $s['goals_for'] - $s['goals_against'],
            );
        }

        usort($entries, static fn(StandingEntry $a, StandingEntry $b): int =>
            [$b->points, $b->goalDifference, $b->goalsFor, $a->teamName]
            <=>
            [$a->points, $a->goalDifference, $a->goalsFor, $b->teamName]
        );

        return $entries;
    }
}
