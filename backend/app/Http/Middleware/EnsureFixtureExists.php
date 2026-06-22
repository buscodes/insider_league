<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Constants\HttpStatus;
use App\Http\Resources\BaseApiResource;
use App\Infrastructure\Models\FootballMatchModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFixtureExists
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!FootballMatchModel::exists()) {
            return BaseApiResource::error(
                message: 'No fixture has been generated yet. Please call POST /api/v1/fixtures/generate first.',
                status:  HttpStatus::UNPROCESSABLE,
            );
        }

        return $next($request);
    }
}
