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
            'combined-report-list',
            'combined-report-create',
            'combined-report-edit',
            'combined-report-delete',
        ];

        $permissions = Permission::query()
            ->whereIn('name', $names)
            ->get();

        if ($permissions->isEmpty()) {
            return;
        }

        $otherRoles = Role::query()
            ->get()
            ->filter(fn ($role) => strtolower((string) $role->name) !== 'developer');

        foreach ($otherRoles as $role) {
            $role->revokePermissionTo($permissions);
            foreach ($role->users as $user) {
                $user->revokePermissionTo($permissions);
            }
        }

        $developer = Role::query()
            ->get()
            ->first(fn ($role) => strtolower((string) $role->name) === 'developer');

        if ($developer) {
            $developer->givePermissionTo($permissions);
            foreach ($developer->users as $user) {
                $user->givePermissionTo($permissions);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        //
    }
};
