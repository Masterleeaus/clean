<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $module = Module::firstOrCreate(['module_name' => 'workcore_trade_compliance']);
        foreach ([
            'business.trade_compliance.view',
            'business.trade_compliance.profiles.manage',
            'business.trade_compliance.jobs.manage',
            'business.trade_compliance.warranties.manage',
        ] as $name) {
            $permission = Permission::updateOrCreate(
                ['name' => $name, 'module_id' => $module->id],
                [
                    'display_name' => ucwords(str_replace(['_', '.'], ' ', $name)),
                    'is_custom' => 1,
                    'allowed_permissions' => Permission::ALL_NONE,
                ],
            );
            foreach (Company::query()->select('id')->cursor() as $company) {
                $role = Role::query()->where('name', 'admin')->where('company_id', $company->id)->first();
                if ($role === null) {
                    continue;
                }
                $grant = PermissionRole::firstOrNew(['permission_id' => $permission->id, 'role_id' => $role->id]);
                $grant->permission_type_id = 4;
                $grant->save();
            }
        }
    }

    public function down(): void
    {
        // Preserve permission history and assignments during rollback.
    }
};
