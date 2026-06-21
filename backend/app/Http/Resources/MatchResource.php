<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domains\League\AggregateRoots\FootballMatch;

final class MatchResource
{
    public static function make(FootballMatch $match): array
    {
        return [
            'id'         => $match->id,
            'week'       => $match->week,
            'home_team'  => TeamResource::make($match->homeTeam),
            'away_team'  => TeamResource::make($match->awayTeam),
            'home_score' => $match->getScore()?->homeScore,
            'away_score' => $match->getScore()?->awayScore,
            'is_played'  => $match->isPlayed(),
        ];
    }

    /** @param FootballMatch[] $matches */
    public static function collection(array $matches): array
    {
        return array_map(self::make(...), $matches);
    }

    private function __construct() {}
}
