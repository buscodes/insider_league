<?php

declare(strict_types=1);

namespace App\Infrastructure\Models;

use App\Core\Bases\BaseEloquentModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'power'])]
class TeamModel extends BaseEloquentModel
{
    protected $table = 'teams';

    public function homeMatches(): HasMany
    {
        return $this->hasMany(FootballMatchModel::class, 'home_team_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(FootballMatchModel::class, 'away_team_id');
    }
}
