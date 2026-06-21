<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domains\League\AggregateRoots\FootballMatch;
use App\Domains\League\Contracts\MatchRepositoryInterface;
use App\Domains\League\Entities\Team;
use App\Domains\League\ValueObjects\FixtureEntry;
use App\Domains\League\ValueObjects\MatchScore;
use App\Infrastructure\Models\FootballMatchModel;

final class EloquentMatchRepository implements MatchRepositoryInterface
{
    public function all(): array
    {
        return FootballMatchModel::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get()
            ->map(fn(FootballMatchModel $m) => $this->toDomain($m))
            ->all();
    }

    public function getByWeek(int $week): array
    {
        return FootballMatchModel::with(['homeTeam', 'awayTeam'])
            ->where('week', $week)
            ->orderBy('id')
            ->get()
            ->map(fn(FootballMatchModel $m) => $this->toDomain($m))
            ->all();
    }

    public function saveAll(array $matches): void
    {
        foreach ($matches as $match) {
            if ($match instanceof FixtureEntry) {
                FootballMatchModel::create([
                    'home_team_id' => $match->homeTeam->id,
                    'away_team_id' => $match->awayTeam->id,
                    'week'         => $match->week,
                    'is_played'    => false,
                ]);
            } elseif ($match instanceof FootballMatch) {
                FootballMatchModel::where('id', $match->id)->update([
                    'home_score' => $match->getScore()?->homeScore,
                    'away_score' => $match->getScore()?->awayScore,
                    'is_played'  => $match->isPlayed(),
                ]);
            }
        }
    }

    public function save(FootballMatch $match): void
    {
        FootballMatchModel::where('id', $match->id)->update([
            'home_score' => $match->getScore()?->homeScore,
            'away_score' => $match->getScore()?->awayScore,
            'is_played'  => $match->isPlayed(),
        ]);
    }

    public function getCurrentWeek(): int
    {
        $match = FootballMatchModel::where('is_played', false)
            ->orderBy('week')
            ->first();

        return $match?->week ?? 0;
    }

    public function resetAll(): void
    {
        FootballMatchModel::truncate();
    }

    private function toDomain(FootballMatchModel $model): FootballMatch
    {
        $homeTeam = new Team($model->homeTeam->id, $model->homeTeam->name, $model->homeTeam->power);
        $awayTeam = new Team($model->awayTeam->id, $model->awayTeam->name, $model->awayTeam->power);

        $score = $model->is_played
            ? new MatchScore($model->home_score, $model->away_score)
            : null;

        return new FootballMatch(
            id:       $model->id,
            homeTeam: $homeTeam,
            awayTeam: $awayTeam,
            week:     $model->week,
            score:    $score,
            isPlayed: $model->is_played,
        );
    }
}
