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

        // 1. Fee Receipt Entry permissions
        $feePermissions = [
            'fee-receipt-entry-list',
            'fee-receipt-entry-create',
            'fee-receipt-entry-edit',
            'fee-receipt-entry-delete',
            'fee-receipt-entry-day-report',
            'fee-receipt-entry-less-report',
            'fee-receipt-entry-paid-report',
            'fee-receipt-entry-unpaid-report',
        ];

        $createdFee = [];
        foreach ($feePermissions as $name) {
            $createdFee[] = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['group' => 'Fee Receipt Entry']
            );
        }

        // 2. Bus Route Wise Students report permission
        $busRoutePerm = Permission::firstOrCreate(
            ['name' => 'bus-route-report-list', 'guard_name' => 'web'],
            ['group' => 'bus-route-report']
        );

        // 3. Assign to roles and their users
        $roles = Role::query()->with('permissions', 'users')->get();
        foreach ($roles as $role) {
            $isSuperOrDev = in_array(strtolower((string) $role->name), ['developer', 'super-admin', 'admin'], true);
            $hasAnyFee = $role->permissions->pluck('name')->intersect(array_merge($feePermissions, [
                'fees-collection-list',
                'fees-collection-create',
                'fees-collection-edit',
                'fees-collection-delete',
            ]))->isNotEmpty();

            if ($isSuperOrDev || $hasAnyFee) {
                $role->givePermissionTo($createdFee);
                foreach ($role->users as $user) {
                    $user->givePermissionTo($createdFee);
                }
            }

            $hasReportAccess = $role->permissions->pluck('name')->intersect([
                'class-report-list',
                'bus-route-village-list',
                'bus-route-report-list',
            ])->isNotEmpty();

            if ($isSuperOrDev || $hasReportAccess) {
                $role->givePermissionTo($busRoutePerm);
                foreach ($role->users as $user) {
                    $user->givePermissionTo($busRoutePerm);
                }
            }
        }

        // 4. Completely remove old fees-collection permissions
        $oldPerms = Permission::where('group', 'Fees-collection')
            ->orWhere('name', 'like', 'fees-collection%')
            ->orWhere('name', 'fee-history-list')
            ->get();

        foreach ($oldPerms as $perm) {
            $perm->roles()->detach();
            $perm->users()->detach();
            $perm->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', [
            'fee-receipt-entry-list',
            'fee-receipt-entry-create',
            'fee-receipt-entry-edit',
            'fee-receipt-entry-delete',
            'fee-receipt-entry-day-report',
            'fee-receipt-entry-less-report',
            'fee-receipt-entry-paid-report',
            'fee-receipt-entry-unpaid-report',
            'bus-route-report-list',
        ])->delete();
    }
};
