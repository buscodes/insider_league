<?php

declare(strict_types=1);

namespace App\Domains\League\Entities;

class Team
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $power,
    ) {}
}
