<?php
namespace App\Console\Commands;

use App\Models\Professional;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportProfessionals extends Command
{
    protected $signature = 'hsnco:import-professionals {--publish : Terbitkan profil dari dokumen klien} {--dry-run : Periksa tanpa menyimpan perubahan} {--sync-categories : Sesuaikan hanya kategori profil existing dengan PDF}';
    protected $description = 'Impor 19 profil PORTOFOLIO.pdf tanpa menimpa profil atau foto yang telah diedit';

    public function handle(): int
    {
        $items = json_decode(file_get_contents(database_path('seeders/data/professionals.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach ($items as $item) {
            if (!isset(config('professionals.categories')[$item['level']]) || blank($item['name']) || blank($item['biography'])) {
                $this->error('Data profil tidak valid. Tidak ada perubahan disimpan.');
                return self::FAILURE;
            }
        }
        $published = (bool) $this->option('publish');
        $dryRun = (bool) $this->option('dry-run');
        [$created, $skipped] = DB::transaction(function () use ($items, $published, $dryRun) {
            $created = $skipped = 0;
            foreach ($items as $index => $item) {
                $slug = Str::slug($item['name']);
                $existing = Professional::withTrashed()->where('slug', $slug)->orWhere('name', $item['name'])->first();
                if ($existing) {
                    if ($this->option('sync-categories') && !$existing->trashed() && $existing->level !== $item['level']) {
                        $latest = $existing->revisions()->orderByDesc('version')->first();
                        if ($latest && $existing->published_revision && $latest->version !== $existing->published_revision) {
                            $this->warn('Kategori tidak disinkronkan karena ada draft pending: '.$existing->name);
                        } elseif (!$dryRun) {
                            $existing->update(['level'=>$item['level']]);
                            $version = ($latest?->version ?? 0) + 1;
                            $existing->revisions()->create(['version'=>$version,'status'=>$existing->status,'payload'=>$existing->attributesToArray(),'published_at'=>$existing->published_at]);
                            if ($existing->status->value === 'published') $existing->update(['published_revision'=>$version]);
                            $this->line('Kategori disesuaikan: '.$existing->name);
                        }
                    }
                    $this->line('Konten existing dipertahankan: '.$item['name']);
                    $skipped++;
                    continue;
                }
                if (!$dryRun) {
                    $profile = Professional::create([
                        'name' => $item['name'], 'slug' => $slug,
                        'position' => config('professionals.categories.'.$item['level']),
                        'level' => $item['level'], 'summary' => $item['summary'] ?: null,
                        'biography' => $item['biography'], 'qualifications' => $item['qualifications'],
                        'languages' => $item['languages'], 'photo_id' => null, 'email' => null,
                        'sort_order' => $index + 1, 'is_featured' => $item['level'] === 'managing_partner',
                        'is_leadership' => $item['level'] === 'managing_partner',
                        'status' => $published ? 'published' : 'draft',
                        'published_at' => $published ? now() : null,
                        'published_revision' => $published ? 1 : null,
                    ]);
                    $profile->revisions()->create([
                        'version' => 1, 'status' => $profile->status,
                        'payload' => $profile->attributesToArray(), 'published_at' => $profile->published_at,
                    ]);
                }
                $created++;
            }
            return [$created, $skipped];
        });
        $this->info(($dryRun ? 'Pratinjau: ' : '').$created.' profil '.($dryRun ? 'akan ditambahkan' : 'ditambahkan').'; '.$skipped.' profil existing dilewati.');
        $this->line('Foto tetap kosong sampai dipilih melalui Media dan editor Profesional. Tidak ada perubahan skema tabel.');
        return self::SUCCESS;
    }
}