<?php

namespace App\Console\Commands;

use App\Services\Cms\ImportServiceProfiles;
use Illuminate\Console\Command;

class ImportServices extends Command
{
    protected $signature = 'hsnco:import-services {--publish : Publish the approved initial service copy}';
    protected $description = 'Import initial service categories without overwriting existing CMS content';

    public function handle(ImportServiceProfiles $importer): int
    {
        $count = $importer->run((bool) $this->option('publish'));
        $this->info($count.' kategori ditambahkan. Konten yang sudah ada tidak diubah.');
        return self::SUCCESS;
    }
}
