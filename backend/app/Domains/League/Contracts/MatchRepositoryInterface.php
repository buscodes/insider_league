<?php

declare(strict_types=1);

namespace App\Domains\League\Contracts;

use App\Domains\League\AggregateRoots\FootballMatch;
use App\Domains\League\ValueObjects\FixtureEntry;

interface MatchRepositoryInterface
{
    /** @return FootballMatch[] */
    public function all(): array;

    /** @return FootballMatch[] */
    public function getByWeek(int $week): array;

    /**
     * Persists a list of fixtures or updates a list of played matches.
     *
     * @param FixtureEntry[]|FootballMatch[] $matches
     */
    public function saveAll(array $matches): void;

    /** Persists a single match result (score + is_played flag). */
    public function save(FootballMatch $match): void;

    /**
     * Returns the week number of the first unplayed match.
     * Returns 0 when no matches exist or all matches have been played.
     */
    public function getCurrentWeek(): int;

    /** Deletes all match records, resetting the league season. */
    public function resetAll(): void;
}
