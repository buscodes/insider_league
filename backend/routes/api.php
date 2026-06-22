<?php

declare(strict_types=1);

use App\Http\Controllers\FixtureController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\TeamController;
use App\Http\Middleware\EnsureFixtureExists;
use App\Core\Constants\AppVersion;
use Illuminate\Support\Facades\Route;

Route::prefix(AppVersion::API_PREFIX)->group(function (): void {

    Route::middleware('throttle:180,1')->group(function (): void {
        Route::get('teams', [TeamController::class, 'index']);
        Route::get('fixtures', [FixtureController::class, 'index']);
        Route::get('league-table', [LeagueController::class, 'table']);
        Route::get('predictions', [LeagueController::class, 'predictions']);
    });

    Route::middleware(['throttle:90,1', EnsureFixtureExists::class])->group(function (): void {
        Route::post('simulation/play-week', [SimulationController::class, 'playWeek']);
        Route::post('simulation/play-all', [SimulationController::class, 'playAll']);
        Route::patch('matches/{id}', [MatchController::class, 'update']);
    });

    Route::middleware('throttle:30,1')->group(function (): void {
        Route::post('fixtures/generate', [FixtureController::class, 'generate']);
        Route::post('simulation/reset', [SimulationController::class, 'reset']);
    });
});
