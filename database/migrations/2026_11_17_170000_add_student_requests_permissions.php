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
            'student-requests-list',
            'student-requests-create',
            'student-requests-edit',
            'student-requests-delete',
        ];

        $permissions = [];
        foreach ($names as $name) {
            $permissions[] = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['group' => 'student-requests']
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
        $permissions = Permission::whereIn('name', [
            'student-requests-list',
            'student-requests-create',
            'student-requests-edit',
            'student-requests-delete',
        ])->get();

        foreach ($permissions as $permission) {
            $permission->roles()->detach();
            $permission->users()->detach();
            $permission->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
