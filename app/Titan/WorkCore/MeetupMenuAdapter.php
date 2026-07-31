<?php

declare(strict_types=1);

namespace App\Titan\WorkCore;

use App\Domains\WorkCore\System\Host\Contracts\MenuAdapterContract;

final class MeetupMenuAdapter implements MenuAdapterContract
{
    public function register(): void
    {
        // Meetup renders WorkCore navigation from the host capability registry.
    }

    public function clearCache(): void
    {
        cache()->forget('titan.workcore.navigation');
    }
}
