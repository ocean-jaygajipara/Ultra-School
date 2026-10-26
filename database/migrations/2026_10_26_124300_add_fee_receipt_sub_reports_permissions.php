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

        $names = [
            'fee-receipt-entry-day-report',
            'fee-receipt-entry-less-report',
            'fee-receipt-entry-paid-report',
            'fee-receipt-entry-unpaid-report',
        ];

        $created = [];
        foreach ($names as $name) {
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['group' => 'Fee Receipt Entry']
            );
            $created[] = $permission;
        }

        $roles = Role::query()->with('permissions', 'users')->get();
        foreach ($roles as $role) {
            $isSuperOrDev = in_array(strtolower((string) $role->name), ['developer', 'super-admin', 'admin'], true);
            $hasFeeList = $role->permissions->pluck('name')->intersect(['fee-receipt-entry-list', 'fees-collection-list'])->isNotEmpty();

            if ($isSuperOrDev || $hasFeeList) {
                $role->givePermissionTo($created);
                foreach ($role->users as $user) {
                    $user->givePermissionTo($created);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', [
            'fee-receipt-entry-day-report',
            'fee-receipt-entry-less-report',
            'fee-receipt-entry-paid-report',
            'fee-receipt-entry-unpaid-report',
        ])->delete();
    }
};
