<?php

declare(strict_types=1);

namespace App\Domains\League\Contracts;

use App\Domains\League\Entities\Team;

interface TeamRepositoryInterface
{
    /** @return Team[] */
    public function all(): array;

    public function findById(int $id): ?Team;
}
