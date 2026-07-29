<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RecurringInvoiceLine extends Model
{
    use HasUlids;
    protected $table = 'titan_money_recurring_invoice_lines';
    protected $fillable = ['recurring_invoice_id','line_type','description','quantity','unit_price_minor','discount_minor','tax_code','tax_rate_basis_points','product_id','metadata_json','display_order'];
    protected function casts(): array { return ['quantity'=>'decimal:4','unit_price_minor'=>'integer','discount_minor'=>'integer','tax_rate_basis_points'=>'integer','metadata_json'=>'array','display_order'=>'integer']; }
    public function recurringInvoice(): BelongsTo { return $this->belongsTo(RecurringInvoice::class); }
}
