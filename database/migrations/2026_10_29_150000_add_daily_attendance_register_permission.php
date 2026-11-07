<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::firstOrCreate(
            ['name' => 'daily-attendance-register-list', 'guard_name' => 'web'],
            ['group' => 'daily-attendance-register']
        );

        $roles = Role::query()->with('permissions', 'users')->get();
        foreach ($roles as $role) {
            $isSuperOrDev = in_array(strtolower((string) $role->name), ['developer', 'super-admin', 'admin'], true);
            $hasReportAccess = $role->permissions->pluck('name')->intersect([
                'class-report-list',
                'bus-route-report-list',
                'attedance-list',
                'daily-attendance-list',
            ])->isNotEmpty();

            if ($isSuperOrDev || $hasReportAccess) {
                $role->givePermissionTo($permission);
                foreach ($role->users as $user) {
                    $user->givePermissionTo($permission);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permission = Permission::where('name', 'daily-attendance-register-list')->first();
        if ($permission) {
            $permission->roles()->detach();
            $permission->users()->detach();
            $permission->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
