<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domains\League\Entities\Team;

final class TeamResource
{
    public static function make(Team $team): array
    {
        return [
            'id'    => $team->id,
            'name'  => $team->name,
            'power' => $team->power,
        ];
    }

    /** @param Team[] $teams */
    public static function collection(array $teams): array
    {
        return array_map(self::make(...), $teams);
    }

    private function __construct() {}
}
