<?php

declare(strict_types=1);

namespace App\Core\Constants;

final class Value
{
    public const bool   TRUE         = true;
    public const bool   FALSE        = false;
    public const int    ZERO         = 0;
    public const int    ONE          = 1;
    public const string EMPTY_STRING = '';

    private function __construct() {}
}
