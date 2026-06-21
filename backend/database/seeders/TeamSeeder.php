<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Models\TeamModel;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            ['name' => 'Manchester City', 'power' => 95],
            ['name' => 'Liverpool',       'power' => 90],
            ['name' => 'Arsenal',         'power' => 88],
            ['name' => 'Chelsea',         'power' => 82],
        ];

        foreach ($teams as $team) {
            TeamModel::create($team);
        }
    }
}
