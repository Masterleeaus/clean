<?php

declare(strict_types=1);

namespace App\Domains\Company\Support;

use App\Domains\Company\Models\Company;
use App\Domains\Company\Models\CompanyMembership;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

final class CurrentCompany
{
    private ?Company $company = null;

    public function set(Company $company): void
    {
        $this->company = $company;
    }

    public function get(): ?Company
    {
        return $this->company;
    }

    public function id(): ?string
    {
        return $this->company?->getKey();
    }

    public function require(): Company
    {
        if (! $this->company) {
            throw new AuthorizationException('A valid company context is required.');
        }

        return $this->company;
    }

    public function resolveFor(User $user, ?string $requestedCompanyId = null, bool $strictRequest = false): Company
    {
        $candidate = $requestedCompanyId ?: $user->active_company_id;

        if ($candidate) {
            $membership = CompanyMembership::query()
                ->where('company_id', $candidate)
                ->where('user_id', $user->getKey())
                ->where('is_active', true)
                ->first();
            $company = $membership ? Company::query()->find($candidate) : null;

            if ($membership && $company) {
                $this->set($company);
                if ($user->active_company_id !== $company->id) {
                    $user->forceFill(['active_company_id' => $company->id])->saveQuietly();
                }
                return $company;
            }

            if ($strictRequest) {
                throw new AuthorizationException('You do not have access to the requested company.');
            }

            $user->forceFill(['active_company_id' => null])->saveQuietly();
        }

        $membership = CompanyMembership::query()
            ->where('user_id', $user->getKey())
            ->where('is_active', true)
            ->whereHas('company')
            ->oldest()
            ->first();

        if ($membership) {
            $company = Company::query()->findOrFail($membership->company_id);
        } else {
            $company = Company::query()->create([
                'name' => $user->name."'s Company",
                'legal_name' => $user->name,
                'currency_code' => 'AUD',
                'timezone' => config('app.timezone', 'Australia/Sydney'),
                'email' => $user->email,
                'settings_json' => ['invoice_prefix' => 'INV'],
            ]);

            CompanyMembership::query()->create([
                'company_id' => $company->id,
                'user_id' => $user->getKey(),
                'role' => 'owner',
                'permissions_json' => ['*'],
                'is_active' => true,
            ]);
        }

        $user->forceFill(['active_company_id' => $company->id])->saveQuietly();
        $this->set($company);

        return $company;
    }
}
