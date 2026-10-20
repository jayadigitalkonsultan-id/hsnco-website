<?php

namespace Database\Seeders;

use App\Models\ContentRevision;
use App\Models\Insight;
use App\Models\Media;
use App\Models\Page;
use App\Models\PracticeArea;
use App\Models\Professional;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FrontendContentSeeder extends Seeder
{
    public function run(): void
    {
        $source = json_decode(file_get_contents(__DIR__.'/data/frontend.json'), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($source) {
            $practices = [];
            foreach ($source['practices'] as $index => $item) {
                $media = $this->image('images/'.$item['image'].'.jpg');
                $practice = PracticeArea::withTrashed()->firstOrCreate(['slug' => $item['id']], [
                    'name' => $item['title'], 'summary' => $item['description'],
                    'body' => $item['description']."\n\n".implode("\n", $item['services']),
                    'image_id' => $media?->id, 'sort_order' => $index + 1,
                    'is_featured' => in_array($item['id'], ['corporate-commercial', 'mergers-acquisitions', 'banking-finance', 'litigation-dispute-resolution'], true),
                    'status' => 'draft',
                ]);
                $practices[$item['id']] = $practice;
                $this->initialRevision($practice);
            }

            $professionals = [];
            $professionalPracticeSlugs = [
                ['corporate-commercial', 'mergers-acquisitions'],
                ['litigation-dispute-resolution'],
                ['technology-intellectual-property'],
                ['banking-finance'],
                ['corporate-commercial'],
                ['employment'],
            ];
            foreach ($source['professional_roles'] as $index => $role) {
                $professionals[] = $professional = Professional::withTrashed()->firstOrCreate(['slug' => 'profil-contoh-'.($index + 1)], [
                    'name' => $role, 'position' => $role, 'level' => $index < 3 ? 'partner' : 'associate',
                    'summary' => $source['professional_focuses'][$index],
                    'biography' => 'Profil contoh. Identitas, kualifikasi, biografi, dan potret memerlukan verifikasi firma.',
                    'photo_id' => $this->image('images/team'.($index + 1).'.jpg')?->id,
                    'is_featured' => $index === 0, 'is_leadership' => $index === 0,
                    'sort_order' => $index + 1, 'status' => 'draft',
                ]);
                if ($professional->wasRecentlyCreated) {
                    $professional->practiceAreas()->sync(collect($professionalPracticeSlugs[$index] ?? [])
                        ->map(fn ($slug) => $practices[$slug]->id)->all());
                }
                $this->initialRevision($professional);
            }

            $insights = [];
            foreach ($source['insight_titles'] as $index => $title) {
                $image = ['investment', 'transactions', 'data'][$index];
                $insights[] = $insight = Insight::withTrashed()->firstOrCreate(['slug' => 'insight-'.($index + 1)], [
                    'title' => $title, 'excerpt' => 'Pratinjau publikasi; isi dan tanggal terbit menunggu verifikasi.',
                    'cover_media_id' => $this->image('images/'.$image.'.jpg')?->id, 'status' => 'draft',
                ]);
                $this->initialRevision($insight);
            }

            foreach ($source['pages'] as $item) {
                $page = Page::withTrashed()->firstOrCreate(['key' => $item['key']], [
                    'slug' => $item['slug'], 'title' => $item['title'], 'template' => $item['key'], 'status' => 'draft',
                ]);
                if (! $page->wasRecentlyCreated) {
                    continue; // Never overwrite editor changes or revive archived content.
                }
                foreach ($item['sections'] as $index => $section) {
                    if ($item['key'] === 'home') {
                        $section['content'] = array_replace($section['content'] ?? [], match ($section['key']) {
                            'hero' => [
                                'eyebrow' => 'Firma hukum independen di Indonesia',
                                'description' => 'Solusi hukum terpadu bagi pelaku usaha, institusi, dan pemimpin dalam menghadapi peluang penting di Indonesia.',
                                'button_label' => 'Kenali HSNCO', 'button_url' => '/about',
                                'image_id' => $this->image('images/hero.jpg')?->id,
                                'image_alt' => 'Arsitektur kota sebagai latar halaman utama',
                            ],
                            'introduction' => [
                                'eyebrow' => 'Pendampingan hukum yang menyeluruh',
                                'body' => 'HSNCO adalah firma hukum Indonesia yang menyediakan layanan menyeluruh dan solusi terpadu untuk berbagai persoalan kompleks. Kami memadukan keahlian hukum dengan pemahaman bisnis agar setiap rekomendasi praktis, tegas, dan sesuai dengan kebutuhan klien.',
                                'subheading' => 'Pemahaman lokal. Wawasan global.',
                                'button_label' => 'Cara kami membantu', 'button_url' => '/about',
                            ],
                            'office-image' => [
                                'media_id' => $this->image('images/office.jpg')?->id,
                                'alt' => 'Ilustrasi interior kantor modern',
                                'caption' => 'Pertimbangan berpengalaman, pemahaman industri, dan pendekatan kolaboratif dalam setiap penugasan.',
                            ],
                            'services-intro' => [
                                'description' => 'Dari nasihat hukum sehari-hari hingga transaksi dan sengketa penting, para profesional kami bekerja sebagai satu tim untuk mendukung tujuan Anda.',
                                'button_label' => 'Jelajahi bidang praktik', 'button_url' => '/practice-areas',
                            ],
                            'featured-practices' => [
                                'practice_area_ids' => collect(['corporate-commercial', 'mergers-acquisitions', 'banking-finance', 'litigation-dispute-resolution'])
                                    ->map(fn ($slug) => $practices[$slug]->id)->all(),
                            ],
                            'values' => [
                                'eyebrow' => 'Nilai yang kami pegang',
                                'image_id' => $this->image('images/values.jpg')?->id,
                                'items' => [
                                    ['title' => 'Pendekatan yang jelas.', 'description' => 'Kami memahami konteks bisnis di balik persoalan hukum. Peran kami adalah menjelaskan kompleksitas dan memudahkan pengambilan keputusan berikutnya.'],
                                    ['title' => 'Integritas tanpa kompromi.', 'description' => 'Kami menjaga kepentingan klien dengan independensi, kehati-hatian, dan integritas.'],
                                    ['title' => 'Keandalan dalam setiap perkara.', 'description' => 'Pendampingan yang tanggap, perhatian profesional berpengalaman, dan nasihat yang dapat diterapkan dari awal hingga hasil akhir.'],
                                ],
                            ],
                            'sectors' => [
                                'eyebrow' => config('service-formats.eyebrow'),
                                'description' => config('service-formats.description'),
                                'items' => config('service-formats.items'),
                            ],
                            'approach' => [
                                'eyebrow' => 'Pendekatan kami',
                                'description' => 'Proses yang terarah, disesuaikan dengan prioritas Anda dan kondisi setiap perkara.',
                                'items' => [
                                    ['title' => 'Memahami', 'description' => 'Kami mendengarkan dengan saksama, mengkaji konteks, dan merumuskan hasil yang ingin dicapai.'],
                                    ['title' => 'Menyusun strategi', 'description' => 'Kami menghubungkan analisis hukum dengan prioritas bisnis untuk menyusun langkah yang praktis.'],
                                    ['title' => 'Melaksanakan', 'description' => 'Kami melaksanakan penugasan secara terarah, dengan perhatian profesional berpengalaman dan komunikasi yang jelas.'],
                                ],
                            ],
                            'recognitions' => [
                                'eyebrow' => 'Pengakuan pasar',
                                'description' => 'Klaim ditampilkan setelah bukti diperiksa dan persetujuan publikasi dicatat.',
                                'items' => collect(['Chambers Asia-Pacific', 'The Legal 500 Asia Pacific', 'IFLR1000', 'Asialaw Profiles'])
                                    ->map(fn ($title) => ['title' => $title, 'rank' => '', 'year' => '', 'evidence' => '', 'verified' => false])->all(),
                            ],
                            'consultation' => [
                                'eyebrow' => 'Mulai percakapan',
                                'description' => 'Sampaikan kebutuhan hukum yang sedang Anda hadapi.',
                                'button_label' => 'Informasi kontak', 'button_url' => '/contact',
                            ],
                            'latest-insights' => [
                                'eyebrow' => 'Wawasan hukum',
                                'limit' => 3,
                            ],
                            default => [],
                        });
                    }
                    if ($item['key'] === 'home' && $section['key'] === 'sectors') {
                        $section['heading'] = config('service-formats.heading');
                    }
                    if ($item['key'] === 'about') {
                        $defaults = config('about.sections.'.$section['key']);
                        $section['type'] = $defaults['type'];
                        $section['heading'] = $defaults['heading'];
                        $section['content'] = array_replace($section['content'] ?? [], $defaults['content']);
                    }
                    if ($item['key'] === 'practice-areas') {
                        [$section['type'], $section['heading'], $content] = match ($section['key']) {
                            'banner' => ['banner', 'Bidang praktik.', [
                                'eyebrow' => 'Keahlian lintas bidang',
                                'description' => 'Keahlian hukum khusus yang dipadukan sesuai kebutuhan bisnis setiap perkara. Jelajahi bidang praktik berikut.',
                            ]],
                            'practice-directory' => ['practice_directory', 'Keahlian terfokus. Nasihat terpadu.', [
                                'eyebrow' => 'Keahlian kami',
                                'description' => 'Keahlian lintas bidang kami terhubung untuk memberi nasihat yang terpadu sesuai kebutuhan setiap perkara.',
                            ]],
                            'integrated-services' => ['rich_text', 'Keahlian tanpa batas.', [
                                'eyebrow' => 'Pendekatan satu firma',
                                'description' => 'Persoalan kompleks sering melibatkan lebih dari satu bidang. Perspektif transaksi, regulasi, dan sengketa dipadukan untuk menjawab setiap tantangan.',
                            ]],
                            'closing-cta' => ['cta', 'Keahlian yang tepat. Di saat yang tepat.', [
                                'eyebrow' => 'Bekerja bersama HSNCO',
                                'button_label' => 'Mulai percakapan', 'button_url' => '/contact#enquiry',
                            ]],
                            default => [$section['type'], $section['heading'], []],
                        };
                        $section['content'] = $content;
                    }
                    if ($item['key'] === 'team') {
                        [$section['type'], $section['heading'], $content] = match ($section['key']) {
                            'banner' => ['banner', 'Tim profesional.', [
                                'eyebrow' => 'Tim di balik layanan hukum',
                                'description' => 'Temukan para profesional HSNCO, bidang keahlian mereka, dan pengalaman yang relevan dengan kebutuhan Anda.',
                            ]],
                            'professionals' => ['professional_directory', 'Pengalaman yang dapat diandalkan.', [
                                'eyebrow' => 'Direktori profesional',
                                'description' => 'Tim dengan keahlian yang saling melengkapi, bekerja bersama untuk memberikan nasihat hukum yang jelas dan terarah.',
                            ]],
                            'closing-cta' => ['cta', 'Wawasan berpengalaman. Komitmen pribadi.', [
                                'eyebrow' => 'Bekerja bersama HSNCO',
                                'button_label' => 'Mulai percakapan', 'button_url' => '/contact#enquiry',
                            ]],
                            default => [$section['type'], $section['heading'], []],
                        };
                        $section['content'] = $content;
                    }
                    if ($item['key'] === 'insights') {
                        [$section['type'], $section['heading'], $content] = match ($section['key']) {
                            'banner' => ['banner', 'Wawasan & perspektif.', [
                                'eyebrow' => 'Wawasan hukum',
                                'description' => 'Analisis, pandangan, dan kabar terbaru dari para profesional HSNCO.',
                            ]],
                            'publications' => ['insight_directory', 'Perspektif untuk keputusan penting.', [
                                'eyebrow' => 'Publikasi HSNCO',
                                'description' => 'Artikel dan wawasan hukum yang membantu pembaca memahami perkembangan bisnis dan regulasi.',
                            ]],
                            'closing-cta' => ['cta', 'Wawasan untuk pasar yang berkembang.', [
                                'eyebrow' => 'Bekerja bersama HSNCO',
                                'button_label' => 'Mulai percakapan', 'button_url' => '/contact#enquiry',
                            ]],
                            default => [$section['type'], $section['heading'], []],
                        };
                        $section['content'] = $content;
                    }
                    if ($item['key'] === 'contact') {
                        [$section['type'], $section['heading'], $content] = match ($section['key']) {
                            'banner' => ['banner', 'Nasihat yang jelas. Dimulai di sini.', [
                                'eyebrow' => 'Mulai percakapan',
                                'description' => 'Sampaikan kebutuhan hukum yang sedang Anda hadapi. Tim HSNCO akan meninjau permintaan awal Anda.',
                            ]],
                            'enquiry' => ['enquiry', 'Keahlian mendalam. Arah yang jelas.', [
                                'eyebrow' => 'Hubungi HSNCO',
                                'description' => 'Berikan gambaran singkat agar tim kami dapat memahami kebutuhan Anda.',
                            ]],
                            'faq' => ['faq', 'Awal percakapan yang bermakna.', [
                                'eyebrow' => 'Sebelum menghubungi kami',
                                'items' => [
                                    ['question' => 'Apakah formulir ini mengirim permintaan?', 'answer' => 'Ya. Setelah berhasil disimpan, permintaan akan masuk ke inbox HSNCO untuk ditinjau. Konfirmasi di halaman ini tampil setelah penyimpanan berhasil.'],
                                    ['question' => 'Apa yang perlu disampaikan dalam permintaan awal?', 'answer' => 'Sertakan bidang bantuan dan uraian singkat mengenai kebutuhan Anda. Jangan sertakan informasi rahasia atau dokumen sensitif pada tahap awal.'],
                                    ['question' => 'Apakah pengisian formulir membentuk hubungan advokat dan klien?', 'answer' => 'Tidak. Pengisian formulir tidak membentuk hubungan advokat dan klien. Penerimaan penugasan dan ketentuan kerja ditangani secara terpisah.'],
                                    ['question' => 'Bagaimana informasi kontak saya digunakan?', 'answer' => 'Informasi digunakan untuk meninjau dan menindaklanjuti permintaan. Akses inbox dibatasi kepada pengelola HSNCO yang berwenang.'],
                                ],
                            ]],
                            default => [$section['type'], $section['heading'], []],
                        };
                        $section['content'] = $content;
                    }
                    if ($item['key'] === 'sitemap') {
                        [$section['type'], $section['heading'], $content] = match ($section['key']) {
                            'banner' => ['banner', 'Jalur yang jelas. Setiap halaman.', [
                                'eyebrow' => 'Jelajahi HSNCO',
                                'description' => 'Temukan informasi firma, bidang praktik, profesional, publikasi, dan cara menghubungi HSNCO.',
                            ]],
                            'directory' => ['sitemap', 'Jelajahi HSNCO', [
                                'eyebrow' => 'Direktori halaman publik',
                                'description' => 'Cari halaman dan tautan publik yang tersedia di situs HSNCO.',
                            ]],
                            default => [$section['type'], $section['heading'], []],
                        };
                        $section['content'] = $content;
                    }
                    if ($section['type'] === 'practice_selection') {
                        $section['content']['practice_area_ids'] = collect($practices)->filter(fn ($p) => $p->is_featured)->pluck('id')->values()->all();
                    } elseif ($section['type'] === 'leadership') {
                        $section['content']['professional_ids'] = [$professionals[0]->id];
                    } elseif ($section['type'] === 'latest_insights') {
                        $section['content']['insight_ids'] = collect($insights)->pluck('id')->all();
                    }
                    $page->sections()->create($section + ['sort_order' => $index + 1]);
                }
                $this->initialRevision($page, ['sections' => $page->sections->map->only(['key', 'type', 'heading', 'content', 'sort_order', 'is_visible'])->all()]);
            }

            $logo = $this->image('images/logo-white.png');
            $settings = [
                'identity.name' => ['name' => 'HSNCO'],
                'identity.logo' => ['media_id' => $logo?->id],
                'identity.favicon' => ['path' => 'assets/icons/favicon.svg'],
                'contact.details' => ['verified' => false, 'office_address' => null, 'email' => null, 'phone' => null],
                'navigation.main' => ['items' => [
                    ['route' => 'home', 'label' => 'Beranda'],
                    ['route' => 'about', 'label' => 'Tentang Firma'],
                    ['route' => 'practice-areas', 'label' => 'Bidang Praktik'],
                    ['route' => 'team', 'label' => 'Profesional'],
                    ['route' => 'insights', 'label' => 'Publikasi'],
                    ['route' => 'contact', 'label' => 'Kontak'],
                ]],
                'footer.content' => ['tagline' => 'Layanan hukum profesional. Kejelasan bagi dunia usaha.', 'requires_verification' => true],
                'seo.defaults' => ['site_name' => 'HSNCO', 'description' => null],
            ];
            foreach ($settings as $key => $value) {
                $setting = SiteSetting::firstOrCreate(['key' => $key], ['group' => explode('.', $key)[0], 'value' => $value]);
                if ($key === 'identity.logo' && $logo && $setting->wasRecentlyCreated) {
                    $setting->mediaUsages()->create(['media_id' => $logo->id, 'field' => 'value.media_id']);
                }
            }
        });
    }

    private function image(string $path): ?Media
    {
        $absolute = public_path('assets/'.$path);
        if (! is_file($absolute)) {
            throw new \RuntimeException('Missing frontend seed asset: '.$path);
        }
        $dimensions = getimagesize($absolute);

        return Media::withTrashed()->firstOrCreate(['disk' => 'frontend', 'path' => $path], [
            'original_name' => basename($path), 'mime_type' => mime_content_type($absolute), 'size' => filesize($absolute),
            'width' => $dimensions[0] ?? null, 'height' => $dimensions[1] ?? null,
            'alt_text' => 'Ilustrasi dari template; menunggu verifikasi.', 'visibility' => 'public',
        ]);
    }

    private function initialRevision($content, array $extra = []): void
    {
        if (! $content->wasRecentlyCreated) {
            return;
        }
        $payload = array_merge($content->only($content->getConnection()->getSchemaBuilder()->getColumnListing($content->getTable())), $extra);
        $payload['requires_verification'] = true;
        $revision = $content->revisions()->create(['version' => 1, 'status' => 'draft', 'payload' => $payload]);
        $this->recordMediaUsages($revision, $payload);
    }

    private function recordMediaUsages(ContentRevision $revision, array $payload, string $path = 'payload'): void
    {
        foreach ($payload as $key => $value) {
            $fieldPath = $path.'.'.$key;
            if (in_array($key, ['image_id', 'media_id', 'photo_id', 'cover_media_id', 'og_media_id'], true)
                && is_string($value) && $value !== '') {
                $revision->mediaUsages()->create(['media_id' => $value, 'field' => $fieldPath]);

                continue;
            }
            if (is_array($value)) {
                $this->recordMediaUsages($revision, $value, $fieldPath);
            }
        }
    }
}
