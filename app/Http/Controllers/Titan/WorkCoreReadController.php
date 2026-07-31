<?php

declare(strict_types=1);

namespace App\Http\Controllers\Titan;

use App\Domains\WorkCore\System\Api\ApiResponseFactory;
use App\Domains\WorkCore\System\Contracts\OperationContextContract;
use App\Domains\WorkCore\System\ReadModels\ReadModelExecutionException;
use App\Domains\WorkCore\System\ReadModels\ReadModelExecutor;
use App\Http\Controllers\Controller;
use App\Titan\Tenancy\ActiveCompanyContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class WorkCoreReadController extends Controller
{
    public function __invoke(
        string $read,
        Request $request,
        ActiveCompanyContext $context,
        ReadModelExecutor $executor,
        ApiResponseFactory $responses,
        OperationContextContract $operation,
    ): JsonResponse {
        $input = $request->all();
        if ($this->containsTenantAuthority($input)) {
            return response()->json([
                'data' => null,
                'errors' => [[
                    'code' => 'TENANT_AUTHORITY_FORBIDDEN',
                    'message' => 'Company context is resolved by the authenticated Titan host and cannot be supplied in read filters.',
                ]],
                'correlation_id' => $operation->hasContext() ? $operation->correlationId() : null,
            ], 422);
        }

        $validated = $request->validate([
            'filters' => ['sometimes', 'array'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        try {
            $result = $executor->execute(
                key: $read,
                filters: (array) ($validated['filters'] ?? []),
                companyId: $context->companyId,
                actorId: $context->userId,
                perPage: (int) ($validated['per_page'] ?? 25),
            );

            if ($result instanceof LengthAwarePaginator) {
                return $responses->paginated($result, static fn (array $item): array => $item);
            }
            if (is_object($result) && method_exists($result, 'toArray')) {
                $result = $result->toArray();
            }
            if (! is_array($result)) {
                throw new ReadModelExecutionException('The WorkCore read returned an invalid result.', 500);
            }

            return $responses->success($result, ['read_model' => $read]);
        } catch (ReadModelExecutionException $exception) {
            return response()->json([
                'data' => null,
                'errors' => [[
                    'code' => 'WORKCORE_READ_REJECTED',
                    'message' => $exception->getMessage(),
                ]],
                'correlation_id' => $operation->hasContext() ? $operation->correlationId() : null,
            ], $exception->statusCode());
        } catch (Throwable $exception) {
            $correlationId = $operation->hasContext() ? $operation->correlationId() : null;
            Log::error('Unexpected WorkCore read failure.', [
                'read_model' => $read,
                'company_id' => $context->companyId,
                'user_id' => $context->userId,
                'correlation_id' => $correlationId,
                'exception' => $exception,
            ]);

            return response()->json([
                'data' => null,
                'errors' => [[
                    'code' => 'WORKCORE_READ_FAILED',
                    'message' => 'The WorkCore read could not be completed safely.',
                ]],
                'correlation_id' => $correlationId,
            ], 500);
        }
    }

    /** @param array<string|int,mixed> $value */
    private function containsTenantAuthority(array $value): bool
    {
        foreach ($value as $key => $item) {
            $normalised = strtolower(str_replace(['_', '-'], '', (string) $key));
            if ($normalised === 'companyid') {
                return true;
            }
            if (is_array($item) && $this->containsTenantAuthority($item)) {
                return true;
            }
        }

        return false;
    }
}
