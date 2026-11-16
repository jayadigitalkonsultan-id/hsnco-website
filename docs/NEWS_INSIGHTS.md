# News & Insights

## Public website

- One navigation entry: **News & Insights** at `/news-insights`.
- Homepage has one combined section showing the three latest published items across both types.
- Listing combines existing `insights` and `berita` data, sorted by publication date, nine items per page.
- Dedicated article pages: `/news-insights/insight/{slug}` and `/news-insights/news/{slug}`. Type-specific URLs prevent conflicts when slugs match.
- Each reading page includes **Recommended Insights**: up to three latest published insights, excluding the current insight. This is an automatic recency-based selection, not a manually curated endorsement. Hide the section when no alternatives exist.
- `/insights` and `/berita` permanently redirect to the combined directory. Old hash fragments cannot be translated server-side; all current cards now point directly to article pages.
- Interface text is English. Existing editorial titles and body text remain as entered in the CMS; editors should supply English article content for an entirely English publication.
- Draft, scheduled, archived and soft-deleted articles are excluded. Private media and unpublished authors are excluded. Rich HTML uses the existing sanitizer; plain text keeps line breaks.
- XML sitemap includes the directory and published article URLs.

## Admin

One sidebar entry **News & Insights** opens `/admin/publications`. It lists both types and links to existing create/edit forms. Existing permissions, publishing rules, revisions and media library are preserved. Admin/editor accounts retain their existing content access restrictions.

## Database and deployment

No new table or column is introduced. Both existing tables are retained to preserve content and audit history. The pre-existing migration `2026_11_06_000001_create_berita_table.php` must already be applied (it was pending locally and was applied during this integration).

After deploying, clear route/view cache if your deployment does not do so. Apply only pending migrations after reviewing `php artisan migrate:status`; no migration is run automatically on hosting.

## Validation

- Feature tests cover combined feed and homepage in both CMS/fallback modes, detail pages for both types, same-slug isolation, pagination, recommendations, HTML sanitization, private media, unpublished articles and admin access.
- Chrome screenshots were taken locally at 1440px and 375px for the directory and reading page; no HTTP or JavaScript errors or horizontal overflow occurred in that run.
- This work does not modify `docker/check-assets.php`; the pre-existing local change remains separate.
