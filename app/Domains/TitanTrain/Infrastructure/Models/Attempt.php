<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Attempt extends TenantModel
{
    protected $table = 'tt_attempts';
    protected $casts = [
        'attempt_number' => 'integer', 'score' => 'float',
        'started_at' => 'datetime', 'submitted_at' => 'datetime', 'graded_at' => 'datetime',
    ];
    public function assignment(): BelongsTo { return $this->belongsTo(Assignment::class, 'assignment_id'); }
    public function assessment(): BelongsTo { return $this->belongsTo(Assessment::class, 'assessment_id'); }
    public function answers(): HasMany { return $this->hasMany(AttemptAnswer::class, 'attempt_id'); }
}
