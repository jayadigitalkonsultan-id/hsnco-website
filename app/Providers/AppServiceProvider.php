<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\ContentRevision;
use App\Models\Enquiry;
use App\Models\EnquiryNote;
use App\Models\Insight;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PracticeArea;
use App\Models\Professional;
use App\Models\SiteSetting;
use App\Models\User;
use App\Policies\AuditLogPolicy;
use App\Policies\ContentPolicy;
use App\Policies\ContentRevisionPolicy;
use App\Policies\ManagedResourcePolicy;
use App\Policies\MediaPolicy;
use App\Policies\PageSectionPolicy;
use App\Policies\UserPolicy;
use App\Services\Cms\AboutPageContent;
use App\Services\Cms\VerifiedContactDetails;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Route files are not executed when production uses route:cache.
        // Register explicit CMS bindings on every application boot.
        foreach (config('admin.modules', []) as $definition) {
            \Illuminate\Support\Facades\Route::model($definition['parameter'], $definition['model']);
        }
        \Illuminate\Support\Facades\Mail::extend('gmail', fn () => new \App\Mail\Transport\GmailApiTransport(app(\App\Services\Mail\GmailOAuth::class)));
        View::composer('frontend.partials.clients', function ($view) {
            try {
                $clients = \App\Models\Client::query()->published()->with('publicImage')->orderBy('sort_order')->orderBy('name')->get();
            } catch (QueryException) {
                $clients = collect();
            }
            $view->with('clients', $clients);
        });
        // Public CMS/settings data is read per request; no content cache can serve stale published values.
        View::composer([
            'layouts.partials.head',
            'layouts.partials.header',
            'layouts.partials.navigation',
            'layouts.partials.footer',
        ], function ($view) {
            try {
                $settings = SiteSetting::query()
                    ->whereIn('key', ['identity.name', 'identity.logo', 'identity.favicon', 'navigation.main', 'seo.defaults', 'footer.content'])
                    ->pluck('value', 'key');
                $routeName = request()->route()?->getName();
                $pageKey = config('seo.route_pages.'.$routeName);
                $seoPage = $pageKey
                    ? Page::query()->published()->where('key', $pageKey)->with('publicOgMedia')->first()
                    : null;
            } catch (QueryException) {
                $settings = collect();
                $seoPage = null;
            }

            $identity = $settings['identity.name'] ?? [];
            $seo = $settings['seo.defaults'] ?? [];
            $name = trim((string) ($identity['name'] ?? $seo['site_name'] ?? 'HSNCO')) ?: 'HSNCO';
            $description = trim((string) ($seo['description'] ?? '')) ?: 'Website of '.$name;
            $tagline = trim((string) data_get($settings->get('footer.content', []), 'tagline', ''))
                ?: 'Professional legal counsel. Clarity for business.';

            $logo = null;
            $logoId = $settings['identity.logo']['media_id'] ?? null;
            if (is_string($logoId) && $logoId !== '') {
                $media = Media::query()->whereKey($logoId)->where('visibility', 'public')->first();
                if ($media && in_array($media->disk, ['public', 'frontend'], true)) {
                    $logo = $media->disk === 'frontend'
                        ? asset('assets/'.ltrim($media->path, '/'))
                        : Storage::disk('public')->url($media->path);
                }
            }
            $logo ??= asset('assets/images/logo-hsnco.png');
            if (collect(['logo-white.png','hsnco-logo.png','logo-hsnco2.png','logo-hsnco2-white.svg'])->contains(fn ($legacyLogo) => str_ends_with($logo, '/images/'.$legacyLogo))) {
                $logo = asset('assets/images/logo-hsnco.png');
            }

            $faviconPath = $settings['identity.favicon']['path'] ?? null;
            $favicon = is_string($faviconPath)
                && preg_match('~^assets/icons/[A-Za-z0-9._-]+\.(?:svg|png|ico)$~', $faviconPath)
                ? asset($faviconPath)
                : asset('assets/icons/favicon.svg');

            $defaults = [
                'home' => 'Home',
                'about' => 'About the Firm',
                'practice-areas' => 'Practice Areas',
                'industries' => 'Industries',
                'team' => 'Professionals',
                'insights' => 'Insights',
                'contact' => 'Contact',
            ];
            $configuredItems = $settings['navigation.main']['items'] ?? [];
            $configuredLabels = collect(is_array($configuredItems) ? $configuredItems : [])
                ->filter(fn ($item) => is_array($item) && isset($item['route'], $item['label']) && is_string($item['route']) && is_string($item['label']))
                ->keyBy('route');
            $navigationItems = collect($defaults)->map(fn ($label, $route) => [
                'route' => $route,
                'label' => trim((string) ($configuredLabels->get($route)['label'] ?? $label)) ?: $label,
            ])->values();

            $canonicalOrigin = rtrim(config('seo.canonical_url') ?: config('app.url'), '/');
            $routeUri = request()->path();
            $canonicalPath = $routeUri === '/' ? '/' : '/'.ltrim($routeUri, '/');
            $canonicalUrl = $canonicalOrigin.$canonicalPath;
            $seoPageTitle = filled($seoPage?->seo_title) ? $seoPage->seo_title : null;
            $seoPageDescription = filled($seoPage?->seo_description) ? $seoPage->seo_description : null;
            $seoImage = null;
            $seoImageAlt = null;
            $ogMedia = $seoPage?->publicOgMedia;
            if ($ogMedia && in_array($ogMedia->disk, ['frontend', 'public', 's3'], true)) {
                $seoImage = $ogMedia->disk === 'frontend'
                    ? asset('assets/'.ltrim($ogMedia->path, '/'))
                    : Storage::disk($ogMedia->disk)->url($ogMedia->path);
                $seoImageAlt = $ogMedia->alt_text ?: $name;
            }
            $seoImage ??= $logo;
            $seoImageAlt ??= $name;

            $view->with(compact('name', 'description', 'tagline', 'logo', 'favicon', 'navigationItems', 'canonicalUrl', 'seoPageTitle', 'seoPageDescription', 'seoImage', 'seoImageAlt'));
        });

        View::composer('frontend.home', function ($view) {
            try {
                $homePage = Page::query()->published()->where('key', 'home')->with('sections')->first();
            } catch (QueryException) {
                $homePage = null;
            }

            $homeSections = $homePage?->sections->keyBy('key') ?? collect();
            $imageIds = collect([
                data_get($homeSections->get('hero'), 'content.image_id'),
                data_get($homeSections->get('office-image'), 'content.media_id'),
                data_get($homeSections->get('values'), 'content.image_id'),
            ])->filter()->unique()->values();
            $homeMedia = $imageIds->isEmpty()
                ? collect()
                : Media::query()->whereIn('id', $imageIds)->where('visibility', 'public')->get()->keyBy('id');
            $mediaUrl = static function ($media): ?string {
                if (! $media || ! in_array($media->disk, ['frontend', 'public', 's3'], true)) {
                    return null;
                }

                return $media->disk === 'frontend'
                    ? asset('assets/'.ltrim($media->path, '/'))
                    : Storage::disk($media->disk)->url($media->path);
            };
            $homeImageUrls = [
                'hero' => $mediaUrl($homeMedia->get(data_get($homeSections->get('hero'), 'content.image_id'))),
                'office-image' => $mediaUrl($homeMedia->get(data_get($homeSections->get('office-image'), 'content.media_id'))),
                'values' => $mediaUrl($homeMedia->get(data_get($homeSections->get('values'), 'content.image_id'))),
            ];

            $contactDetails = [];
            $homeInsights = collect();
            try {
                $contactDetails = SiteSetting::query()->where('key', 'contact.details')->value('value') ?? [];
                $insightContent = $homeSections->get('latest-insights')?->content ?? [];
                $limit = min(12, max(1, (int) ($insightContent['limit'] ?? 3)));
                $selectedInsightIds = $insightContent['insight_ids'] ?? [];
                $publishedInsights = Insight::query()->published()->with('publicCoverMedia');
                if (is_array($selectedInsightIds) && $selectedInsightIds !== []) {
                    $insightMap = $publishedInsights->whereIn('id', $selectedInsightIds)->get()->keyBy('id');
                    $homeInsights = collect($selectedInsightIds)->map(fn ($id) => $insightMap->get($id))->filter()->take($limit)->values();
                } else {
                    $homeInsights = $publishedInsights->orderByDesc('published_at')->orderByDesc('id')->limit($limit)->get();
                }
                $homeInsights = $homeInsights->map(function (Insight $insight) use ($mediaUrl) {
                    $insight->home_cover_url = $mediaUrl($insight->publicCoverMedia);

                    return $insight;
                });
            } catch (QueryException) {
                $contactDetails = [];
                $homeInsights = collect();
            }

            $homeContactDetails = app(VerifiedContactDetails::class)->from($contactDetails);
            $view->with([
                'homePageIsPublished' => $homePage !== null,
                'homeSections' => $homeSections,
                'homeIndustries' => app(\App\Services\Cms\IndustryCatalog::class)->published(),
                'homeImageUrls' => $homeImageUrls,
                'homeContactDetails' => $homeContactDetails,
                'homeInsights' => $homeInsights,
            ]);
        });

        View::composer('frontend.home.practice-summary', function ($view) {
            $practices = PracticeArea::query()->published()->with('publicImage')->orderBy('sort_order')->orderBy('name')->get();
            $practices->each(function (PracticeArea $practice) {
                $image = $practice->publicImage;
                $practice->public_image_url = $image && in_array($image->disk, ['frontend', 'public', 's3'], true)
                    ? ($image->disk === 'frontend' ? asset('assets/'.ltrim($image->path, '/')) : Storage::disk($image->disk)->url($image->path))
                    : null;
            });
            $view->with('summaryPractices', $practices);
        });
        View::composer('frontend.about', function ($view) {
            try {
                $aboutPage = Page::query()->published()->where('key', 'about')->with('sections')->first();
            } catch (QueryException) {
                $aboutPage = null;
            }
            $view->with(app(AboutPageContent::class)->viewData($aboutPage));
        });

        View::composer(['frontend.practice-areas', 'components.frontend.enquiry-form', 'layouts.partials.footer'], function ($view) {
            try {
                $practiceAreas = PracticeArea::query()->published()
                    ->with('publicImage')
                    ->orderBy('sort_order')->orderBy('name')->get();
                $practicePage = Page::query()->published()->where('key', 'practice-areas')->with('sections')->first();
                $siteSettings = SiteSetting::query()->whereIn('key', ['contact.details', 'footer.content', 'navigation.main'])->pluck('value', 'key');
            } catch (QueryException) {
                $practiceAreas = collect();
                $practicePage = null;
                $siteSettings = collect();
            }

            $mediaUrl = static function ($media): ?string {
                if (! $media || ! in_array($media->disk, ['frontend', 'public', 's3'], true)) {
                    return null;
                }
                return $media->disk === 'frontend'
                    ? asset('assets/'.ltrim($media->path, '/'))
                    : Storage::disk($media->disk)->url($media->path);
            };
            $practiceAreas = $practiceAreas->map(function (PracticeArea $practice) use ($mediaUrl) {
                $practice->public_image_url = $mediaUrl($practice->publicImage);
                return $practice;
            });

            $footerContactDetails = app(VerifiedContactDetails::class)->from($siteSettings->get('contact.details', []));
            $navigationLabels = collect($siteSettings->get('navigation.main', [])['items'] ?? [])
                ->filter(fn ($item) => is_array($item) && isset($item['route'], $item['label']))
                ->keyBy('route');
            $footerFirmLinks = collect([
                'about' => 'About the Firm',
                'team' => 'Professionals',
                'insights' => 'Insights',
                'sitemap' => 'Sitemap',
            ])->map(fn ($label, $route) => [
                'route' => $route,
                'label' => trim((string) ($navigationLabels->get($route)['label'] ?? $label)) ?: $label,
            ])->values();
            $view->with([
                'practiceAreas' => $practiceAreas,
                'practicePageIsPublished' => $practicePage !== null,
                'practiceSections' => $practicePage?->sections->keyBy('key') ?? collect(),
                'footerContactDetails' => $footerContactDetails,
                'footerFirmLinks' => $footerFirmLinks,
            ]);
        });

        View::composer('frontend.team', function ($view) {
            try {
                $teamPage = Page::query()->published()->where('key', 'team')->with('sections')->first();
                $professionals = Professional::query()->published()
                    ->with(['publicPhoto', 'publishedPracticeAreas' => fn ($query) => $query->orderBy('practice_areas.sort_order')->orderBy('practice_areas.name')])
                    ->orderByDesc('is_leadership')->orderBy('sort_order')->orderBy('name')->get();
            } catch (QueryException) {
                $teamPage = null;
                $professionals = collect();
            }

            $photoUrl = static function ($media): ?string {
                if (! $media || ! in_array($media->disk, ['frontend', 'public', 's3'], true)) {
                    return null;
                }
                return $media->disk === 'frontend'
                    ? asset('assets/'.ltrim($media->path, '/'))
                    : Storage::disk($media->disk)->url($media->path);
            };
            $professionals = $professionals->map(function (Professional $professional) use ($photoUrl) {
                $professional->public_photo_url = $photoUrl($professional->publicPhoto);
                return $professional;
            });
            $featuredProfessional = $professionals->first(fn (Professional $professional) => $professional->is_leadership);
            $teamMembers = $professionals->reject(fn (Professional $professional) => $featuredProfessional && $professional->is($featuredProfessional))->values();
            $profileModalData = $professionals->mapWithKeys(fn (Professional $professional) => [$professional->slug => [
                'name' => $professional->name,
                'position' => $professional->position,
                'summary' => $professional->summary,
                'biography' => $professional->biography,
                'qualifications' => $professional->qualifications ?? [],
                'languages' => $professional->languages ?? [],
                'practiceAreas' => $professional->publishedPracticeAreas->pluck('name')->values(),
                'photo' => $professional->public_photo_url,
                'photoAlt' => $professional->publicPhoto?->alt_text ?: 'Portrait of '.$professional->name,
            ]]);

            $view->with([
                'teamPageIsPublished' => $teamPage !== null,
                'teamSections' => $teamPage?->sections->keyBy('key') ?? collect(),
                'professionals' => $professionals,
                'featuredProfessional' => $featuredProfessional,
                'teamMembers' => $teamMembers,
                'profileModalData' => $profileModalData,
            ]);
        });

        View::composer('frontend.insights', function ($view) {
            try {
                $insightPage = Page::query()->published()->where('key', 'insights')->with('sections')->first();
                $insights = Insight::query()->published()
                    ->with(['publicCoverMedia', 'publishedAuthor'])
                    ->orderByDesc('published_at')->orderByDesc('id')
                    ->paginate(7)->withQueryString();
            } catch (QueryException) {
                $insightPage = null;
                $insights = new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 7);
            }

            $mediaUrl = static function ($media): ?string {
                if (! $media || ! in_array($media->disk, ['frontend', 'public', 's3'], true)) {
                    return null;
                }
                return $media->disk === 'frontend'
                    ? asset('assets/'.ltrim($media->path, '/'))
                    : Storage::disk($media->disk)->url($media->path);
            };
            $insights->getCollection()->transform(function (Insight $insight) use ($mediaUrl) {
                $insight->cover_url = $mediaUrl($insight->publicCoverMedia);
                $insight->published_author_name = $insight->publishedAuthor?->name;
                return $insight;
            });
            $insightItems = $insights->getCollection();
            $leadInsight = $insightItems->first();
            $insightStories = $insightItems->slice(1)->values();
            $insightModalData = $insightItems->mapWithKeys(fn (Insight $insight) => [$insight->slug => [
                'title' => $insight->title,
                'excerpt' => $insight->excerpt,
                'body' => $insight->body,
                'author' => $insight->published_author_name,
                'publishedAt' => $insight->published_at?->toIso8601String(),
                'publishedDate' => $insight->published_at?->timezone(config('cms.display_timezone'))->format('d/m/Y'),
                'cover' => $insight->cover_url,
                'coverAlt' => $insight->publicCoverMedia?->alt_text ?: 'Cover image for '.$insight->title,
            ]]);

            $view->with([
                'insightPageIsPublished' => $insightPage !== null,
                'insightSections' => $insightPage?->sections->keyBy('key') ?? collect(),
                'insights' => $insights,
                'leadInsight' => $leadInsight,
                'insightStories' => $insightStories,
                'insightModalData' => $insightModalData,
            ]);
        });

        View::composer('frontend.sitemap', function ($view) {
            $page = null;
            $pages = collect();
            $practices = collect();
            try {
                $page = Page::query()->published()->where('key', 'sitemap')->with('sections')->first();
                $pages = Page::query()->published()->whereIn('key', ['home', 'about', 'practice-areas', 'team', 'insights', 'contact', 'sitemap'])->get()->keyBy('key');
                $practices = PracticeArea::query()->published()->orderBy('sort_order')->orderBy('name')->get();
            } catch (QueryException) {
                // Keep the public route directory usable when CMS tables are not available yet.
            }

            $pageLinks = collect([
                'home' => ['Home', route('home'), 'Return to the HSNCO homepage.'],
                'about' => ['About the Firm', route('about'), 'Discover the firm, its journey and approach.'],
                'practice-areas' => ['Practice Areas', route('practice-areas'), 'Explore HSNCO’s legal practice areas.'],
                'industries' => ['Industries', route('industries'), 'Explore industry sectors and their legal context.'],
                'clients' => ['Our Clients', route('clients'), 'Discover HSNCO’s clients across industries.'],
                'team' => ['Professionals', route('team'), 'Explore the HSNCO professional directory.'],
                'billing-fees' => ['Fees & Engagements', route('billing-fees'), 'Explore six fee arrangements offered by HS & CO.'],
                'insights' => ['Insights', route('insights'), 'Read available HSNCO insights and publications.'],
                'contact' => ['Contact', route('contact'), 'Contact HSNCO through our contact page.'],
                'sitemap' => ['Sitemap', route('sitemap'), 'Explore the HSNCO website structure and public links.'],
            ])->map(function (array $default, string $key) use ($pages, $practices): array {
                $page = $pages->get($key);
                $children = $key === 'practice-areas'
                    ? $practices->map(fn (PracticeArea $practice) => [
                        'text' => $practice->name,
                        'href' => route('practice-areas').'#'.$practice->slug,
                        'description' => $practice->summary ?: 'Informasi practice areas '.$practice->name.'.',
                    ])->values()->all()
                    : [];

                return [
                    'text' => $page && filled($page->title) ? $page->title : $default[0],
                    'href' => $default[1],
                    'description' => $default[2],
                    'children' => $children,
                ];
            })->values();

            $view->with([
                'sitemapPageIsPublished' => $page !== null,
                'sitemapSections' => $page?->sections->keyBy('key') ?? collect(),
                'sitemapTreeData' => $pageLinks,
            ]);
        });

        View::composer('frontend.partials.practice-enquiry', function ($view) {
            try {
                $settings = SiteSetting::query()->where('key', 'contact.details')->value('value') ?? [];
            } catch (QueryException) {
                $settings = [];
            }
            $view->with('verifiedContactDetails', app(VerifiedContactDetails::class)->from($settings));
        });
        View::composer('frontend.contact', function ($view) {
            try {
                $contactPage = Page::query()->published()->where('key', 'contact')->with('sections')->first();
                $settings = SiteSetting::query()->where('key', 'contact.details')->value('value') ?? [];
            } catch (QueryException) {
                $contactPage = null;
                $settings = [];
            }

            $view->with([
                'contactPageIsPublished' => $contactPage !== null,
                'contactSections' => $contactPage?->sections->keyBy('key') ?? collect(),
                'verifiedContactDetails' => app(VerifiedContactDetails::class)->from($settings),
            ]);
        });

        RateLimiter::for('public-enquiries', fn (Request $request) => Limit::perMinute(
            max(1, (int) config('enquiries.rate_limit_per_minute', 5)),
        )->by((string) $request->ip()));

        Relation::enforceMorphMap([
            'page' => Page::class,
            'page_section' => PageSection::class,
            'practice_area' => PracticeArea::class,
            'professional' => Professional::class,
            'insight' => Insight::class,
            'client' => \App\Models\Client::class,
            'industry' => \App\Models\Industry::class,
            'site_setting' => SiteSetting::class,
            'content_revision' => ContentRevision::class,
        ]);

        foreach ([Page::class, PracticeArea::class, Professional::class, Insight::class, \App\Models\Client::class, \App\Models\Industry::class] as $model) {
            Gate::policy($model, ContentPolicy::class);
        }
        foreach ([SiteSetting::class, Enquiry::class, EnquiryNote::class] as $model) {
            Gate::policy($model, ManagedResourcePolicy::class);
        }
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Media::class, MediaPolicy::class);
        Gate::policy(PageSection::class, PageSectionPolicy::class);
        Gate::policy(ContentRevision::class, ContentRevisionPolicy::class);

        $origin = rtrim((string) config('app.url'), '/');

        // TLS may terminate before the request reaches the container.
        // The configured production origin is authoritative for generated URLs.
        if ($this->app->isProduction()
            && filter_var($origin, FILTER_VALIDATE_URL)
            && parse_url($origin, PHP_URL_SCHEME) === 'https') {
            URL::forceRootUrl($origin);
            URL::forceScheme('https');
            URL::useAssetOrigin(config('app.asset_url') ?: $origin);
        }
    }
}
