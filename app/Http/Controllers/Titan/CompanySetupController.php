<?php

declare(strict_types=1);

namespace App\Http\Controllers\Titan;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\CompanyRole;
use App\Titan\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CompanySetupController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $activeCompanyId = (int) ($user?->active_company_id ?? 0);
        if ($activeCompanyId > 0) {
            $hasActiveMembership = CompanyMembership::query()
                ->where('user_id', $user->id)
                ->where('company_id', $activeCompanyId)
                ->where('status', 'active')
                ->whereHas('company', static fn ($query) => $query->where('status', 'active'))
                ->exists();

            if ($hasActiveMembership) {
                return redirect()->route('titan.operations');
            }

            $user->forceFill(['active_company_id' => null])->save();
            $request->session()->forget((string) config('titan.tenancy.session_key', 'titan_company_id'));
        }

        $memberships = CompanyMembership::query()
            ->with('company:id,name,slug,status')
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->whereHas('company', static fn ($query) => $query->where('status', 'active'))
            ->get();

        return view('titan.company-setup', compact('memberships'));
    }

    public function store(Request $request, AuditRecorder $audit): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,create'],
            'target_company_id' => ['nullable', 'integer', 'min:1', 'required_if:action,activate'],
            'name' => ['nullable', 'string', 'max:255', 'required_if:action,create'],
            'timezone' => ['nullable', 'timezone'],
            'currency' => ['nullable', 'string', 'size:3'],
            'country_code' => ['nullable', 'string', 'size:2'],
        ]);

        $user = $request->user();
        $companyId = DB::transaction(function () use ($validated, $user, $audit): int {
            if ($validated['action'] === 'activate') {
                $membership = CompanyMembership::query()
                    ->where('user_id', $user->id)
                    ->where('company_id', (int) $validated['target_company_id'])
                    ->where('status', 'active')
                    ->whereHas('company', static fn ($query) => $query->where('status', 'active'))
                    ->firstOrFail();
                $companyId = (int) $membership->company_id;
            } else {
                $company = Company::create([
                    'public_id' => (string) Str::uuid(),
                    'name' => trim((string) $validated['name']),
                    'slug' => Str::slug((string) $validated['name']) . '-' . Str::lower(Str::random(8)),
                    'status' => 'active',
                    'timezone' => (string) ($validated['timezone'] ?? config('app.timezone', 'Australia/Melbourne')),
                    'currency' => strtoupper((string) ($validated['currency'] ?? 'AUD')),
                    'country_code' => strtoupper((string) ($validated['country_code'] ?? 'AU')),
                    'created_by_user_id' => $user->id,
                ]);
                $role = CompanyRole::create([
                    'company_id' => $company->id,
                    'name' => 'Owner',
                    'key' => 'owner',
                    'description' => 'Full company ownership and administrative access.',
                    'is_system' => true,
                ]);
                CompanyMembership::create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                    'role_key' => 'owner',
                    'status' => 'active',
                    'is_owner' => true,
                    'joined_at' => now(),
                ]);
                $companyId = (int) $company->id;
            }

            $user->forceFill(['active_company_id' => $companyId])->save();
            $audit->record([
                'company_id' => $companyId,
                'user_id' => $user->id,
                'action' => 'titan.company_context.initialised',
                'entity_type' => 'company',
                'entity_id' => (string) $companyId,
                'reason' => 'User selected or created an authorised Titan company.',
            ]);

            return $companyId;
        });

        $request->session()->put((string) config('titan.tenancy.session_key', 'titan_company_id'), $companyId);

        return redirect()->route('titan.operations')->with('success', 'Titan company context is active.');
    }
}
