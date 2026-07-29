<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Models;

final class Competency extends TenantModel
{
    protected $table = 'tt_competencies';
    protected $casts = ['active' => 'boolean'];
}
