<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

final class SetPlatformAdminCommand extends Command
{
    protected $signature = 'platform:admin {email : Existing user email} {--revoke : Remove platform administrator authority}';
    protected $description = 'Grant or revoke the explicit platform administrator flag for an existing user.';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user) {
            $this->error('No user exists with that email address.');
            return self::FAILURE;
        }

        $enabled = ! $this->option('revoke');
        $user->forceFill(['is_platform_admin' => $enabled])->saveQuietly();
        $this->info($enabled ? 'Platform administrator authority granted.' : 'Platform administrator authority revoked.');

        return self::SUCCESS;
    }
}
