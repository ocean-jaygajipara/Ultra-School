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
            ['name' => 'leaving-certificate-entry-list', 'guard_name' => 'web'],
            ['group' => 'leaving-certificate-entry']
        );

        // Auto-grant only to developer; other roles assigned manually later
        $developer = Role::whereRaw('LOWER(name) = ?', ['developer'])->first();
        if ($developer) {
            $developer->givePermissionTo($permission);
            foreach ($developer->users as $user) {
                $user->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permission = Permission::where('name', 'leaving-certificate-entry-list')->first();
        if ($permission) {
            $permission->roles()->detach();
            $permission->users()->detach();
            $permission->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
