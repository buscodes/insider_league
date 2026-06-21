<?php

declare(strict_types=1);

namespace App\Core\Constants;

final class LeagueConstants
{
    public const int   TOTAL_TEAMS          = 4;
    public const int   TOTAL_WEEKS          = 6;
    public const int   MATCHES_PER_WEEK     = 2;
    public const int   TOTAL_MATCHES        = 12;
    public const int   MIN_PREDICTION_WEEK    = 4;
    public const int   MONTE_CARLO_ITERATIONS = 1000;
    public const int   HOME_ADVANTAGE  = 10;
    public const float DRAW_THRESHOLD  = 0.15;
    public const float DRAW_CHANCE     = 0.25;

    private function __construct() {}
}
