<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Services;

use App\Domains\TitanPay\Models\CollectionSession;
use App\Domains\TitanPay\Models\QrCode as QrCodeRecord;
use RuntimeException;
use Symfony\Component\Process\Process;

final class QrCodeService
{
    public function record(CollectionSession $session, string $publicToken): QrCodeRecord
    {
        $payload = route('titanpay.public.show', ['token' => $publicToken]);

        return QrCodeRecord::query()->updateOrCreate(
            ['collection_session_id' => $session->id],
            [
                'payload' => $payload,
                'format' => 'svg',
                'expires_at' => $session->expires_at,
                'metadata_json' => [
                    'purpose' => 'invoice_collection',
                    'invoice_id' => $session->invoice_id,
                    'sha256' => hash('sha256', $payload),
                ],
            ],
        );
    }

    public function svg(CollectionSession $session, string $publicToken): string
    {
        $record = $this->record($session, $publicToken);
        $script = base_path('resources/python/titanpay_qr/generate.py');
        if (! is_file($script)) {
            throw new RuntimeException('The bundled Titan Pay QR renderer is missing.');
        }

        $process = new Process([
            (string) config('titanpay.qr.python_binary', 'python3'),
            $script,
            $record->payload,
        ], base_path(), null, null, (float) config('titanpay.qr.timeout_seconds', 5));
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Titan Pay could not render the QR code: '.trim($process->getErrorOutput()));
        }

        $svg = $process->getOutput();
        if (! str_contains($svg, '<svg') || strlen($svg) > 2_000_000) {
            throw new RuntimeException('Titan Pay received an invalid QR image from the renderer.');
        }

        return $svg;
    }

    public function imageUrl(string $publicToken): string
    {
        return route('titanpay.public.qr', ['token' => $publicToken]);
    }
}
