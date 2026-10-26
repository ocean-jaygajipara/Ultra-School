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

        $permissions = [
            'fee-receipt-entry-list',
            'fee-receipt-entry-create',
            'fee-receipt-entry-edit',
            'fee-receipt-entry-delete',
        ];

        $created = [];
        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['group' => 'Fee Receipt Entry']
            );
            $created[$name] = $permission;
        }

        // Map fees-collection permissions to fee-receipt-entry permissions
        $mapping = [
            'fees-collection-list' => 'fee-receipt-entry-list',
            'fees-collection-create' => 'fee-receipt-entry-create',
            'fees-collection-edit' => 'fee-receipt-entry-edit',
            'fees-collection-delete' => 'fee-receipt-entry-delete',
        ];

        $roles = Role::query()->with('permissions', 'users')->get();
        foreach ($roles as $role) {
            $isSuperOrDev = in_array(strtolower((string) $role->name), ['developer', 'super-admin', 'admin'], true);
            if ($isSuperOrDev) {
                $role->givePermissionTo(array_values($created));
                foreach ($role->users as $user) {
                    $user->givePermissionTo(array_values($created));
                }
                continue;
            }

            $rolePermNames = $role->permissions->pluck('name')->all();
            $toAssign = [];
            foreach ($mapping as $oldPerm => $newPerm) {
                if (in_array($oldPerm, $rolePermNames, true) && isset($created[$newPerm])) {
                    $toAssign[] = $created[$newPerm];
                }
            }

            if (!empty($toAssign)) {
                $role->givePermissionTo($toAssign);
                foreach ($role->users as $user) {
                    $user->givePermissionTo($toAssign);
                }
            }
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
        ])->delete();
    }
};
