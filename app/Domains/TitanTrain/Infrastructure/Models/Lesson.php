<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Lesson extends TenantModel
{
    protected $table = 'tt_lessons';
    protected $casts = [
        'duration_minutes' => 'integer', 'position' => 'integer',
        'is_required' => 'boolean', 'is_skippable' => 'boolean',
    ];
    public function module(): BelongsTo { return $this->belongsTo(Module::class, 'module_id'); }
}
