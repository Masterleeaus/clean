<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class RecurringInvoice extends Model
{
    use BelongsToCompany, HasUlids;

    protected $table = 'titan_money_recurring_invoices';
    protected $fillable = ['company_id','customer_id','source_type','source_id','service_agreement_id','frequency','interval_count','billing_timing','start_date','end_date','next_generation_date','last_generated_at','stop_after','occurrences','status','currency_code','tax_inclusive','payment_terms_code','notes','metadata_json','created_by_user_id'];
    protected function casts(): array { return ['interval_count'=>'integer','start_date'=>'date','end_date'=>'date','next_generation_date'=>'date','last_generated_at'=>'datetime','stop_after'=>'integer','occurrences'=>'integer','tax_inclusive'=>'boolean','metadata_json'=>'array']; }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function lines(): HasMany { return $this->hasMany(RecurringInvoiceLine::class)->orderBy('display_order'); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
}
