<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $map = [
        'faculty-complaint-report-list' => 'teacher-complaint-report-list',
        'faculty-complaint-report-create' => 'teacher-complaint-report-create',
        'faculty-complaint-report-edit' => 'teacher-complaint-report-edit',
        'faculty-complaint-report-delete' => 'teacher-complaint-report-delete',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->map as $old => $new) {
            $permission = Permission::where('name', $old)->where('guard_name', 'web')->first();
            if (!$permission) {
                continue;
            }

            $existing = Permission::where('name', $new)->where('guard_name', 'web')->first();
            if ($existing) {
                $permission->roles()->detach();
                $permission->users()->detach();
                $permission->delete();
                continue;
            }

            $permission->name = $new;
            if (\Illuminate\Support\Facades\Schema::hasColumn($permission->getTable(), 'group')) {
                $permission->group = 'teacher-complaint-report';
            }
            $permission->save();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->map as $old => $new) {
            $permission = Permission::where('name', $new)->where('guard_name', 'web')->first();
            if (!$permission) {
                continue;
            }

            $existing = Permission::where('name', $old)->where('guard_name', 'web')->first();
            if ($existing) {
                $permission->roles()->detach();
                $permission->users()->detach();
                $permission->delete();
                continue;
            }

            $permission->name = $old;
            if (\Illuminate\Support\Facades\Schema::hasColumn($permission->getTable(), 'group')) {
                $permission->group = 'faculty-complaint-report';
            }
            $permission->save();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
