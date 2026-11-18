<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $currentDb = (string) DB::connection()->getDatabaseName();
        $usDb = (string) (config('schools.list.us.database') ?: env('DB_DATABASE_US', 'ultra_school_us'));

        // Keep only on US school; remove from UES / UPS / UV.
        if ($currentDb === $usDb) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return;
        }

        $names = [
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

        $permissions = Permission::whereIn('name', $names)->get();
        foreach ($permissions as $permission) {
            $permission->roles()->detach();
            $permission->users()->detach();
            $permission->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // No-op: cleanup migration for non-US schools.
    }
};
