<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Question extends TenantModel
{
    protected $table = 'tt_questions';
    protected $hidden = ['correct_answer'];
    protected $casts = ['options' => 'array', 'correct_answer' => 'array', 'requires_review' => 'boolean'];
    public function assessment(): BelongsTo { return $this->belongsTo(Assessment::class, 'assessment_id'); }
}
