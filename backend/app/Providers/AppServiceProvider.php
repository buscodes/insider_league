<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\League\Contracts\MatchRepositoryInterface;
use App\Domains\League\Contracts\MatchSimulatorInterface;
use App\Domains\League\Contracts\TeamRepositoryInterface;
use App\Domains\League\Services\WeightedRandomSimulator;
use App\Infrastructure\Repositories\MatchRepository;
use App\Infrastructure\Repositories\TeamRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TeamRepositoryInterface::class, TeamRepository::class);
        $this->app->bind(MatchRepositoryInterface::class, MatchRepository::class);
        $this->app->bind(MatchSimulatorInterface::class, WeightedRandomSimulator::class);
    }

    public function boot(): void {}
}
