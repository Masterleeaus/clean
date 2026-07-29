<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Http\Controllers;

use App\Domains\TitanPay\Models\GatewayConnection;
use App\Domains\TitanPay\Services\PaymentReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class WebhookController extends Controller
{
    public function __invoke(
        Request $request,
        string $provider,
        string $connection,
        PaymentReconciliationService $service,
    ) {
        $gatewayConnection = GatewayConnection::query()
            ->withoutGlobalScope('company')
            ->findOrFail($connection);

        abort_unless(
            $gatewayConnection->is_active && $gatewayConnection->provider === $provider,
            404
        );

        $event = $service->handleWebhook($gatewayConnection, $request);

        return response()->json([
            'received' => true,
            'status' => $event->processing_status,
        ]);
    }
}
