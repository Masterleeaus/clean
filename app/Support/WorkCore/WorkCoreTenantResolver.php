<?php

declare(strict_types=1);

namespace App\Support\WorkCore;

use App\Domains\WorkCore\System\Contracts\TenantResolverContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class WorkCoreTenantResolver implements TenantResolverContract
{
    public function resolve(Request $request): ?array
    {
        $user = $request->user();
        if (! $user instanceof Authenticatable) {
            return null;
        }

        $candidate = $request->header((string) config('workcore.tenancy.header', 'X-Titan-Company'));

        if ($candidate === null && $request->hasSession()) {
            $candidate = $request->session()->get((string) config('workcore.tenancy.session_key', 'titan_company_id'));
        }

        $activeCompanyColumn = (string) config('workcore.identity.active_company_column', 'active_company_id');
        if ($candidate === null && $activeCompanyColumn !== '' && method_exists($user, 'getAttribute')) {
            $candidate = $user->getAttribute($activeCompanyColumn);
        }

        if ($candidate === null && (bool) config('workcore.tenancy.auto_select_single_membership', true)) {
            $companyIds = DB::table('tz_company_memberships')
                ->where('user_id', $user->getAuthIdentifier())
                ->where('status', 'active')
                ->limit(2)
                ->pluck('company_id');

            if ($companyIds->count() === 1) {
                $candidate = $companyIds->first();
            }
        }

        if (! is_numeric($candidate)) {
            return null;
        }

        $companyId = (int) $candidate;
        $member = DB::table('tz_company_memberships')
            ->where('company_id', $companyId)
            ->where('user_id', $user->getAuthIdentifier())
            ->where('status', 'active')
            ->exists();

        if (! $member) {
            return null;
        }

        $this->persistActiveCompanyWhenSupported($user, $activeCompanyColumn, $companyId);

        if ($request->hasSession()) {
            $request->session()->put((string) config('workcore.tenancy.session_key', 'titan_company_id'), $companyId);
        }

        return [
            'company_id' => $companyId,
            'user_id' => (int) $user->getAuthIdentifier(),
        ];
    }

    private function persistActiveCompanyWhenSupported(Authenticatable $user, string $column, int $companyId): void
    {
        if ($column === '' || ! (bool) config('workcore.tenancy.persist_active_company', false)) {
            return;
        }

        if (! method_exists($user, 'getTable') || ! method_exists($user, 'forceFill') || ! method_exists($user, 'saveQuietly')) {
            return;
        }

        $table = (string) $user->getTable();
        if ($table === '' || ! Schema::hasColumn($table, $column)) {
            return;
        }

        if ((int) $user->getAttribute($column) === $companyId) {
            return;
        }

        $user->forceFill([$column => $companyId])->saveQuietly();
    }
}
