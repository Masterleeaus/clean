<?php

declare(strict_types=1);

namespace App\Titan\WorkCore;

use App\Domains\WorkCore\System\Host\Contracts\StorageAdapterContract;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class MeetupStorageAdapter implements StorageAdapterContract
{
    public function put(string $path, string $contents, array $options = []): bool
    {
        return Storage::disk($this->disk())->put($this->normalise($path), $contents, $options);
    }

    public function temporaryUrl(string $path, int $minutes = 15): ?string
    {
        try {
            return Storage::disk($this->disk())->temporaryUrl(
                $this->normalise($path),
                now()->addMinutes(max(1, min($minutes, 1440))),
            );
        } catch (Throwable) {
            return null;
        }
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->disk())->delete($this->normalise($path));
    }

    private function disk(): string
    {
        return (string) config('titan.private_disk', 'local');
    }

    private function normalise(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', trim($path)), '/');
        if ($path === '' || str_contains($path, '../')) {
            throw new \InvalidArgumentException('A safe private storage path is required.');
        }

        return 'workcore/' . $path;
    }
}
