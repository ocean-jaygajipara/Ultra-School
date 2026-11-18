<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    protected function permissionNames(): array
    {
        return [
            'name-change-form-list',
            'name-change-form-create',
            'name-change-form-edit',
            'name-change-form-delete',
            'trial-certificate-list',
            'trial-certificate-create',
            'trial-certificate-edit',
            'trial-certificate-delete',
            'character-certificate-list',
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

        // Keep on US only; remove completely from other schools.
        if ($this->isUsSchool()) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return;
        }

        $permissions = Permission::whereIn('name', $this->permissionNames())->get();
        foreach ($permissions as $permission) {
            $permission->roles()->detach();
            $permission->users()->detach();
            $permission->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // No-op: intentional US-only cleanup.
    }
};
