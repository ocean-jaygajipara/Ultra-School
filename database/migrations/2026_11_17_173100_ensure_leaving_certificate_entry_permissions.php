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
            ['name' => 'leaving-certificate-entry-list', 'group' => 'leaving-certificate-entry'],
            ['name' => 'leaving-certificate-entry-create', 'group' => 'leaving-certificate-entry'],
            ['name' => 'leaving-certificate-entry-edit', 'group' => 'leaving-certificate-entry'],
            ['name' => 'leaving-certificate-entry-delete', 'group' => 'leaving-certificate-entry'],
        ];

        $permissions = [];
        foreach ($permissionDefs as $def) {
            $permissions[] = Permission::firstOrCreate(
                ['name' => $def['name'], 'guard_name' => 'web'],
                ['group' => $def['group']]
            );
        }

        $roles = Role::query()->get()->filter(function ($role) {
            return in_array(strtolower((string) $role->name), ['developer', 'super-admin'], true);
        });

        foreach ($roles as $role) {
            $role->givePermissionTo($permissions);
            foreach ($role->users as $user) {
                $user->givePermissionTo($permissions);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep permissions; only ensured here for developer access.
    }
};
