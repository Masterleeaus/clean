<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Http\Controllers;

use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\AgentApprovalRequest;
use App\Domains\TitanMoney\Models\AgentRun;
use App\Domains\TitanMoney\Models\AutomationPolicy;
use App\Domains\TitanMoney\Models\ChannelDelivery;
use App\Domains\TitanMoney\Models\InvoiceFollowUp;
use App\Domains\TitanMoney\Services\AutomationPolicyService;
use App\Domains\TitanMoney\Services\TitanMoneyAutomationCoordinator;
use App\Domains\TitanMoney\Support\DecimalParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

final class AutomationController extends Controller
{
    public function index(CurrentCompany $currentCompany, AutomationPolicyService $policies): View
    {
        $company = $currentCompany->require();
        $policies->ensureDefaults($company);

        return view('titanmoney::automation.index', [
            'policies'=>AutomationPolicy::query()->orderBy('agent_key')->get(),
            'runs'=>AgentRun::query()->latest('started_at')->limit(20)->get(),
            'approvals'=>AgentApprovalRequest::query()->where('status','pending')->latest()->limit(20)->get(),
            'followUps'=>InvoiceFollowUp::query()->with('invoice.customer')->latest()->limit(20)->get(),
            'deliveries'=>ChannelDelivery::query()->latest()->limit(20)->get(),
            'company'=>$company,
        ]);
    }

    public function update(Request $request, AutomationPolicy $policy): RedirectResponse
    {
        $data = $request->validate([
            'enabled'=>'nullable|boolean',
            'autonomy_level'=>'required|in:observe,draft,bounded,full',
            'max_auto_issue_amount'=>'nullable|decimal:0,2|min:0|max:10000000',
            'auto_issue'=>'nullable|boolean',
            'send_on_issue'=>'nullable|boolean',
            'payment_terms_days'=>'nullable|integer|min:0|max:365',
            'channels'=>'nullable|array',
            'channels.*'=>'in:customer_app,email,sms,whatsapp,voice,internal',
            'auto_match_exact_references'=>'nullable|boolean',
        ]);

        $settings = $policy->settings_json ?? [];
        if ($policy->agent_key === 'invoice_generation') {
            $settings['auto_issue'] = (bool) ($data['auto_issue'] ?? false);
            $settings['send_on_issue'] = (bool) ($data['send_on_issue'] ?? false);
            $settings['payment_terms_days'] = (int) ($data['payment_terms_days'] ?? 14);
        } elseif ($policy->agent_key === 'payment_reconciliation') {
            $settings['auto_match_exact_references'] = (bool) ($data['auto_match_exact_references'] ?? false);
        }

        $policy->update([
            'enabled'=>(bool)($data['enabled'] ?? false),
            'autonomy_level'=>$data['autonomy_level'],
            'max_auto_issue_minor'=>DecimalParser::minorUnits((string)($data['max_auto_issue_amount'] ?? 0)),
            'channels_json'=>array_values($data['channels'] ?? $policy->channels_json ?? ['email']),
            'settings_json'=>$settings,
            'updated_by_user_id'=>$request->user()?->id,
        ]);

        return back()->with('success', 'Automation policy updated.');
    }


    public function updateIntegration(Request $request, CurrentCompany $currentCompany): RedirectResponse
    {
        $company = $currentCompany->require();
        $data = $request->validate([
            'workcore_company_id' => ['required','integer','min:1'],
            'workcore_prices_tax_inclusive' => ['nullable','boolean'],
        ]);
        $settings = $company->settings_json ?? [];
        $settings['workcore_company_id'] = (int) $data['workcore_company_id'];
        $settings['workcore_prices_tax_inclusive'] = (bool) ($data['workcore_prices_tax_inclusive'] ?? false);
        $company->update(['settings_json'=>$settings]);

        return back()->with('success', 'WorkCore company mapping updated.');
    }

    public function run(Request $request, TitanMoneyAutomationCoordinator $coordinator): RedirectResponse
    {
        $data = $request->validate(['agent_key'=>'nullable|in:invoice_generation,receivables_follow_up,payment_reconciliation','dry_run'=>'nullable|boolean']);
        $runs = $coordinator->runEnabled(
            $data['agent_key'] ?? null,
            null,
            (bool)($data['dry_run'] ?? false),
            'manual',
        );

        return back()->with('success', 'Completed '.count($runs).' Titan Money agent run(s).');
    }
}
