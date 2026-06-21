<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domains\League\ValueObjects\StandingEntry;

final class StandingResource
{
    public static function make(StandingEntry $entry, ?float $prediction = null): array
    {
        return [
            'team_name'               => $entry->teamName,
            'points'                  => $entry->points,
            'played'                  => $entry->played,
            'won'                     => $entry->won,
            'drawn'                   => $entry->drawn,
            'lost'                    => $entry->lost,
            'goals_for'               => $entry->goalsFor,
            'goals_against'           => $entry->goalsAgainst,
            'goal_difference'         => $entry->goalDifference,
            'championship_prediction' => $prediction,
        ];
    }

    /**
     * @param StandingEntry[]          $standings
     * @param array<string,float>|null $predictions
     */
    public static function collection(array $standings, ?array $predictions = null): array
    {
        return array_map(
            fn(StandingEntry $e) => self::make($e, $predictions[$e->teamName] ?? null),
            $standings,
        );
    }

    private function __construct() {}
}
