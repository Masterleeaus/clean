<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\Customer;
use App\Domains\TitanZero\WorkCore\DTO\CompletedBillableJob;

final class WorkCoreCustomerProjectionService
{
    public function __construct(private readonly CurrentCompany $currentCompany) {}

    public function project(CompletedBillableJob $job): Customer
    {
        $company = $this->currentCompany->require();
        $source = $job->customer;
        $premises = $job->premises;
        $externalId = (string) ($source['public_id'] ?? '');

        $customer = Customer::query()->firstOrNew([
            'company_id' => $company->id,
            'external_type' => 'workcore',
            'external_id' => $externalId,
        ]);

        $metadata = array_merge($customer->metadata_json ?? [], [
            'workcore_customer_public_id' => $externalId,
            'customer_app_user_id' => $source['portal_user_id'] ?? null,
            'communication_preferences' => array_merge(
                ['customer_app' => true, 'sms' => true, 'email' => true, 'voice' => false],
                (array) (($customer->metadata_json ?? [])['communication_preferences'] ?? []),
            ),
        ]);

        $serviceAddress = $premises ? array_filter([
            'name' => $premises['name'] ?? null,
            'address_line_1' => $premises['address_line_1'] ?? null,
            'address_line_2' => $premises['address_line_2'] ?? null,
            'suburb' => $premises['suburb'] ?? null,
            'state' => $premises['state'] ?? null,
            'postcode' => $premises['postcode'] ?? null,
            'country_code' => $premises['country_code'] ?? 'AU',
        ], static fn (mixed $value): bool => $value !== null && $value !== '') : null;

        $customer->fill([
            'name' => (string) ($source['name'] ?? 'WorkCore customer'),
            'email' => $source['email'] ?? null,
            'phone' => $source['phone'] ?? null,
            'billing_address_json' => $source['address'] ? ['formatted' => $source['address']] : $customer->billing_address_json,
            'service_address_json' => $serviceAddress ?: $customer->service_address_json,
            'metadata_json' => $metadata,
            'is_active' => true,
        ]);
        $customer->save();

        return $customer;
    }
}
