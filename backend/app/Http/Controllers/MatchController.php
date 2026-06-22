<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\League\Contracts\MatchRepositoryInterface;
use App\Domains\League\ValueObjects\MatchScore;
use App\Http\Requests\UpdateMatchRequest;
use App\Core\Constants\HttpStatus;
use App\Http\Resources\BaseApiResource;
use App\Http\Resources\MatchResource;
use Illuminate\Http\JsonResponse;

class MatchController extends Controller
{
    public function __construct(
        private readonly MatchRepositoryInterface $matchRepository,
    ) {}

    public function update(UpdateMatchRequest $request, int $id): JsonResponse
    {
        $match = null;

        foreach ($this->matchRepository->all() as $m) {
            if ($m->id === $id) {
                $match = $m;
                break;
            }
        }

        if ($match === null) {
            return BaseApiResource::error(message: 'Match not found.', status: HttpStatus::NOT_FOUND);
        }

        $match->updateScore(new MatchScore(
            homeScore: $request->integer('home_score'),
            awayScore: $request->integer('away_score'),
        ));

        $this->matchRepository->save($match);

        return BaseApiResource::success(
            data:    MatchResource::make($match),
            message: 'Match score updated successfully.',
        );
    }
}
