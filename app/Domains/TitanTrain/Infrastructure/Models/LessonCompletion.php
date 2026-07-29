<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LessonCompletion extends TenantModel
{
    public $timestamps = false;
    protected $table = 'tt_lesson_completions';
    protected $casts = ['completed_at' => 'datetime'];
    public function assignment(): BelongsTo { return $this->belongsTo(Assignment::class, 'assignment_id'); }
    public function lesson(): BelongsTo { return $this->belongsTo(Lesson::class, 'lesson_id'); }
}
