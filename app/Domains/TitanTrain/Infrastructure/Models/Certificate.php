<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Certificate extends TenantModel
{
    protected $table = 'tt_certificates';
    protected $casts = ['metadata' => 'array', 'issued_at' => 'datetime', 'expires_at' => 'datetime', 'revoked_at' => 'datetime'];
    public function program(): BelongsTo { return $this->belongsTo(Program::class, 'program_id'); }
}
