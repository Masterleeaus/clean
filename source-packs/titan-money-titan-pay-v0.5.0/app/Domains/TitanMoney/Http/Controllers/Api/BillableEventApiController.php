<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Http\Controllers\Api;

use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Services\BillableEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

final class BillableEventApiController extends Controller
{
    public function store(Request $request, BillableEventService $events, CurrentCompany $currentCompany): JsonResponse
    {
        $companyId = $currentCompany->require()->id;
        $data = $request->validate([
            'source_type'=>'required|string|max:100',
            'source_id'=>'required|string|max:191',
            'source_version'=>'nullable|string|max:100',
            'idempotency_key'=>'nullable|string|max:191',
            'customer_id'=>['required','string',Rule::exists('titan_money_customers','id')->where(fn($query)=>$query->where('company_id',$companyId))],
            'occurred_at'=>'nullable|date',
            'available_at'=>'nullable|date',
            'payload'=>'required|array',
            'payload.currency_code'=>'nullable|string|size:3',
            'payload.tax_inclusive'=>'nullable|boolean',
            'payload.issue_date'=>'nullable|date',
            'payload.due_date'=>'nullable|date',
            'payload.property_id'=>'nullable|string|max:191',
            'payload.job_id'=>'nullable|string|max:191',
            'payload.work_order_id'=>'nullable|string|max:191',
            'payload.service_agreement_id'=>'nullable|string|max:191',
            'payload.notes'=>'nullable|string|max:5000',
            'payload.customer_message'=>'nullable|string|max:5000',
            'payload.lines'=>'required|array|min:1|max:500',
            'payload.lines.*.description'=>'required|string|max:1000',
            'payload.lines.*.quantity'=>'required|numeric|min:0.0001',
            'payload.lines.*.unit_price_minor'=>'required|integer|min:0',
            'payload.lines.*.discount_minor'=>'nullable|integer|min:0',
            'payload.lines.*.tax_rate_basis_points'=>'nullable|integer|min:0|max:10000',
            'payload.lines.*.product_id'=>['nullable','string',Rule::exists('titan_money_products','id')->where(fn($query)=>$query->where('company_id',$companyId))],
        ]);

        $event = $events->ingest($data);
        return response()->json(['data'=>$event,'accepted'=>true], $event->wasRecentlyCreated ? 202 : 200);
    }
}
