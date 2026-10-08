<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SchoolDatabaseManager
{
    const SESSION_KEY = 'active_school_code';

    /**
     * Get list of all registered schools.
     */
    public static function all(): array
    {
        return config('schools.list', []);
    }

    /**
     * Check if a school code exists.
     */
    public static function exists(string $code): bool
    {
        $code = strtolower(trim($code));
        return array_key_exists($code, self::all());
    }

    /**
     * Get information for a specific school.
     */
    public static function get(string $code): ?array
    {
        $code = strtolower(trim($code));
        return self::all()[$code] ?? null;
    }

    /**
     * Get default school code.
     */
    public static function getDefaultSchoolCode(): string
    {
        return config('schools.default', 'ues');
    }

    /**
     * Get currently active school code.
     */
    public static function getActiveSchoolCode(): string
    {
        if (Session::has(self::SESSION_KEY)) {
            $code = Session::get(self::SESSION_KEY);
            if (self::exists($code)) {
                return $code;
            }
        }

        return self::getDefaultSchoolCode();
    }

    /**
     * Get currently active school information.
     */
    public static function getActiveSchool(): array
    {
        $code = self::getActiveSchoolCode();
        return self::get($code) ?? [
            'code'       => 'ues',
            'short_name' => 'UES',
            'name'       => 'Ultra English School',
            'database'   => 'ultra_school_ues',
        ];
    }

    /**
     * Set the active school in session and switch DB.
     */
    public static function setActiveSchool(string $code): void
    {
        $code = strtolower(trim($code));
        if (self::exists($code)) {
            Session::put(self::SESSION_KEY, $code);
            self::switchDatabase($code);
        }
    }

    /**
     * Dynamically switch the default MySQL database connection.
     */
    public static function switchDatabase(string $code): void
    {
        $school = self::get($code);
        if (!$school) {
            return;
        }

        $dbName = $school['database'] ?? null;
        if ($dbName) {
            Config::set('database.connections.mysql.database', $dbName);
        }

        if (!empty($school['username'])) {
            Config::set('database.connections.mysql.username', $school['username']);
        }

        if (isset($school['password'])) {
            Config::set('database.connections.mysql.password', $school['password']);
        }

        DB::purge('mysql');
        DB::reconnect('mysql');
    }
}
