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
            'route-no-list',
            'route-no-create',
            'route-no-edit',
            'route-no-delete',
        ];

        $created = [];
        foreach ($names as $name) {
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['group' => 'route-no']
            );
            if ($permission->wasRecentlyCreated) {
                $created[] = $permission;
            }
        }

        if ($created !== []) {
            $roles = Role::query()->get()->filter(function ($role) {
                return in_array(strtolower((string) $role->name), ['developer', 'super-admin'], true);
            });
            foreach ($roles as $role) {
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
            'route-no-list',
            'route-no-create',
            'route-no-edit',
            'route-no-delete',
        ])->delete();
    }
};
