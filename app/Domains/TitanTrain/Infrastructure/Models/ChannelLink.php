<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Models;

final class ChannelLink extends TenantModel
{
    protected $table = 'tt_channel_links';
    protected $casts = ['metadata' => 'array'];
}
