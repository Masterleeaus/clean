<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Module extends TenantModel
{
    protected $table = 'tt_modules';
    protected $casts = ['position' => 'integer'];
    public function program(): BelongsTo { return $this->belongsTo(Program::class, 'program_id'); }
    public function lessons(): HasMany { return $this->hasMany(Lesson::class, 'module_id')->orderBy('position'); }
}
