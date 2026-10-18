<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabasePreflight extends Command
{
    protected $signature = 'database:preflight {--database= : Configured connection name}';

    protected $description = 'Inspect the selected database before migrations without changing data';

    public function handle(): int
    {
        $connection = DB::connection($this->option('database'));
        $schema = $connection->getSchemaBuilder();
        $this->line('Driver: '.$connection->getDriverName().' | Database: '.$connection->getDatabaseName());
        if (! $schema->hasTable('users')) {
            $this->info('No existing users table. Initial migrations can run after backup.');

            return self::SUCCESS;
        }

        $column = collect($schema->getColumns('users'))->firstWhere('name', 'id');
        if (! in_array($column['type_name'] ?? null, ['char', 'varchar', 'text'], true)) {
            $this->error('users.id is not a string ULID column. Prepare an explicit ID/foreign-key mapping before migration.');

            return self::FAILURE;
        }
        if ($connection->table('users')->select('id')->orderBy('id')->lazy(500)->contains(fn ($user) => ! Str::isUlid($user->id))) {
            $this->error('Existing user identifiers contain non-ULID values. Backfill planning is required.');

            return self::FAILURE;
        }
        if (! $schema->hasColumn('users', 'status') && $connection->table('users')->exists()) {
            $this->error('Existing accounts need an explicit role/status/onboarding backfill before the security migration.');

            return self::FAILURE;
        }
        $this->info('Existing user identifiers and security schema are compatible. Back up before applying pending migrations.');

        return self::SUCCESS;
    }
}
