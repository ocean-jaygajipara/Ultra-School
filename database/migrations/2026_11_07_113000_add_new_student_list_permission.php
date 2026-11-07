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
            ['name' => 'new-student-list-list', 'guard_name' => 'web'],
            ['group' => 'new-student-list']
        );

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
        $permission = Permission::where('name', 'new-student-list-list')->first();
        if ($permission) {
            $permission->roles()->detach();
            $permission->users()->detach();
            $permission->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
