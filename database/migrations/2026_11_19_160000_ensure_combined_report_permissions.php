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

        $permissionDefs = [
            ['name' => 'combined-report-list', 'group' => 'combined-report'],
            ['name' => 'combined-report-create', 'group' => 'combined-report'],
            ['name' => 'combined-report-edit', 'group' => 'combined-report'],
            ['name' => 'combined-report-delete', 'group' => 'combined-report'],
        ];

        $permissions = [];
        foreach ($permissionDefs as $def) {
            $permissions[] = Permission::firstOrCreate(
                ['name' => $def['name'], 'guard_name' => 'web'],
                ['group' => $def['group']]
            );
        }

        $roles = Role::query()->get()->filter(function ($role) {
            return strtolower((string) $role->name) === 'developer';
        });

        foreach ($roles as $role) {
            $role->givePermissionTo($permissions);
            foreach ($role->users as $user) {
                $user->givePermissionTo($permissions);
            }
        }

        // Ensure only developer keeps these (revoke from other roles if previously assigned).
        $otherRoles = Role::query()->get()->filter(function ($role) {
            return strtolower((string) $role->name) !== 'developer';
        });
        foreach ($otherRoles as $role) {
            $role->revokePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep permissions; only ensured here for developer access.
    }
};
