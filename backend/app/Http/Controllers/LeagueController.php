<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\League\AggregateRoots\FootballMatch;
use App\Domains\League\Contracts\MatchRepositoryInterface;
use App\Domains\League\Contracts\TeamRepositoryInterface;
use App\Domains\League\Services\ChampionshipPredictionService;
use App\Domains\League\Services\StandingsCalculationService;
use App\Http\Resources\BaseApiResource;
use App\Http\Resources\StandingResource;
use Illuminate\Http\JsonResponse;

class LeagueController extends Controller
{
    public function __construct(
        private readonly TeamRepositoryInterface $teamRepository,
        private readonly MatchRepositoryInterface $matchRepository,
        private readonly StandingsCalculationService $standingsService,
        private readonly ChampionshipPredictionService $predictionService,
    ) {}

    public function table(): JsonResponse
    {
        $teams     = $this->teamRepository->all();
        $matches   = $this->matchRepository->all();
        $standings = $this->standingsService->calculate($teams, $matches);
        $week      = $this->completedWeek($matches);
        $preds     = $this->predictionService->predict($teams, $matches, $week);

        return BaseApiResource::success(
            data:    StandingResource::collection($standings, $preds),
            message: 'League table calculated successfully.',
        );
    }

    public function predictions(): JsonResponse
    {
        $teams   = $this->teamRepository->all();
        $matches = $this->matchRepository->all();
        $week    = $this->completedWeek($matches);
        $preds   = $this->predictionService->predict($teams, $matches, $week);

        if ($preds === null) {
            return BaseApiResource::success(
                data:    null,
                message: 'Championship predictions are available from week 4 onwards.',
            );
        }

        $data = array_map(
            fn(string $name, float $pct) => ['team_name' => $name, 'championship_prediction' => $pct],
            array_keys($preds),
            array_values($preds),
        );

        return BaseApiResource::success(
            data:    $data,
            message: 'Championship predictions calculated.',
        );
    }

    /** @param FootballMatch[] $matches */
    private function completedWeek(array $matches): int
    {
        $played = count(array_filter($matches, fn(FootballMatch $m) => $m->isPlayed()));

        return intdiv($played, 2);
    }
}
