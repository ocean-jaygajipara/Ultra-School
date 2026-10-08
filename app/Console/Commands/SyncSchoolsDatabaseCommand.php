<?php

namespace App\Console\Commands;

use App\Services\SchoolDatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSchoolsDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schools:sync-initial {--from=ultra_school : Source database name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync essential users, roles, permissions, and master tables from main database to all school databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sourceDb = $this->option('from');
        $schools = SchoolDatabaseManager::all();

        $this->info("Syncing initial users and masters from source database `{$sourceDb}`...");

        // Ensure target databases and tables exist
        $this->call('schools:migrate');

        $pdo = new \PDO(
            "mysql:host=" . config('database.connections.mysql.host') . ";port=" . config('database.connections.mysql.port'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );

        $essentialTables = [
            'roles',
            'permissions',
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'users',
            'master_classes',
            'master_religions',
            'master_categories',
            'master_houses',
            'master_schools',
            'master_courses',
            'master_fees',
            'master_semesters',
            'master_subjects',
        ];

        foreach ($schools as $code => $school) {
            $targetDb = $school['database'];
            if ($targetDb === $sourceDb) {
                continue;
            }

            $this->info("Copying essential data to `{$targetDb}`...");

            try {
                // Disable foreign keys temporarily
                $pdo->exec("USE `{$targetDb}`; SET FOREIGN_KEY_CHECKS=0;");

                foreach ($essentialTables as $tbl) {
                    // Check if table exists in source
                    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '{$sourceDb}' AND table_name = '{$tbl}'");
                    if ($stmt->fetchColumn() > 0) {
                        $pdo->exec("TRUNCATE TABLE `{$targetDb}`.`{$tbl}`;");
                        $pdo->exec("INSERT INTO `{$targetDb}`.`{$tbl}` SELECT * FROM `{$sourceDb}`.`{$tbl}`;");
                        $this->line("  ✓ Copied table `{$tbl}`");
                    }
                }

                $pdo->exec("USE `{$targetDb}` ; SET FOREIGN_KEY_CHECKS=1;");
                $this->info("✓ `{$targetDb}` is synchronized.");
            } catch (\Exception $e) {
                $this->warn("  Could not copy some data to `{$targetDb}`: " . $e->getMessage());
            }
        }

        $this->info("All school databases initial sync complete!");
        return Command::SUCCESS;
    }
}
