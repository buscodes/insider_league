<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\League\Contracts\TeamRepositoryInterface;
use App\Http\Resources\BaseApiResource;
use App\Http\Resources\TeamResource;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    public function __construct(
        private readonly TeamRepositoryInterface $teamRepository,
    ) {}

    public function index(): JsonResponse
    {
        $teams = $this->teamRepository->all();

        return BaseApiResource::success(
            data:    TeamResource::collection($teams),
            message: 'Teams retrieved successfully.',
        );
    }
}
