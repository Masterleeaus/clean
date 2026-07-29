<?php

use App\Domains\WorkCore\System\Contracts\TenantContextContract;

if (! function_exists('workcore_tenant')) {
    function workcore_tenant(): TenantContextContract { return app(TenantContextContract::class); }
}
if (! function_exists('workcore_company_id')) {
    function workcore_company_id(): int { return workcore_tenant()->companyId(); }
}
