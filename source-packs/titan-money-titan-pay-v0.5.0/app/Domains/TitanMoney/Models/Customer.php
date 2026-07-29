<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Customer extends Model
{
    use BelongsToCompany, HasUlids;

    protected $table = 'titan_money_customers';
    protected $fillable = ['company_id','external_type','external_id','name','email','phone','business_name','abn','billing_address_json','service_address_json','metadata_json','is_active'];
    protected function casts(): array { return ['billing_address_json'=>'array','service_address_json'=>'array','metadata_json'=>'array','is_active'=>'boolean']; }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
}
