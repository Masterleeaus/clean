<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class TaxProfile extends Model
{
    use BelongsToCompany, HasUlids;

    protected $table = 'titan_money_tax_profiles';
    protected $fillable = ['company_id','code','name','rate_basis_points','classification','is_inclusive','is_default','is_active'];
    protected function casts(): array { return ['rate_basis_points'=>'integer','is_inclusive'=>'boolean','is_default'=>'boolean','is_active'=>'boolean']; }
}
