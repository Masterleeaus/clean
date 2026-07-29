<?php

declare(strict_types=1);

namespace App\Titan\Tenancy;

use App\Domains\WorkCore\System\Contracts\OperationContextContract;
use App\Domains\WorkCore\System\Contracts\TenantContextContract;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class EnsureActiveCompany
{
    public function __construct(private readonly CompanyContextResolver $resolver) {}

    public function handle(Request $request, Closure $next): Response
    {
        $context = $this->resolver->resolve($request);
        if ($context === null) {
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'error' => [
                        'code' => 'TENANT_CONTEXT_REQUIRED',
                        'message' => 'Select or create an active company before using Titan operations.',
                    ],
                ], 409);
            }

            return redirect()->route('titan.company.setup');
        }

        app()->instance(ActiveCompanyContext::class, $context);
        $request->attributes->set(ActiveCompanyContext::class, $context);

        $tenant = app(TenantContextContract::class);
        $operation = app(OperationContextContract::class);
        $tenant->set($context->companyId, $context->userId);
        $operation->set(
            companyId: $context->companyId,
            actorId: $context->userId,
            correlationId: $this->correlationId($request),
            causationId: $this->optionalHeader($request, 'X-Titan-Causation-ID'),
            locale: (string) config('workcore.context.default_locale', 'en_AU'),
            timezone: (string) config('workcore.context.default_timezone', 'Australia/Melbourne'),
        );

        try {
            return $next($request);
        } finally {
            $operation->clear();
            $tenant->clear();
            $request->attributes->remove(ActiveCompanyContext::class);
            app()->forgetInstance(ActiveCompanyContext::class);
        }
    }

    private function correlationId(Request $request): string
    {
        return $this->optionalHeader($request, 'X-Titan-Correlation-ID') ?? (string) Str::uuid();
    }

    private function optionalHeader(Request $request, string $name): ?string
    {
        $value = trim((string) $request->header($name, ''));

        return $value !== '' && strlen($value) <= 160 ? $value : null;
    }
}
