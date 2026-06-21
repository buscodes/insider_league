<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\League\Contracts\MatchRepositoryInterface;
use App\Domains\League\Contracts\TeamRepositoryInterface;
use App\Domains\League\Services\FixtureGeneratorService;
use App\Http\Resources\BaseApiResource;
use App\Http\Resources\MatchResource;
use App\Infrastructure\Models\FootballMatchModel;
use Illuminate\Http\JsonResponse;

class FixtureController extends Controller
{
    public function __construct(
        private readonly TeamRepositoryInterface $teamRepository,
        private readonly MatchRepositoryInterface $matchRepository,
        private readonly FixtureGeneratorService $fixtureGenerator,
    ) {}

    public function index(): JsonResponse
    {
        $matches = $this->matchRepository->all();

        return BaseApiResource::success(
            data:    MatchResource::collection($matches),
            message: 'Fixtures retrieved successfully.',
        );
    }

    public function generate(): JsonResponse
    {
        if (FootballMatchModel::exists()) {
            return BaseApiResource::error(
                message: 'Fixture already exists. Reset the league before generating a new one.',
                status:  422,
            );
        }

        $teams    = $this->teamRepository->all();
        $fixtures = $this->fixtureGenerator->generate($teams);
        $this->matchRepository->saveAll($fixtures);

        $matches = $this->matchRepository->all();

        return BaseApiResource::success(
            data:    MatchResource::collection($matches),
            message: 'Fixtures generated successfully.',
            status:  201,
        );
    }
}
