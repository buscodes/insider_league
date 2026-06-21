<?php

declare(strict_types=1);

namespace App\Domains\League\Services;

use App\Core\Constants\LeagueConstants;
use App\Domains\League\Entities\Team;
use App\Domains\League\ValueObjects\FixtureEntry;
use InvalidArgumentException;

final class FixtureGeneratorService
{
    // Home/away index pairs per week for a 4-team round-robin (0-based team indices)
    private const array SCHEDULE = [
        1 => [[0, 1], [2, 3]],
        2 => [[0, 2], [1, 3]],
        3 => [[0, 3], [1, 2]],
        4 => [[1, 0], [3, 2]],
        5 => [[2, 0], [3, 1]],
        6 => [[3, 0], [2, 1]],
    ];

    /**
     * @param  Team[]         $teams
     * @return FixtureEntry[]
     */
    // Generates the 12-match round-robin schedule across 6 weeks
    public function generate(array $teams): array
    {
        if (count($teams) !== LeagueConstants::TOTAL_TEAMS) {
            throw new InvalidArgumentException('Exactly ' . LeagueConstants::TOTAL_TEAMS . ' teams are required to generate fixtures.');
        }

        $teams    = array_values($teams);
        $fixtures = [];

        foreach (self::SCHEDULE as $week => $pairs) {
            foreach ($pairs as [$homeIdx, $awayIdx]) {
                $fixtures[] = new FixtureEntry(
                    homeTeam: $teams[$homeIdx],
                    awayTeam: $teams[$awayIdx],
                    week: $week,
                );
            }
        }

        return $fixtures;
    }
}
