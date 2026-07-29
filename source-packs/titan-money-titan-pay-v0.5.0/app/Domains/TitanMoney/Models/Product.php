<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class Product extends Model
{
    use BelongsToCompany, HasUlids;

    protected $table = 'titan_money_products';
    protected $fillable = ['company_id','external_id','sku','name','description','unit','unit_price_minor','currency_code','tax_profile_id','stock_quantity','stock_alert_quantity','metadata_json','is_active'];
    protected function casts(): array { return ['unit_price_minor'=>'integer','stock_quantity'=>'decimal:4','stock_alert_quantity'=>'decimal:4','metadata_json'=>'array','is_active'=>'boolean']; }
}
