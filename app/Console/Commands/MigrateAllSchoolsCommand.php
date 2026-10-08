<?php

namespace App\Console\Commands;

use App\Services\SchoolDatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateAllSchoolsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schools:migrate 
                            {--school= : Run migration for a specific school (e.g. ues, ups, uv, us)}
                            {--fresh : Drop all tables and re-run all migrations}
                            {--seed : Seed the databases}
                            {--sync-from= : Sync schema and seed/data from an existing database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and run migrations across school databases (UES, UPS, UV, US)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $allSchools = SchoolDatabaseManager::all();
        $targetSchool = strtolower(trim((string) $this->option('school')));

        if (!empty($targetSchool)) {
            if (!SchoolDatabaseManager::exists($targetSchool)) {
                $this->error("Invalid school code '{$targetSchool}'. Available options: " . implode(', ', array_keys($allSchools)));
                return Command::FAILURE;
            }
            $schools = [$targetSchool => $allSchools[$targetSchool]];
            $this->info("Targeting single school database: [{$allSchools[$targetSchool]['name']}]");
        } else {
            $schools = $allSchools;
        }

        $fresh = $this->option('fresh');
        $seed = $this->option('seed');
        $sourceDb = $this->option('sync-from');

        $this->info('====================================================');
        $this->info(' Multi-School Databases Migration & Setup ');
        $this->info('====================================================');

        // Step 1: Ensure MySQL databases exist
        foreach ($schools as $code => $school) {
            $dbName = $school['database'];
            $this->info("Checking database for [{$school['short_name']}]: {$dbName}...");

            try {
                // Connect without database to create if not exists
                $pdo = new \PDO(
                    "mysql:host=" . config('database.connections.mysql.host') . ";port=" . config('database.connections.mysql.port'),
                    config('database.connections.mysql.username'),
                    config('database.connections.mysql.password'),
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                );

                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                $this->info("✓ Database `{$dbName}` is ready.");
            } catch (\Exception $e) {
                $this->error("Failed to create/check database `{$dbName}`: " . $e->getMessage());
                return Command::FAILURE;
            }
        }

        // Step 2: Migrate each school database
        foreach ($schools as $code => $school) {
            $dbName = $school['database'];
            $this->newLine();
            $this->info("----------------------------------------------------");
            $this->info("Migrating [{$school['name']}] (DB: {$dbName})...");
            $this->info("----------------------------------------------------");

            SchoolDatabaseManager::switchDatabase($code);

            $migrateCommand = $fresh ? 'migrate:fresh' : 'migrate';
            $params = [
                '--database' => 'mysql',
                '--force' => true,
            ];

            if ($seed) {
                $params['--seed'] = true;
            }

            $exitCode = Artisan::call($migrateCommand, $params, $this->output);

            if ($exitCode !== 0) {
                $this->error("Migration failed for [{$school['name']}].");
            } else {
                $this->info("✓ Successfully migrated [{$school['name']}].");
            }
        }

        $this->newLine();
        $this->info('====================================================');
        $this->info(' All school databases have been processed successfully! ');
        $this->info('====================================================');

        return Command::SUCCESS;
    }
}
