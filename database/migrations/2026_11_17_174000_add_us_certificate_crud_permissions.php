<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    protected function permissionNames(): array
    {
        return [
            'name-change-form-create',
            'name-change-form-edit',
            'name-change-form-delete',
            'trial-certificate-create',
            'trial-certificate-edit',
            'trial-certificate-delete',
            'character-certificate-create',
            'character-certificate-edit',
            'character-certificate-delete',
        ];
    }

    protected function isUsSchool(): bool
    {
        $currentDb = (string) DB::connection()->getDatabaseName();
        $usDb = (string) (config('schools.list.us.database') ?: env('DB_DATABASE_US', 'ultra_school_us'));

        return $currentDb !== '' && $currentDb === $usDb;
    }

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Only Ultra Secondary School (US) should have these CRUD permissions.
        if (!$this->isUsSchool()) {
            $this->removePermissions();
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return;
        }

        $permissionDefs = [
            ['name' => 'name-change-form-create', 'group' => 'name-change-form'],
            ['name' => 'name-change-form-edit', 'group' => 'name-change-form'],
            ['name' => 'name-change-form-delete', 'group' => 'name-change-form'],
            ['name' => 'trial-certificate-create', 'group' => 'trial-certificate'],
            ['name' => 'trial-certificate-edit', 'group' => 'trial-certificate'],
            ['name' => 'trial-certificate-delete', 'group' => 'trial-certificate'],
            ['name' => 'character-certificate-create', 'group' => 'character-certificate'],
            ['name' => 'character-certificate-edit', 'group' => 'character-certificate'],
            ['name' => 'character-certificate-delete', 'group' => 'character-certificate'],
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
        $this->removePermissions();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function removePermissions(): void
    {
        $permissions = Permission::whereIn('name', $this->permissionNames())->get();
        foreach ($permissions as $permission) {
            $permission->roles()->detach();
            $permission->users()->detach();
            $permission->delete();
        }
    }
};
