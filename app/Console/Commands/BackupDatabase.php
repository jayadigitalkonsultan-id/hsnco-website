<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'database:backup';

    protected $description = 'Back up the configured MySQL database to private local storage';

    public function handle(): int
    {
        $connection = DB::connection();
        if ($connection->getDriverName() !== 'mysql') {
            $this->error('This command supports MySQL. Use the engine-specific backup procedure for other drivers.');

            return self::FAILURE;
        }

        $config = $connection->getConfig();
        $directory = storage_path('app/private/backups');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/'.gmdate('Ymd_His').'_'.Str::lower(Str::random(8)).'.sql';
        $process = new Process([
            config('database.dump_binary'), '--single-transaction', '--skip-lock-tables', '--no-tablespaces',
            '--set-gtid-purged=OFF', '--column-statistics=0',
            '--host='.$config['host'], '--port='.($config['port'] ?? 3306), '--user='.$config['username'],
            '--result-file='.$path, $config['database'],
        ], base_path(), ['MYSQL_PWD' => (string) ($config['password'] ?? '')]);
        $process->setTimeout(300);

        try {
            $process->run();
            if (! $process->isSuccessful() || ! is_file($path) || filesize($path) === 0) {
                throw new \RuntimeException('Dump did not complete.');
            }
        } catch (\Throwable $exception) {
            File::delete($path);
            $this->error('Backup failed. Check DB_DUMP_BINARY, database access, and private storage permissions.');

            return self::FAILURE;
        }

        $this->info('Backup saved: '.$path);
        $this->line('SHA-256: '.hash_file('sha256', $path));

        return self::SUCCESS;
    }
}
