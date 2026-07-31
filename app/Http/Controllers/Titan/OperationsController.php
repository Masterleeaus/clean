<?php

declare(strict_types=1);

namespace App\Http\Controllers\Titan;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyMembership;
use App\Titan\Permissions\PermissionResolver;
use App\Titan\Tenancy\ActiveCompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class OperationsController extends Controller
{
    public function __invoke(
        ActiveCompanyContext $context,
        PermissionResolver $permissions,
    ): View {
        $company = Company::query()->findOrFail($context->companyId);
        $memberships = CompanyMembership::query()
            ->with('company:id,name,slug,status')
            ->where('user_id', $context->userId)
            ->where('status', 'active')
            ->whereHas('company', static fn ($query) => $query->where('status', 'active'))
            ->orderBy('id')
            ->get();

        $mapsEnabled = Schema::hasTable('titan_extensions')
            && (bool) DB::table('titan_extensions')
                ->where('id', 'titan-maps-intelligence')
                ->where('enabled', true)
                ->where('compatible', true)
                ->whereIn('status', ['ready', 'enabled'])
                ->exists();

        $settings = is_array($company->settings) ? $company->settings : [];
        $ai = is_array($settings['ai'] ?? null) ? $settings['ai'] : [];
        $canConfigureAi = $context->isOwner
            || $permissions->allows($context->userId, $context->companyId, 'titan.ai.configure');

        return view('titan.operations', [
            'activeContext' => $context,
            'activeCompany' => $company,
            'memberships' => $memberships,
            'mapsEnabled' => $mapsEnabled,
            'canConfigureAi' => $canConfigureAi,
            'aiConfiguration' => [
                'provider' => (string) ($ai['provider'] ?? ''),
                'model' => (string) ($ai['model'] ?? ''),
                'configured' => trim((string) ($ai['credential_reference'] ?? '')) !== '',
            ],
        ]);
    }
}
