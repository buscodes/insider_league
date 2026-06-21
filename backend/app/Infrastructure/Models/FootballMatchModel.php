<?php

declare(strict_types=1);

namespace App\Infrastructure\Models;

use App\Core\Bases\BaseEloquentModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['home_team_id', 'away_team_id', 'week', 'home_score', 'away_score', 'is_played'])]
class FootballMatchModel extends BaseEloquentModel
{
    protected $table = 'matches';

    protected function casts(): array
    {
        return [
            'home_score' => 'integer',
            'away_score' => 'integer',
            'week'       => 'integer',
            'is_played'  => 'boolean',
        ];
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(TeamModel::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(TeamModel::class, 'away_team_id');
    }
}
