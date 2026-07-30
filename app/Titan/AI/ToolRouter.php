<?php

declare(strict_types=1);

namespace App\Titan\AI;

use App\Domains\WorkCore\System\Actions\ActionRequest;
use App\Domains\WorkCore\System\Actions\BusinessActionDispatcher;
use App\Domains\WorkCore\System\Actions\BusinessActionRegistry;
use App\Domains\WorkCore\System\ReadModels\{ReadModelExecutor, ReadModelRegistry};
use App\Titan\Audit\AuditRecorder;
use App\Titan\Capabilities\CapabilityRegistry;
use App\Titan\Permissions\PermissionResolver;
use App\Titan\Tenancy\ActiveCompanyContext;
use App\Titan\Tenancy\CompanyContextResolver;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use Throwable;

final class ToolRouter
{
    public function __construct(
        private readonly Container $container,
        private readonly CapabilityRegistry $capabilities,
        private readonly BusinessActionRegistry $actions,
        private readonly BusinessActionDispatcher $dispatcher,
        private readonly ReadModelRegistry $readModels,
        private readonly ReadModelExecutor $readExecutor,
        private readonly PermissionResolver $permissions,
        private readonly CompanyContextResolver $contexts,
        private readonly AuditRecorder $audit,
    ) {}

    /** @param array<string,mixed> $arguments @param array<string,mixed> $execution */
    public function execute(string $tool, array $arguments, array $execution = []): array
    {
        $tool = trim($tool);
        if ($tool === '' || $this->containsTenantOverride($arguments)) {
            return $this->failure('TITAN_TOOL_INVALID_REQUEST', 'The requested Titan tool input is invalid.');
        }

        $context = $this->context();
        $conversationId = isset($execution['conversation']) ? (string) $execution['conversation'] : null;
        $correlationId = isset($execution['correlation']) ? (string) $execution['correlation'] : (string) Str::uuid();

        try {
            if ($this->capabilities->has($tool)) {
                $result = $this->executeCapability($tool, $arguments, $context);
            } elseif ($this->actions->has($tool)) {
                $result = $this->executeWorkCoreAction($tool, $arguments, $execution, $context, $correlationId);
            } elseif ($this->readModels->has($tool)) {
                $result = $this->executeWorkCoreRead($tool, $arguments, $context, $correlationId);
            } else {
                return $this->failure('TITAN_TOOL_NOT_REGISTERED', 'The requested Titan tool is not registered.');
            }

            $this->audit->record([
                'company_id' => $context->companyId,
                'user_id' => $context->userId,
                'agent_id' => 'titan-zero',
                'conversation_id' => $conversationId,
                'action' => 'titan.tool.executed',
                'entity_type' => 'titan_tool',
                'entity_id' => $tool,
                'reason' => 'Titan Zero executed a registered capability.',
                'correlation_id' => $correlationId,
                'metadata' => ['ok' => (bool) ($result['ok'] ?? false)],
            ]);

            return $result;
        } catch (Throwable $exception) {
            $this->recordFailure($tool, $context, $conversationId, $correlationId, $exception);
            $status = (int) $exception->getCode();
            if ($status === 428) {
                return $this->failure('TITAN_TOOL_CONFIRMATION_REQUIRED', 'Explicit confirmation is required before this action can run.');
            }
            if (in_array($status, [401, 403], true)) {
                return $this->failure('TITAN_TOOL_PERMISSION_DENIED', 'The active user is not permitted to run this tool.');
            }

            return $this->failure('TITAN_TOOL_EXECUTION_FAILED', 'The registered Titan tool could not be completed.');
        }
    }

    /** @param array<string,mixed> $arguments */
    private function executeCapability(string $tool, array $arguments, ActiveCompanyContext $context): array
    {
        $definition = $this->capabilities->get($tool) ?? [];
        $handlerClass = $definition['handler'] ?? null;
        if (! is_string($handlerClass) || $handlerClass === '') {
            return $this->failure('TITAN_TOOL_NOT_EXECUTABLE', 'The registered capability is informational and cannot be executed.');
        }

        $permission = trim((string) ($definition['permission'] ?? ''));
        if ($permission !== '' && ! $this->permissions->allows($context->userId, $context->companyId, $permission)) {
            return $this->failure('TITAN_TOOL_PERMISSION_DENIED', 'The active user is not permitted to run this tool.');
        }

        $handler = $this->container->make($handlerClass);
        if (! is_object($handler) || ! method_exists($handler, 'execute')) {
            return $this->failure('TITAN_TOOL_NOT_EXECUTABLE', 'The registered capability handler is unavailable.');
        }

        $result = $handler->execute($arguments);
        if (! is_array($result)) {
            return $this->failure('TITAN_TOOL_INVALID_RESULT', 'The registered capability returned an invalid result.');
        }

        return $result + ['ok' => true];
    }

    /** @param array<string,mixed> $arguments @param array<string,mixed> $execution */
    private function executeWorkCoreAction(
        string $tool,
        array $arguments,
        array $execution,
        ActiveCompanyContext $context,
        string $correlationId,
    ): array {
        $idempotency = trim((string) ($execution['idempotency'] ?? ''));
        if ($idempotency === '') {
            $idempotency = hash('sha256', $context->companyId . '|' . $context->userId . '|' . $tool . '|' . json_encode($arguments, JSON_THROW_ON_ERROR));
        }

        $result = $this->dispatcher->dispatch(new ActionRequest(
            key: $tool,
            payload: $arguments,
            companyId: $context->companyId,
            actorId: $context->userId,
            idempotencyKey: $idempotency,
            confirmationId: isset($execution['confirmation']) ? (string) $execution['confirmation'] : null,
            source: 'titan_zero',
        ));

        return ['ok' => true, 'tool' => $tool, 'data' => $result->toArray(), 'correlation' => $correlationId];
    }

    /** @param array<string,mixed> $arguments */
    private function executeWorkCoreRead(
        string $tool,
        array $arguments,
        ActiveCompanyContext $context,
        string $correlationId,
    ): array {
        $perPage = (int) ($arguments['per_page'] ?? 25);
        $filters = isset($arguments['filters']) && is_array($arguments['filters'])
            ? $arguments['filters']
            : array_diff_key($arguments, ['per_page' => true]);

        $result = $this->readExecutor->execute(
            key: $tool,
            filters: $filters,
            companyId: $context->companyId,
            actorId: $context->userId,
            perPage: $perPage,
        );
        if (is_object($result) && method_exists($result, 'toArray')) {
            $result = $result->toArray();
        }
        if (! is_array($result)) {
            return $this->failure('TITAN_TOOL_INVALID_RESULT', 'The registered WorkCore read returned an invalid result.');
        }

        return ['ok' => true, 'tool' => $tool, 'data' => $result, 'correlation' => $correlationId];
    }

    /** @param array<string,mixed> $input */
    private function containsTenantOverride(array $input): bool
    {
        foreach ($input as $key => $value) {
            $normalised = strtolower(str_replace(['_', '-'], '', (string) $key));
            if ($normalised === 'companyid') {
                return true;
            }
            if (is_array($value) && $this->containsTenantOverride($value)) {
                return true;
            }
        }

        return false;
    }

    private function context(): ActiveCompanyContext
    {
        if (app()->bound(ActiveCompanyContext::class)) {
            return app(ActiveCompanyContext::class);
        }

        $context = $this->contexts->resolve(request());
        if (! $context instanceof ActiveCompanyContext) {
            throw new \RuntimeException('Active company context is required.', 409);
        }

        return $context;
    }

    private function recordFailure(
        string $tool,
        ActiveCompanyContext $context,
        ?string $conversationId,
        string $correlationId,
        Throwable $exception,
    ): void {
        try {
            $this->audit->record([
                'company_id' => $context->companyId,
                'user_id' => $context->userId,
                'agent_id' => 'titan-zero',
                'conversation_id' => $conversationId,
                'action' => 'titan.tool.failed',
                'entity_type' => 'titan_tool',
                'entity_id' => $tool,
                'reason' => 'Registered Titan tool execution failed.',
                'correlation_id' => $correlationId,
                'metadata' => ['exception' => $exception::class],
            ]);
        } catch (Throwable) {
            // Preserve the original safe failure result if audit persistence is unavailable.
        }
    }

    private function failure(string $code, string $message): array
    {
        return ['ok' => false, 'error' => ['code' => $code, 'message' => $message]];
    }
}
