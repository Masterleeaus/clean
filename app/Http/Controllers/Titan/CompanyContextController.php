<?php

declare(strict_types=1);

namespace App\Http\Controllers\Titan;

use App\Domains\WorkCore\System\Contracts\OperationContextContract;
use App\Http\Controllers\Controller;
use App\Models\CompanyMembership;
use App\Titan\Audit\AuditRecorder;
use App\Titan\Tenancy\ActiveCompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class CompanyContextController extends Controller
{
    public function show(ActiveCompanyContext $context): JsonResponse
    {
        $membership = CompanyMembership::query()
            ->with('company:id,name,slug,status,timezone,currency,country_code')
            ->whereKey($context->membershipId)
            ->where('company_id', $context->companyId)
            ->where('user_id', $context->userId)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'context' => $context->jsonSerialize(),
                'company' => $membership->company,
            ],
        ]);
    }

    public function switch(
        Request $request,
        ActiveCompanyContext $current,
        AuditRecorder $audit,
        OperationContextContract $operation,
    ): JsonResponse {
        $validated = $request->validate([
            'target_company_id' => ['required', 'integer', 'min:1'],
        ]);

        $targetCompanyId = (int) $validated['target_company_id'];
        $membership = CompanyMembership::query()
            ->with('company:id,name,slug,status,timezone,currency,country_code')
            ->where('company_id', $targetCompanyId)
            ->where('user_id', $current->userId)
            ->where('status', 'active')
            ->whereHas('company', static fn ($query) => $query->where('status', 'active'))
            ->first();

        if ($membership === null) {
            throw new AccessDeniedHttpException('You do not belong to the selected Titan company.');
        }

        DB::transaction(function () use ($request, $targetCompanyId, $current, $membership, $audit, $operation): void {
            $request->user()?->forceFill(['active_company_id' => $targetCompanyId])->save();

            if ($request->hasSession()) {
                $request->session()->put(
                    (string) config('titan.tenancy.session_key', 'titan_company_id'),
                    $targetCompanyId,
                );
            }

            $audit->record([
                'company_id' => $targetCompanyId,
                'user_id' => $current->userId,
                'action' => 'titan.company_context.switched',
                'entity_type' => 'company_membership',
                'entity_id' => (string) $membership->getKey(),
                'before_state' => ['company_id' => $current->companyId],
                'after_state' => ['company_id' => $targetCompanyId],
                'reason' => 'Authenticated user selected another active company membership.',
                'correlation_id' => $operation->hasContext() ? $operation->correlationId() : null,
            ]);
        });

        $context = new ActiveCompanyContext(
            companyId: $targetCompanyId,
            userId: $current->userId,
            membershipId: (int) $membership->getKey(),
            roleKey: (string) ($membership->role_key ?: 'member'),
            isOwner: (bool) $membership->is_owner,
        );

        return response()->json([
            'data' => [
                'context' => $context->jsonSerialize(),
                'company' => $membership->company,
            ],
            'meta' => [
                'effective_on_next_request' => true,
            ],
        ]);
    }
}
