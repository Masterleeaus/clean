<?php

declare(strict_types=1);

namespace App\Http\Controllers\Titan;

use App\Domains\WorkCore\System\Contracts\OperationContextContract;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Titan\Audit\AuditRecorder;
use App\Titan\Permissions\PermissionResolver;
use App\Titan\Tenancy\ActiveCompanyContext;
use App\Titan\Vault\Vault;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class AiConfigurationController extends Controller
{
    public function __invoke(
        Request $request,
        ActiveCompanyContext $context,
        PermissionResolver $permissions,
        Vault $vault,
        AuditRecorder $audit,
        OperationContextContract $operation,
    ): RedirectResponse {
        if (! $context->isOwner && ! $permissions->allows($context->userId, $context->companyId, 'titan.ai.configure')) {
            abort(403, 'You are not permitted to configure Titan AI.');
        }

        $validated = $request->validate([
            'provider' => ['required', Rule::in(['openai', 'openrouter', 'gemini'])],
            'model' => ['required', 'string', 'max:160', 'regex:/^[A-Za-z0-9._:\/-]+$/'],
            'api_key' => ['nullable', 'string', 'max:4096'],
        ]);

        /** @var array{old_reference:string,new_reference:string}|null $result */
        $result = DB::transaction(function () use ($validated, $context, $vault, $audit, $operation): ?array {
            $company = Company::query()
                ->whereKey($context->companyId)
                ->lockForUpdate()
                ->firstOrFail();

            $settings = is_array($company->settings) ? $company->settings : [];
            $existing = is_array($settings['ai'] ?? null) ? $settings['ai'] : [];
            $oldReference = trim((string) ($existing['credential_reference'] ?? ''));
            $oldProvider = strtolower(trim((string) ($existing['provider'] ?? '')));
            $newProvider = (string) $validated['provider'];
            $secret = trim((string) ($validated['api_key'] ?? ''));

            if ($secret === '' && ($oldReference === '' || ($oldProvider !== '' && $oldProvider !== $newProvider))) {
                return null;
            }

            $newReference = $oldReference;
            if ($secret !== '') {
                $newReference = $vault->put(
                    companyId: $context->companyId,
                    provider: $newProvider,
                    key: 'api_key',
                    value: $secret,
                    userId: null,
                    metadata: ['purpose' => 'titan-zero-ai'],
                );
            }

            $settings['ai'] = [
                'provider' => $newProvider,
                'model' => (string) $validated['model'],
                'credential_reference' => $newReference,
                'updated_at' => now()->toIso8601String(),
                'updated_by_user_id' => $context->userId,
            ];

            $company->forceFill(['settings' => $settings])->save();

            $audit->record([
                'company_id' => $context->companyId,
                'user_id' => $context->userId,
                'action' => 'titan.ai.configuration.updated',
                'entity_type' => 'company',
                'entity_id' => (string) $context->companyId,
                'reason' => 'Authorised user updated the company AI provider configuration.',
                'correlation_id' => $operation->hasContext() ? $operation->correlationId() : null,
                'metadata' => [
                    'provider' => $newProvider,
                    'model' => (string) $validated['model'],
                    'credential_replaced' => $newReference !== $oldReference,
                ],
            ]);

            return [
                'old_reference' => $oldReference,
                'new_reference' => $newReference,
            ];
        });

        if ($result === null) {
            return back()
                ->withErrors(['api_key' => 'Add a new API key when configuring Titan AI for the first time or changing providers.'])
                ->withInput($request->only(['provider', 'model']));
        }

        if ($result['old_reference'] !== '' && $result['new_reference'] !== $result['old_reference']) {
            $vault->forget($context->companyId, $result['old_reference']);
        }

        return redirect()->route('titan.operations')->with('success', 'Titan AI configuration saved securely.');
    }
}
