<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

// Only disposable schemas created by this invocation are ever migrated, restored or dropped.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (DB::connection()->getDriverName() !== 'mysql') {
    throw new RuntimeException('Run this verification against the configured MySQL development connection.');
}

$originalConnection = DB::connection();
$originalName = $originalConnection->getName();
$original = $originalConnection->getConfig();
// A copied name makes Migrator::runMethod switch back to the original connection.
// ConnectionFactory only supplies a name when one is absent.
unset($original['name']);
$databaseNames = ['hsnco_foundation_check_'.bin2hex(random_bytes(6)), 'hsnco_foundation_restore_'.bin2hex(random_bytes(6))];
$created = [];
$server = DB::connection()->getPdo();
$exitCode = 0;
$localTableCounts = function () use ($originalConnection): array {
    $counts = [];
    foreach ($originalConnection->getSchemaBuilder()->getTableListing($originalConnection->getDatabaseName(), false) as $table) {
        $counts[$table] = $originalConnection->table($table)->count();
    }
    ksort($counts);

    return $counts;
};
$before = $localTableCounts();

try {
    foreach ($databaseNames as $name) {
        if (! preg_match('/^hsnco_foundation_(check|restore)_[a-f0-9]{12}$/', $name)) {
            throw new RuntimeException('Unsafe disposable schema name.');
        }
        $server->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $created[] = $name;
    }

    config(['database.default' => 'foundation_check', 'database.connections.foundation_check' => array_merge($original, ['url' => null, 'database' => $databaseNames[0]])]);
    $run = function (string $command, array $arguments = []): void {
        if (in_array($command, ['migrate', 'migrate:rollback', 'db:seed'], true)) {
            $arguments['--database'] = 'foundation_check';
        }
        if (Artisan::call($command, $arguments) !== 0) {
            throw new RuntimeException($command.' failed.');
        }
    };
    $run('migrate', ['--force' => true]);
    if (DB::connection('foundation_check')->getName() !== 'foundation_check') {
        throw new RuntimeException('Disposable connection identity mismatch.');
    }
    $run('migrate:rollback', ['--force' => true]);
    if (DB::table('migrations')->count() !== 0) {
        throw new RuntimeException('Migration rollback did not clear the disposable schema.');
    }
    $run('migrate', ['--force' => true]);
    $run('db:seed', ['--force' => true]);
    $run('db:seed', ['--force' => true]);
    if (DB::table('pages')->count() !== 7 || DB::table('content_revisions')->count() !== 25 || DB::table('users')->count() !== 0) {
        throw new RuntimeException('Unexpected MySQL seed results.');
    }
    $schema = DB::connection('foundation_check')->getSchemaBuilder();
    $fingerprints = [];
    foreach ($schema->getTableListing($databaseNames[0], false) as $table) {
        $foreignKeys = $schema->getForeignKeys($table);
        foreach ($foreignKeys as &$foreignKey) {
            unset($foreignKey['foreign_schema']);
        }
        unset($foreignKey);
        $definition = ['columns' => $schema->getColumns($table), 'indexes' => $schema->getIndexes($table), 'foreign_keys' => $foreignKeys];
        $fingerprints[$table] = ['sha256' => hash('sha256', json_encode($definition)), 'columns' => count($definition['columns']), 'foreign_keys' => count($foreignKeys)];
    }
    file_put_contents(base_path('docs/FOUNDATION_MYSQL_VERIFICATION.json'), json_encode(['verified_at_utc' => gmdate('c'), 'engine' => $server->getAttribute(PDO::ATTR_SERVER_VERSION), 'schema' => $fingerprints], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
    $run('database:backup');
    $backupOutput = Artisan::output();
    preg_match('/Backup saved: (.+)/', $backupOutput, $match);
    $backupPath = isset($match[1]) ? trim($match[1]) : null;
    if (! $backupPath || ! is_file($backupPath)) {
        throw new RuntimeException('Backup file missing.');
    }

    $restore = new Process([
        env('DB_CLIENT_BINARY', 'mysql'), '--host='.$original['host'], '--port='.($original['port'] ?? 3306),
        '--user='.$original['username'], '--binary-mode=1', $databaseNames[1],
    ], base_path(), ['MYSQL_PWD' => (string) ($original['password'] ?? '')]);
    $restore->setInput(file_get_contents($backupPath));
    $restore->setTimeout(120);
    $restore->run();
    if (! $restore->isSuccessful()) {
        throw new RuntimeException('Restore failed; check DB_CLIENT_BINARY and database permissions.');
    }
    config(['database.connections.foundation_restore' => array_merge($original, ['url' => null, 'database' => $databaseNames[1]])]);
    if (DB::connection('foundation_restore')->table('pages')->count() !== 7
        || DB::connection('foundation_restore')->table('content_revisions')->count() !== 25) {
        throw new RuntimeException('Restored contents did not match the backup.');
    }
    if ($localTableCounts() !== $before) {
        throw new RuntimeException('The original database table counts changed during the isolated check.');
    }
    echo "PASS: MySQL migrate, rollback, reapply, idempotent seed, backup and restore in disposable schemas.\n";
    echo 'Verification backup: '.$backupPath.PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, get_class($exception).': '.$exception->getMessage().PHP_EOL);
    $exitCode = 1;
} finally {
    DB::purge('foundation_check');
    DB::purge('foundation_restore');
    DB::setDefaultConnection($originalName);
    foreach (array_reverse($created) as $name) {
        // The name was generated, validated, and CREATE DATABASE succeeded in this invocation.
        $server->exec('DROP DATABASE `'.$name.'`');
    }
}
exit($exitCode);
