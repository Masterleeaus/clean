<?php

declare(strict_types=1);

namespace App\Titan\Tenancy;

use App\Models\CompanyMembership;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class CompanyContextResolver
{
    public function resolve(Request $request): ?ActiveCompanyContext
    {
        $user = $request->user();
        if ($user === null || ! is_numeric($user->getAuthIdentifier())) {
            return null;
        }

        $userId = (int) $user->getAuthIdentifier();
        $headerName = (string) config('titan.tenancy.header', 'X-Titan-Company');
        $sessionKey = (string) config('titan.tenancy.session_key', 'titan_company_id');

        $requested = $request->header($headerName);
        if ($requested === null && $request->hasSession()) {
            $requested = $request->session()->get($sessionKey);
        }
        if ($requested === null) {
            $requested = $user->getAttribute('active_company_id');
        }

        if (! is_numeric($requested) || (int) $requested < 1) {
            return null;
        }

        $companyId = (int) $requested;
        $membership = CompanyMembership::query()
            ->where('user_id', $userId)
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->whereHas('company', static fn ($query) => $query->where('status', 'active'))
            ->first();

        if ($membership === null) {
            throw new AccessDeniedHttpException('You do not belong to the selected Titan company.');
        }

        return new ActiveCompanyContext(
            companyId: $companyId,
            userId: $userId,
            membershipId: (int) $membership->getKey(),
            roleKey: (string) ($membership->role_key ?: 'member'),
            isOwner: (bool) $membership->is_owner,
        );
    }
}
