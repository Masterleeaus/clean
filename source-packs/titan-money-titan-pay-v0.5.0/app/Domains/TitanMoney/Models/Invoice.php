<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use App\Domains\TitanMoney\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Invoice extends Model
{
    use BelongsToCompany, HasUlids;

    protected $table = 'titan_money_invoices';
    protected $fillable = [
        'company_id','branch_id','workspace_id','invoice_number','invoice_series','status','customer_id','contact_id',
        'property_id','unit_id','project_id','job_id','work_order_id','service_agreement_id','recurring_invoice_id',
        'source_type','source_id','source_version','source_idempotency_key','currency_code','issue_date','service_period_start',
        'service_period_end','due_date','payment_terms_code','subtotal_minor','discount_minor','tax_minor','total_minor',
        'amount_paid_minor','amount_credited_minor','balance_due_minor','tax_inclusive','customer_snapshot_json',
        'business_snapshot_json','billing_address_snapshot_json','service_address_snapshot_json','notes','customer_message',
        'internal_notes','issued_at','voided_at','cancelled_at','paid_at','document_path','document_hash','template_version',
        'version','created_by_user_id','created_by_agent_id','device_id','conversation_id','correlation_id','causation_id',
    ];

    protected function casts(): array
    {
        return [
            'status'=>InvoiceStatus::class,'issue_date'=>'date','service_period_start'=>'date','service_period_end'=>'date','due_date'=>'date',
            'subtotal_minor'=>'integer','discount_minor'=>'integer','tax_minor'=>'integer','total_minor'=>'integer','amount_paid_minor'=>'integer',
            'amount_credited_minor'=>'integer','balance_due_minor'=>'integer','tax_inclusive'=>'boolean','customer_snapshot_json'=>'array',
            'business_snapshot_json'=>'array','billing_address_snapshot_json'=>'array','service_address_snapshot_json'=>'array',
            'issued_at'=>'datetime','voided_at'=>'datetime','cancelled_at'=>'datetime','paid_at'=>'datetime','version'=>'integer',
        ];
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function lines(): HasMany { return $this->hasMany(InvoiceLine::class)->orderBy('display_order'); }
    public function reminders(): HasMany { return $this->hasMany(InvoiceReminder::class); }
    public function allocations(): HasMany { return $this->hasMany(PaymentAllocation::class); }
    public function followUps(): HasMany { return $this->hasMany(InvoiceFollowUp::class); }
    public function deliveries(): HasMany { return $this->hasMany(ChannelDelivery::class); }
    public function recurringInvoice(): BelongsTo { return $this->belongsTo(RecurringInvoice::class); }

    public function isMutable(): bool
    {
        return in_array($this->status, [InvoiceStatus::Draft, InvoiceStatus::PendingApproval, InvoiceStatus::Approved], true);
    }
}
