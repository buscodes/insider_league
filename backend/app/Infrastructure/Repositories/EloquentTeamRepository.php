<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domains\League\Contracts\TeamRepositoryInterface;
use App\Domains\League\Entities\Team;
use App\Infrastructure\Models\TeamModel;

final class EloquentTeamRepository implements TeamRepositoryInterface
{
    public function all(): array
    {
        return TeamModel::all()
            ->map(fn(TeamModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findById(int $id): ?Team
    {
        $model = TeamModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    private function toDomain(TeamModel $model): Team
    {
        return new Team($model->id, $model->name, $model->power);
    }
}
