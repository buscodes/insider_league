<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\League\Contracts\MatchRepositoryInterface;
use App\Domains\League\Contracts\MatchSimulatorInterface;
use App\Domains\League\Contracts\TeamRepositoryInterface;
use App\Domains\League\Services\WeightedRandomSimulator;
use App\Infrastructure\Repositories\EloquentMatchRepository;
use App\Infrastructure\Repositories\EloquentTeamRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TeamRepositoryInterface::class, EloquentTeamRepository::class);
        $this->app->bind(MatchRepositoryInterface::class, EloquentMatchRepository::class);
        $this->app->bind(MatchSimulatorInterface::class, WeightedRandomSimulator::class);
    }

    public function boot(): void {}
}
