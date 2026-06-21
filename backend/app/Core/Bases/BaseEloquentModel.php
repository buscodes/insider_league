<?php

declare(strict_types=1);

namespace App\Core\Bases;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class BaseEloquentModel extends Model
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->toIso8601String();
    }
}