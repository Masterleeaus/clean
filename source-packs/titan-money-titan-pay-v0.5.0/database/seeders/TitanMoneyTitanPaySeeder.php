<?php

namespace Database\Seeders;

use App\Domains\Company\Models\Company;
use App\Domains\Company\Models\CompanyMembership;
use App\Domains\TitanMoney\Models\TaxProfile;
use App\Domains\TitanPay\Models\GatewayConnection;
use App\Models\User;
use Illuminate\Database\Seeder;

final class TitanMoneyTitanPaySeeder extends Seeder
{
    public function run(): void
    {
        User::query()->each(function (User $user): void {
            $membership = CompanyMembership::query()->where('user_id', $user->id)->first();
            if (! $membership) {
                $company = Company::query()->create([
                    'name' => $user->name."'s Company",
                    'legal_name' => $user->name,
                    'email' => $user->email,
                    'currency_code' => 'AUD',
                    'timezone' => 'Australia/Sydney',
                    'settings_json' => ['invoice_prefix' => 'INV'],
                ]);
                $membership = CompanyMembership::query()->create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'role' => 'owner',
                    'permissions_json' => ['*'],
                    'is_active' => true,
                ]);
                $user->forceFill(['active_company_id' => $company->id])->saveQuietly();
            }

            $companyId = $membership->company_id;
            TaxProfile::query()->updateOrCreate(
                ['company_id'=>$companyId,'code'=>'GST10'],
                ['name'=>'Australian GST 10%','rate_basis_points'=>1000,'classification'=>'taxable','is_inclusive'=>false,'is_default'=>true,'is_active'=>true]
            );
            TaxProfile::query()->updateOrCreate(
                ['company_id'=>$companyId,'code'=>'GSTFREE'],
                ['name'=>'GST-free','rate_basis_points'=>0,'classification'=>'gst_free','is_inclusive'=>false,'is_default'=>false,'is_active'=>true]
            );

            foreach ([
                ['cash','Cash'], ['payid','PayID'], ['bank_transfer','Bank transfer'],
            ] as [$provider,$name]) {
                GatewayConnection::query()->withoutGlobalScope('company')->updateOrCreate(
                    ['company_id'=>$companyId,'provider'=>$provider,'merchant_account_reference'=>null],
                    ['display_name'=>$name,'environment'=>'live','currency_code'=>'AUD','is_active'=>true,'configuration_json'=>[]]
                );
            }
        });
    }
}
