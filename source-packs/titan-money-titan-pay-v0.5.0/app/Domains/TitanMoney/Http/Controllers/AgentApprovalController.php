<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Http\Controllers;

use App\Domains\TitanMoney\Models\AgentApprovalRequest;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanMoney\Models\InvoiceFollowUp;
use App\Domains\TitanMoney\Services\ChannelDeliveryService;
use App\Domains\TitanMoney\Services\InvoiceDocumentService;
use App\Domains\TitanMoney\Services\InvoiceReminderService;
use App\Domains\TitanMoney\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use LogicException;

final class AgentApprovalController extends Controller
{
    public function approve(
        Request $request,
        AgentApprovalRequest $approval,
        InvoiceService $invoices,
        InvoiceDocumentService $documents,
        InvoiceReminderService $reminders,
        ChannelDeliveryService $deliveries,
    ): RedirectResponse {
        $data = $request->validate(['notes'=>'nullable|string|max:2000']);

        DB::transaction(function () use ($request, $approval, $invoices, $documents, $reminders, $deliveries, $data): void {
            $locked = AgentApprovalRequest::query()->whereKey($approval->id)->lockForUpdate()->firstOrFail();
            $this->assertPending($locked);

            if ($locked->action_key === 'issue_invoice') {
                $invoice = Invoice::query()->findOrFail($locked->resource_id);
                $invoice = $invoices->issue($invoice);
                $documents->snapshot($invoice);
                $reminders->schedule($invoice, config('titanmoney.reminder_offsets',[-7,0,7,14]));
            } elseif ($locked->action_key === 'send_invoice_follow_up') {
                $followUp = InvoiceFollowUp::query()->findOrFail($locked->resource_id);
                $template = (string)($followUp->metadata_json['template'] ?? 'invoice_reminder');
                $deliveries->queueFollowUp($followUp, $followUp->channels_json ?? ['email'], $template);
            } else {
                abort(422, 'Unsupported agent approval action.');
            }

            $locked->update([
                'status'=>'approved',
                'decided_at'=>now(),
                'decided_by_user_id'=>$request->user()?->id,
                'decision_notes'=>$data['notes'] ?? null,
            ]);
        });

        return back()->with('success', 'Agent action approved and executed.');
    }

    public function reject(Request $request, AgentApprovalRequest $approval): RedirectResponse
    {
        $data = $request->validate(['notes'=>'nullable|string|max:2000']);

        DB::transaction(function () use ($request, $approval, $data): void {
            $locked = AgentApprovalRequest::query()->whereKey($approval->id)->lockForUpdate()->firstOrFail();
            $this->assertPending($locked);

            $locked->update([
                'status'=>'rejected',
                'decided_at'=>now(),
                'decided_by_user_id'=>$request->user()?->id,
                'decision_notes'=>$data['notes'] ?? null,
            ]);

            if ($locked->action_key === 'send_invoice_follow_up') {
                InvoiceFollowUp::query()->whereKey($locked->resource_id)->update([
                    'status'=>'cancelled',
                    'cancelled_at'=>now(),
                ]);
            }
        });

        return back()->with('success', 'Agent action rejected.');
    }

    private function assertPending(AgentApprovalRequest $approval): void
    {
        if ($approval->status !== 'pending') {
            throw new LogicException('This agent approval has already been decided.');
        }
        if ($approval->expires_at?->isPast()) {
            throw new LogicException('This agent approval request has expired.');
        }
    }
}
