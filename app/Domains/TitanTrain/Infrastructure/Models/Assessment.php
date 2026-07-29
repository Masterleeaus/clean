<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Assessment extends TenantModel
{
    protected $table = 'tt_assessments';
    protected $casts = ['pass_mark' => 'integer', 'attempt_limit' => 'integer', 'duration_minutes' => 'integer'];
    public function program(): BelongsTo { return $this->belongsTo(Program::class, 'program_id'); }
    public function questions(): HasMany { return $this->hasMany(Question::class, 'assessment_id')->orderBy('position'); }
}
