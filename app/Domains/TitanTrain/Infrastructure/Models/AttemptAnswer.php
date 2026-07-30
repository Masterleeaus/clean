<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AttemptAnswer extends TenantModel
{
    protected $table = 'tt_attempt_answers';
    protected $casts = ['answer' => 'array', 'is_correct' => 'boolean'];
    public function attempt(): BelongsTo { return $this->belongsTo(Attempt::class, 'attempt_id'); }
    public function question(): BelongsTo { return $this->belongsTo(Question::class, 'question_id'); }
}
