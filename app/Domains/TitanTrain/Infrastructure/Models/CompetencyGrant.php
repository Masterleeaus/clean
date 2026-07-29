<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CompetencyGrant extends TenantModel
{
    protected $table = 'tt_competency_grants';
    protected $casts = ['metadata' => 'array', 'granted_at' => 'datetime', 'expires_at' => 'datetime', 'revoked_at' => 'datetime'];
    public function competency(): BelongsTo { return $this->belongsTo(Competency::class, 'competency_id'); }
}
