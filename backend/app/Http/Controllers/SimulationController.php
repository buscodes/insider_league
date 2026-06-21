<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\League\AggregateRoots\FootballMatch;
use App\Domains\League\Contracts\MatchRepositoryInterface;
use App\Domains\League\Contracts\MatchSimulatorInterface;
use App\Core\Constants\Value;
use App\Http\Resources\BaseApiResource;
use App\Http\Resources\MatchResource;
use Illuminate\Http\JsonResponse;

class SimulationController extends Controller
{
    public function __construct(
        private readonly MatchRepositoryInterface $matchRepository,
        private readonly MatchSimulatorInterface $simulator,
    ) {}

    public function playWeek(): JsonResponse
    {
        $week = $this->matchRepository->getCurrentWeek();

        if ($week === Value::ZERO) {
            return BaseApiResource::error(
                message: 'All matches have already been played.',
                status:  422,
            );
        }

        $weekMatches = $this->matchRepository->getByWeek($week);

        foreach ($weekMatches as $match) {
            $match->play($this->simulator->simulate($match->homeTeam, $match->awayTeam));
        }

        $this->matchRepository->saveAll($weekMatches);

        return BaseApiResource::success(
            data:    MatchResource::collection($weekMatches),
            message: "Week {$week} simulated successfully.",
        );
    }

    public function playAll(): JsonResponse
    {
        $allMatches = $this->matchRepository->all();
        $unplayed   = array_values(array_filter($allMatches, fn(FootballMatch $m) => !$m->isPlayed()));

        if (count($unplayed) === Value::ZERO) {
            return BaseApiResource::error(
                message: 'All matches have already been played.',
                status:  422,
            );
        }

        foreach ($unplayed as $match) {
            $match->play($this->simulator->simulate($match->homeTeam, $match->awayTeam));
        }

        $this->matchRepository->saveAll($unplayed);

        $allMatches = $this->matchRepository->all();

        return BaseApiResource::success(
            data:    MatchResource::collection($allMatches),
            message: 'All remaining matches simulated successfully.',
        );
    }

    public function reset(): JsonResponse
    {
        $this->matchRepository->resetAll();

        return BaseApiResource::success(
            data:    null,
            message: 'League reset successfully.',
        );
    }
}
