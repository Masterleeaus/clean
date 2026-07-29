<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Http\Controllers;

use App\Domains\TitanPay\Services\CollectionService;
use App\Domains\TitanPay\Services\QrCodeService;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

final class QrCodeController extends Controller
{
    public function __invoke(string $token, CollectionService $collections, QrCodeService $qr): Response
    {
        $session = $collections->findPublic($token);
        abort_if($session->isExpired(), 410, 'This payment link has expired.');

        return response($qr->svg($session, $token), 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'",
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
