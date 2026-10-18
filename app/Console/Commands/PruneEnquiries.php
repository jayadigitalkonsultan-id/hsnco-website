<?php

namespace App\Console\Commands;

use App\Models\Enquiry;
use Illuminate\Console\Command;

class PruneEnquiries extends Command
{
    protected $signature = 'enquiries:prune {--dry-run : Tampilkan jumlah data yang akan dihapus tanpa menghapusnya}';

    protected $description = 'Hapus permanen enquiry closed/spam yang melewati masa retensi.';

    public function handle(): int
    {
        $cutoff = now()->subDays((int) config('enquiries.retention_days', 365));
        $query = Enquiry::withTrashed()
            ->whereIn('status', ['closed', 'spam'])
            ->whereRaw('COALESCE(closed_at, updated_at) < ?', [$cutoff]);
        $count = (clone $query)->count();

        if (! $this->option('dry-run')) {
            $query->forceDelete();
        }

        $this->info(($this->option('dry-run') ? 'Akan menghapus ' : 'Menghapus ').$count.' enquiry sebelum '.$cutoff->toDateString().'.');

        return self::SUCCESS;
    }
}
