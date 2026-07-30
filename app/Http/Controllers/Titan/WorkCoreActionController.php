<?php

declare(strict_types=1);

namespace App\Http\Controllers\Titan;

use App\Domains\WorkCore\System\Actions\ActionRequest;
use App\Domains\WorkCore\System\Actions\BusinessActionDispatcher;
use App\Domains\WorkCore\System\Actions\Exceptions\ActionDispatchException;
use App\Domains\WorkCore\System\Api\ApiResponseFactory;
use App\Domains\WorkCore\System\Contracts\OperationContextContract;
use App\Http\Controllers\Controller;
use App\Titan\Tenancy\ActiveCompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class WorkCoreActionController extends Controller
{
    public function __invoke(
        string $action,
        Request $request,
        ActiveCompanyContext $context,
        BusinessActionDispatcher $dispatcher,
        ApiResponseFactory $responses,
        OperationContextContract $operation,
    ): JsonResponse {
        $input = $request->all();
        if ($this->containsTenantAuthority($input)) {
            return response()->json([
                'errors' => [[
                    'code' => 'TENANT_AUTHORITY_FORBIDDEN',
                    'message' => 'Company context is resolved by the authenticated Titan host and cannot be supplied in an action payload.',
                ]],
                'correlation_id' => $operation->hasContext() ? $operation->correlationId() : null,
            ], 422);
        }

        $validated = $request->validate([
            'payload' => ['sometimes', 'array'],
            'idempotency_key' => ['sometimes', 'string', 'max:190'],
            'confirmation_id' => ['sometimes', 'nullable', 'string', 'max:190'],
        ]);

        $idempotencyKey = trim((string) $request->header(
            'Idempotency-Key',
            $validated['idempotency_key'] ?? '',
        ));
        if ($idempotencyKey === '') {
            $idempotencyKey = (string) Str::uuid();
        }

        $confirmationId = trim((string) $request->header(
            'X-Titan-Confirmation-ID',
            $validated['confirmation_id'] ?? '',
        ));

        try {
            $result = $dispatcher->dispatch(new ActionRequest(
                key: $action,
                payload: (array) ($validated['payload'] ?? []),
                companyId: $context->companyId,
                actorId: $context->userId,
                idempotencyKey: $idempotencyKey,
                confirmationId: $confirmationId !== '' ? $confirmationId : null,
                source: 'titan_api',
            ));

            return $responses->action($result);
        } catch (ActionDispatchException $exception) {
            return response()->json([
                'data' => null,
                'errors' => [[
                    'code' => 'WORKCORE_ACTION_REJECTED',
                    'message' => $exception->getMessage(),
                ]],
                'correlation_id' => $operation->hasContext() ? $operation->correlationId() : null,
            ], $exception->getStatusCode());
        } catch (Throwable $exception) {
            $correlationId = $operation->hasContext() ? $operation->correlationId() : null;
            Log::error('Unexpected WorkCore action failure.', [
                'action' => $action,
                'company_id' => $context->companyId,
                'user_id' => $context->userId,
                'correlation_id' => $correlationId,
                'exception' => $exception,
            ]);

            return response()->json([
                'data' => null,
                'errors' => [[
                    'code' => 'WORKCORE_ACTION_FAILED',
                    'message' => 'The action could not be completed safely.',
                ]],
                'correlation_id' => $correlationId,
            ], 500);
        }
    }

    /** @param array<string|int,mixed> $value */
    private function containsTenantAuthority(array $value): bool
    {
        foreach ($value as $key => $item) {
            if (is_string($key) && in_array(strtolower($key), ['company_id', 'companyid'], true)) {
                return true;
            }
            if (is_array($item) && $this->containsTenantAuthority($item)) {
                return true;
            }
        }

        return false;
    }
}
