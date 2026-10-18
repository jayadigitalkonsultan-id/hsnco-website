<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MigrationLifecycleTest extends TestCase
{
    public function test_preflight_and_security_migration_refuse_legacy_ids_without_changing_data(): void
    {
        config(['database.connections.legacy' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]]);
        $connection = DB::connection('legacy');
        $connection->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email');
        });
        $connection->table('users')->insert(['id' => 42, 'email' => 'preserved@example.test']);
        $this->artisan('database:preflight', ['--database' => 'legacy'])->assertFailed();
        $previous = DB::getDefaultConnection();
        DB::setDefaultConnection('legacy');
        try {
            $migration = require database_path('migrations/2026_10_16_000001_add_security_fields_to_users_table.php');
            try {
                $migration->up();
                $this->fail('An integer-key legacy table must be rejected.');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('ULID mapping', $exception->getMessage());
            }
            $this->assertSame('preserved@example.test', $connection->table('users')->where('id', 42)->value('email'));
            $this->assertFalse($connection->getSchemaBuilder()->hasColumn('users', 'status'));
        } finally {
            DB::setDefaultConnection($previous);
            DB::purge('legacy');
        }
    }

    public function test_all_migrations_can_be_applied_rolled_back_and_reapplied_on_an_isolated_database(): void
    {
        config(['database.connections.lifecycle' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]]);
        $this->artisan('migrate', ['--database' => 'lifecycle', '--force' => true])->assertSuccessful();
        $this->assertSame(21, DB::connection('lifecycle')->table('migrations')->count());
        $this->artisan('migrate:rollback', ['--database' => 'lifecycle', '--force' => true])->assertSuccessful();
        $this->assertSame(0, DB::connection('lifecycle')->table('migrations')->count());
        $this->artisan('migrate', ['--database' => 'lifecycle', '--force' => true])->assertSuccessful();
        $this->assertTrue(DB::connection('lifecycle')->getSchemaBuilder()->hasTable('two_factor_challenges'));
        DB::purge('lifecycle');
    }
}
