<?php

declare(strict_types=1);

namespace App\Core\Constants;

final class HttpStatus
{
    public const int OK         = 200;
    public const int CREATED    = 201;
    public const int BAD_REQUEST = 400;
    public const int NOT_FOUND  = 404;
    public const int UNPROCESSABLE = 422;

    private function __construct() {}
}
