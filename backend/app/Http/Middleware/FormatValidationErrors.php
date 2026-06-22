<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Exceptions\DomainException;
use App\Core\Constants\HttpStatus;
use App\Http\Resources\BaseApiResource;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class FormatValidationErrors
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (ValidationException $e) {
            return BaseApiResource::error(
                message: 'The given data was invalid.',
                data:    ['errors' => $e->errors()],
                status:  HttpStatus::UNPROCESSABLE,
            );
        } catch (DomainException $e) {
            return BaseApiResource::error(
                message: $e->getMessage(),
                data:    null,
                status:  HttpStatus::BAD_REQUEST,
            );
        }
    }
}
