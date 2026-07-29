<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class MigrateChatAttachmentsCommand extends Command
{
    protected $signature = 'chat:migrate-private-attachments
        {--commit : Move files and delete their legacy public copies; without this option the command is a dry run}';
    protected $description = 'Move legacy public chat attachments into authenticated private storage.';

    public function handle(): int
    {
        $commit = (bool) $this->option('commit');
        $found = 0;
        $migrated = 0;
        $failed = 0;

        Message::query()
            ->whereNotNull('attachment_path')
            ->orderBy('id')
            ->chunkById(200, function ($messages) use ($commit, &$found, &$migrated, &$failed): void {
                foreach ($messages as $message) {
                    $path = (string) $message->attachment_path;
                    if ($path === '' || ! Storage::disk('public')->exists($path)) {
                        continue;
                    }

                    $found++;
                    if (! $commit) {
                        $this->line('[dry-run] '.$path);
                        continue;
                    }

                    try {
                        if (! Storage::disk('local')->exists($path)) {
                            $stream = Storage::disk('public')->readStream($path);
                            if (! is_resource($stream)) {
                                throw new RuntimeException('Unable to open the public attachment stream.');
                            }

                            try {
                                if (! Storage::disk('local')->writeStream($path, $stream)) {
                                    throw new RuntimeException('Unable to write the private attachment stream.');
                                }
                            } finally {
                                fclose($stream);
                            }
                        }

                        if (! Storage::disk('local')->exists($path)) {
                            throw new RuntimeException('Private attachment verification failed.');
                        }

                        Storage::disk('public')->delete($path);
                        $migrated++;
                    } catch (\Throwable $error) {
                        $failed++;
                        $this->error($path.': '.$error->getMessage());
                    }
                }
            });

        $this->info(sprintf(
            '%s Found %d legacy public attachment(s); migrated %d; failed %d.',
            $commit ? 'Completed.' : 'Dry run complete.',
            $found,
            $migrated,
            $failed,
        ));

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
