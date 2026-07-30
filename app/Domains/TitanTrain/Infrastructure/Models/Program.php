<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

final class Program extends TenantModel
{
    protected $table = 'tt_programs';
    protected $casts = ['requirements' => 'array', 'outcomes' => 'array', 'published_at' => 'datetime'];
    public function modules(): HasMany { return $this->hasMany(Module::class, 'program_id')->orderBy('position'); }
    public function assessments(): HasMany { return $this->hasMany(Assessment::class, 'program_id')->orderBy('id'); }
    public function assignments(): HasMany { return $this->hasMany(Assignment::class, 'program_id'); }
}
