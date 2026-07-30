<?php

declare(strict_types=1);

namespace App\Titan\Maps;

use App\Titan\Permissions\PermissionResolver;
use App\Titan\Tenancy\ActiveCompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class MapsExportController
{
    public function __invoke(
        string $reference,
        Request $request,
        ActiveCompanyContext $context,
        PermissionResolver $permissions,
    ): StreamedResponse {
        if (! $permissions->allows($context->userId, $context->companyId, 'titan-maps-intelligence.search.export')) {
            throw new AccessDeniedHttpException('You are not permitted to download Maps exports.');
        }

        $record = DB::table('maps_private_exports')
            ->where('reference', $reference)
            ->where('company_id', $context->companyId)
            ->where('expires_at', '>', now())
            ->first();
        if ($record === null || ! Storage::disk((string) $record->disk)->exists((string) $record->path)) {
            throw new NotFoundHttpException('The Maps export does not exist or has expired.');
        }

        return Storage::disk((string) $record->disk)->download(
            (string) $record->path,
            (string) $record->filename,
            ['Content-Type' => (string) $record->mime_type],
        );
    }
}
