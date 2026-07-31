<?php

declare(strict_types=1);

namespace App\Titan\Maps;

use App\Extensions\TitanMapsIntelligence\Contracts\PrivateExportStore;
use App\Titan\Tenancy\ActiveCompanyContext;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;

final class MapsPrivateExportStore implements PrivateExportStore
{
    public function __construct(private readonly ActiveCompanyContext $context) {}

    public function put(
        string $companyId,
        string $filename,
        string $mimeType,
        string $contents,
        DateTimeInterface $expiresAt,
    ): array {
        if (! is_numeric($companyId) || (int) $companyId !== $this->context->companyId) {
            throw new RuntimeException('Private export storage requires the active Titan company.');
        }

        $reference = (string) Str::uuid();
        $safeFilename = preg_replace('/[^A-Za-z0-9._-]+/', '-', basename($filename)) ?: 'maps-export.dat';
        $path = sprintf('titan-maps/%d/%s/%s', $this->context->companyId, $reference, $safeFilename);
        $disk = (string) config('titan.private_disk', 'local');
        if (! Storage::disk($disk)->put($path, $contents, ['visibility' => 'private'])) {
            throw new RuntimeException('The private Maps export could not be stored.');
        }

        DB::table('maps_private_exports')->insert([
            'reference' => $reference,
            'company_id' => $this->context->companyId,
            'user_id' => $this->context->userId,
            'disk' => $disk,
            'path' => $path,
            'filename' => $safeFilename,
            'mime_type' => $mimeType,
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'reference' => $reference,
            'signed_url' => URL::temporarySignedRoute(
                'titan-maps-intelligence.exports.download',
                $expiresAt,
                ['reference' => $reference],
            ),
            'expires_at' => $expiresAt->format(DATE_ATOM),
        ];
    }
}
