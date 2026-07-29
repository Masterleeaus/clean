<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Console\Commands;

use App\Domains\Company\Models\Company;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Enums\InvoiceStatus;
use App\Domains\TitanMoney\Enums\PaymentStatus;
use App\Domains\TitanMoney\Models\Customer;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanMoney\Models\Payment;
use App\Domains\TitanMoney\Models\Product;
use App\Domains\TitanMoney\Models\RecurringInvoice;
use App\Domains\TitanMoney\Support\DecimalParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

final class ImportInvoixProCommand extends Command
{
    protected $signature = 'titan-money:import-invoixpro
        {--connection=invoixpro : Configured legacy database connection}
        {--company= : Target Titan company ULID}
        {--legacy-user= : Restrict import to an InvoixPro user ID}
        {--commit : Write changes; otherwise perform a dry run}';

    protected $description = 'Safely import supported InvoixPro records into Titan Money and Titan Pay.';

    public function handle(CurrentCompany $context): int
    {
        $connection = (string) $this->option('connection');
        $companyId = (string) $this->option('company');
        $commit = (bool) $this->option('commit');
        $legacyUser = $this->option('legacy-user');

        if ($companyId === '') {
            $this->error('--company is required.');
            return self::INVALID;
        }

        $company = Company::query()->find($companyId);
        if (! $company) {
            $this->error('Target company was not found.');
            return self::INVALID;
        }
        $context->set($company);

        try {
            DB::connection($connection)->getPdo();
        } catch (Throwable $e) {
            $this->error('Legacy connection failed: '.$e->getMessage());
            return self::FAILURE;
        }

        $counts = $this->inspect($connection, $legacyUser);
        $this->table(['Record type','Found'], collect($counts)->map(fn($v,$k)=>[$k,$v])->values()->all());

        if (! $commit) {
            $this->warn('Dry run only. Re-run with --commit after reviewing the counts and backup.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($connection,$company,$legacyUser): void {
            $this->importCustomers($connection,$company->id,$legacyUser);
            $this->importProducts($connection,$company->id,$legacyUser);
            $this->importRecurring($connection,$company->id,$legacyUser);
            $this->importInvoices($connection,$company->id,$legacyUser);
            $this->importPaymentClaims($connection,$company->id,$legacyUser);
        });

        $this->info('Import completed. Legacy payment logs were imported as claimed—not verified—and require reconciliation.');
        return self::SUCCESS;
    }

    private function inspect(string $connection, mixed $legacyUser): array
    {
        $result=[];
        foreach (['clients','products','recurring_invoices','invoices','invoice_items','invoice_payment_logs'] as $table) {
            $schema=Schema::connection($connection);
            if (! $schema->hasTable($table)) { $result[$table]=0; continue; }
            $query=DB::connection($connection)->table($table);
            if ($legacyUser && $schema->hasColumn($table,'user_id')) $query->where('user_id',$legacyUser);
            $result[$table]=$query->count();
        }
        return $result;
    }

    private function legacyQuery(string $connection,string $table,mixed $legacyUser)
    {
        $query=DB::connection($connection)->table($table)->orderBy('id');
        if ($legacyUser && Schema::connection($connection)->hasColumn($table,'user_id')) $query->where('user_id',$legacyUser);
        return $query;
    }

    private function importCustomers(string $connection,string $companyId,mixed $legacyUser): void
    {
        if (! Schema::connection($connection)->hasTable('clients')) return;
        $this->legacyQuery($connection,'clients',$legacyUser)->chunkById(200,function($rows) use($companyId): void {
            foreach($rows as $row) Customer::query()->updateOrCreate(
                ['company_id'=>$companyId,'external_type'=>'invoixpro_client','external_id'=>(string)$row->id],
                ['name'=>$row->name,'email'=>$row->email,'phone'=>$row->phone,'business_name'=>$row->company,'billing_address_json'=>$row->address?['formatted'=>$row->address]:null,'metadata_json'=>['legacy_user_id'=>$row->user_id],'is_active'=>true]
            );
        });
    }

    private function importProducts(string $connection,string $companyId,mixed $legacyUser): void
    {
        if (! Schema::connection($connection)->hasTable('products')) return;
        $this->legacyQuery($connection,'products',$legacyUser)->chunkById(200,function($rows) use($companyId): void {
            foreach($rows as $row) Product::query()->updateOrCreate(
                ['company_id'=>$companyId,'external_id'=>'invoixpro:'.$row->id],
                ['name'=>$row->name,'unit_price_minor'=>$this->minor($row->price),'currency_code'=>'AUD','stock_quantity'=>$row->stock ?? 0,'stock_alert_quantity'=>$row->stock_alert ?? 0,'metadata_json'=>['legacy_user_id'=>$row->user_id],'is_active'=>true]
            );
        });
    }

    private function importRecurring(string $connection,string $companyId,mixed $legacyUser): void
    {
        if (! Schema::connection($connection)->hasTable('recurring_invoices')) return;
        $this->legacyQuery($connection,'recurring_invoices',$legacyUser)->chunkById(100,function($rows) use($connection,$companyId): void {
            foreach($rows as $row){
                $customer=Customer::query()->where('external_type','invoixpro_client')->where('external_id',(string)$row->client_id)->first();
                if(! $customer) continue;
                $recurring=RecurringInvoice::query()->updateOrCreate(
                    ['company_id'=>$companyId,'source_type'=>'invoixpro_recurring','source_id'=>(string)$row->id],
                    ['customer_id'=>$customer->id,'frequency'=>$this->frequency((string)$row->frequency),'interval_count'=>1,'billing_timing'=>'advance','start_date'=>$row->start_date,'next_generation_date'=>$row->next_generation_date,'last_generated_at'=>$row->last_generated_at,'stop_after'=>$row->stop_after,'occurrences'=>$row->occurrences ?? 0,'status'=>$row->status ?? 'active','currency_code'=>strtoupper($row->currency_code ?: 'AUD'),'tax_inclusive'=>false,'payment_terms_code'=>'NET14','notes'=>$row->notes,'metadata_json'=>['legacy_subtotal'=>$row->subtotal,'legacy_tax'=>$row->tax,'legacy_total'=>$row->total]]
                );
                if(Schema::connection($connection)->hasTable('recurring_invoice_items')){
                    $items=DB::connection($connection)->table('recurring_invoice_items')->where('recurring_invoice_id',$row->id)->orderBy('id')->get();
                    $recurring->lines()->delete();
                    foreach($items as $i=>$item) $recurring->lines()->create(['line_type'=>'service','description'=>$item->item_name,'quantity'=>$item->quantity,'unit_price_minor'=>$this->minor($item->unit_price),'discount_minor'=>0,'tax_rate_basis_points'=>DecimalParser::basisPoints((string)($row->tax_rate??0)),'metadata_json'=>['legacy_id'=>$item->id],'display_order'=>$i]);
                }
            }
        });
    }

    private function importInvoices(string $connection,string $companyId,mixed $legacyUser): void
    {
        if (! Schema::connection($connection)->hasTable('invoices')) return;
        $this->legacyQuery($connection,'invoices',$legacyUser)->chunkById(100,function($rows) use($connection,$companyId): void {
            foreach($rows as $row){
                $customer=Customer::query()->where('external_type','invoixpro_client')->where('external_id',(string)$row->client_id)->first();
                if(! $customer) continue;
                $total=$this->minor($row->total); $legacyPaid=(string)$row->status==='paid';
                $number=(string)$row->invoice_number;
                $collision=Invoice::query()->where('invoice_number',$number)->where('source_idempotency_key','!=','invoixpro:invoice:'.$row->id)->exists();
                if($collision) $number.='-IP-'.$row->id;
                $invoice=Invoice::query()->firstOrCreate(
                    ['company_id'=>$companyId,'source_idempotency_key'=>'invoixpro:invoice:'.$row->id],
                    ['invoice_number'=>$number,'invoice_series'=>'LEGACY','status'=>(string)$row->status==='overdue'?InvoiceStatus::Overdue:InvoiceStatus::Issued,'customer_id'=>$customer->id,'source_type'=>'invoixpro_invoice','source_id'=>(string)$row->id,'currency_code'=>strtoupper($row->currency_code??'AUD'),'issue_date'=>$row->invoice_date,'due_date'=>$row->due_date,'subtotal_minor'=>$this->minor($row->subtotal),'discount_minor'=>$this->minor($row->discount_amount??0),'tax_minor'=>$this->minor($row->tax),'total_minor'=>$total,'amount_paid_minor'=>0,'amount_credited_minor'=>0,'balance_due_minor'=>$total,'tax_inclusive'=>false,'customer_snapshot_json'=>$customer->only(['id','name','email','phone','business_name','abn']),'billing_address_snapshot_json'=>$customer->billing_address_json,'notes'=>$row->notes,'issued_at'=>$row->created_at ?? now(),'paid_at'=>null,'version'=>1]
                );
                if($invoice->wasRecentlyCreated && Schema::connection($connection)->hasTable('invoice_items')){
                    $items=DB::connection($connection)->table('invoice_items')->where('invoice_id',$row->id)->orderBy('id')->get();
                    foreach($items as $i=>$item) $invoice->lines()->create(['line_type'=>'service','description'=>$item->item_name,'quantity'=>$item->quantity,'unit_price_minor'=>$this->minor($item->unit_price),'discount_minor'=>0,'tax_rate_basis_points'=>0,'tax_minor'=>0,'line_total_minor'=>$this->minor($item->total_price),'metadata_json'=>['legacy_id'=>$item->id],'display_order'=>$i]);
                }
                if($legacyPaid){
                    $hasLegacyLog=Schema::connection($connection)->hasTable('invoice_payment_logs')
                        && DB::connection($connection)->table('invoice_payment_logs')->where('invoice_id',$row->id)->exists();
                    if(! $hasLegacyLog){
                        Payment::query()->firstOrCreate(
                            ['company_id'=>$companyId,'gateway_provider'=>'invoixpro_legacy','gateway_transaction_id'=>'legacy-invoice-status-'.$row->id],
                            ['payer_type'=>'legacy_claim','payer_id'=>(string)$row->id,'payment_method'=>'legacy_status','currency_code'=>$invoice->currency_code,'amount_minor'=>$total,'unallocated_minor'=>$total,'received_at'=>$row->paid_at??$row->updated_at??now(),'reference'=>$number,'status'=>PaymentStatus::Claimed,'metadata_json'=>['legacy_status'=>'paid','requires_reconciliation'=>true,'source'=>'invoice_status_without_payment_log']]
                        );
                    }
                }
            }
        });
    }

    private function importPaymentClaims(string $connection,string $companyId,mixed $legacyUser): void
    {
        if (! Schema::connection($connection)->hasTable('invoice_payment_logs')) return;
        DB::connection($connection)->table('invoice_payment_logs')->orderBy('id')->chunkById(200,function($rows) use($companyId): void {
            foreach($rows as $row){
                $invoice=Invoice::query()->where('source_idempotency_key','invoixpro:invoice:'.$row->invoice_id)->first();
                if(! $invoice) continue;
                Payment::query()->firstOrCreate(
                    ['company_id'=>$companyId,'gateway_provider'=>'invoixpro_legacy','gateway_transaction_id'=>'legacy-log-'.$row->id],
                    ['payer_type'=>'legacy_claim','payer_id'=>(string)$row->id,'payment_method'=>$row->gateway_type ?: 'legacy','currency_code'=>$invoice->currency_code,'amount_minor'=>$this->minor($row->amount),'unallocated_minor'=>$this->minor($row->amount),'received_at'=>$row->created_at ?? now(),'reference'=>$row->reference,'status'=>PaymentStatus::Claimed,'metadata_json'=>['legacy_status'=>$row->status,'legacy_notes'=>$row->notes,'legacy_proof_path'=>$row->proof_file,'requires_reconciliation'=>true]]
                );
            }
        });
    }

    private function minor(mixed $value): int { return DecimalParser::minorUnits((string)$value); }
    private function frequency(string $frequency): string
    {
        return match(strtolower($frequency)){
            'biweekly','fortnightly'=>'fortnightly','quarterly'=>'quarterly','semiannual','semi-annually'=>'six_monthly','yearly','annual'=>'annual',default=>strtolower($frequency)
        };
    }
}
