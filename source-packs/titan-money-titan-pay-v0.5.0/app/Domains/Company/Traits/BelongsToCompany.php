<?php

declare(strict_types=1);

namespace App\Domains\Company\Traits;

use App\Domains\Company\Models\Company;
use App\Domains\Company\Support\CurrentCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::creating(function ($model): void {
            if (! $model->company_id) {
                $model->company_id = app(CurrentCompany::class)->id();
            }
        });

        static::addGlobalScope('company', function (Builder $builder): void {
            $companyId = app(CurrentCompany::class)->id();
            if ($companyId) {
                $builder->where($builder->qualifyColumn('company_id'), $companyId);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForCompany(Builder $query, string $companyId): Builder
    {
        return $query->withoutGlobalScope('company')->where('company_id', $companyId);
    }
}
