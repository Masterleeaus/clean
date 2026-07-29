<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Assignment extends TenantModel
{
    protected $table = 'tt_assignments';
    protected $casts = [
        'metadata' => 'array', 'due_at' => 'datetime', 'started_at' => 'datetime',
        'lessons_completed_at' => 'datetime', 'knowledge_passed_at' => 'datetime',
        'practical_passed_at' => 'datetime', 'completed_at' => 'datetime',
        'cycle_number' => 'integer', 'progress_percent' => 'integer',
    ];
    public function program(): BelongsTo { return $this->belongsTo(Program::class, 'program_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function completions(): HasMany { return $this->hasMany(LessonCompletion::class, 'assignment_id'); }
    public function attempts(): HasMany { return $this->hasMany(Attempt::class, 'assignment_id'); }
}
