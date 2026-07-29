<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class InvoiceLine extends Model
{
    use HasUlids;

    protected $table = 'titan_money_invoice_lines';
    protected $fillable = ['invoice_id','line_type','description','quantity','unit_price_minor','discount_minor','tax_code','tax_rate_basis_points','tax_minor','line_total_minor','product_id','service_id','job_task_id','inventory_movement_id','service_date','metadata_json','display_order'];
    protected function casts(): array { return ['quantity'=>'decimal:4','unit_price_minor'=>'integer','discount_minor'=>'integer','tax_rate_basis_points'=>'integer','tax_minor'=>'integer','line_total_minor'=>'integer','service_date'=>'date','metadata_json'=>'array','display_order'=>'integer']; }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
