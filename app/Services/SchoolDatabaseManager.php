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

    /**
     * Get standards for the active school (or specified school code).
     * UES: 1 to 8
     * UPS: 1 to 8
     * UV: 1 to 8
     * US: 9 to 10
     */
    public static function getSchoolStandards(?string $code = null): array
    {
        $code = $code ? strtolower(trim($code)) : self::getActiveSchoolCode();
        $school = self::get($code);
        if ($school && !empty($school['standards'])) {
            return $school['standards'];
        }

        switch ($code) {
            case 'us':
                return [
                    '9th - Gujarati Medium',
                    '9th - English Medium',
                    '10th - Gujarati Medium',
                    '10th - English Medium',
                ];
            case 'ues':
            case 'ups':
            case 'uv':
            default:
                return ['1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th'];
        }
    }

    /**
     * Get passed standards for the active school (or specified school code).
     * UES, UPS, UV: Std 1 to Std 8
     * US: Std 8, Std 9, Std 10
     */
    public static function getSchoolPassedStandards(?string $code = null): array
    {
        $code = $code ? strtolower(trim($code)) : self::getActiveSchoolCode();
        $school = self::get($code);
        if ($school && !empty($school['passed_standards'])) {
            return $school['passed_standards'];
        }

        switch ($code) {
            case 'us':
                return ['Std 8', 'Std 9', 'Std 10'];
            case 'ues':
            case 'ups':
            case 'uv':
            default:
                return ['Std 1', 'Std 2', 'Std 3', 'Std 4', 'Std 5', 'Std 6', 'Std 7', 'Std 8'];
        }
    }
}
