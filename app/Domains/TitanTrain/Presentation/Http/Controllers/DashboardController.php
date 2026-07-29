<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Presentation\Http\Controllers;

use App\Domains\TitanTrain\Application\CleanerFoundationInstaller;
use App\Domains\TitanTrain\Application\CleanerReadinessService;
use App\Domains\TitanTrain\Application\TitanTrainAccess;
use App\Domains\TitanTrain\Infrastructure\Models\Assignment;
use App\Domains\WorkCore\System\Contracts\TenantContextContract;
use App\Domains\WorkCore\System\Models\Company;
use App\Domains\WorkCore\System\Models\CompanyMember;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        TenantContextContract $tenant,
        TitanTrainAccess $access,
        CleanerFoundationInstaller $installer,
        CleanerReadinessService $readiness,
    ): View {
        $membership = $access->membership($request->user());
        $company = Company::withoutGlobalScopes()->findOrFail($tenant->companyId());
        $manage = $access->isManager($request->user());
        $program = $installer->find();
        $myReadiness = $readiness->forUser($request->user());
        $assignments = Assignment::query()->where('user_id', $request->user()->getKey())->with('program')->latest('id')->get();
        $cleaners = collect();
        if ($manage) {
            $cleaners = CompanyMember::withoutGlobalScopes()->where('company_id', $company->id)->where('status', 'active')->with('user')->limit(100)->get()
                ->map(fn (CompanyMember $member) => ['member' => $member, 'user' => $member->user, 'readiness' => $readiness->forUser($member->user)]);
        }

        return view('titan-train::dashboard', compact('company', 'membership', 'manage', 'program', 'myReadiness', 'assignments', 'cleaners'));
    }
}
