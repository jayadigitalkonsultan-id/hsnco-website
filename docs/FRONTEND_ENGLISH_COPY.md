# English public website copy

## Scope

Public pages, navigation, breadcrumbs, headings, legal service descriptions, industry overviews, fees, enquiry forms, feedback, accessibility labels, portfolio modals, professional biographies and SEO copy now use professional English. The Our Clients heading links to /clients without an arrow symbol. The black and gold theme and typography remain.

Use clear, courteous British/international English: adviser, specialised, authorised, judgement. Prefer accurate descriptions over claims of superiority. Do not add rankings, clients, credentials, experience or guarantees that the source does not support. Proper names of people, organisations, universities and Indonesian addresses remain in their original form. Indonesian legal terms such as PKPU and PTUN retain their abbreviations with English context.

The admin interface, authentication emails, user roles, access permissions and credentials are outside this public-copy change. Source excerpts and historical revisions remain available in their original language for provenance.

## Implementation

- Public Blade views and shared partials contain reviewed English defaults.
- config/about.php, services.php, service-formats.php, billing.php, industries.php, professionals.php and clients.php provide English public copy.
- resources/content/frontend-english.tsv contains reviewed source-to-English copy used by the one-time CMS update.
- resources/content/professionals-en.json contains translations of the 19 supplied biographies alongside the source wording for reliable matching. The original PDF-import data remains intact.
- EnglishCopy recognises the approved source wording, Windows/newline variants and known prior conversions. It is a content update utility, not a runtime translation API.
- Newly imported professional biographies use the same English copy. Existing edited biographies are retained by the importer.
- Stable service slugs are explicit in config/services.php, so translating headings does not create duplicate service records or break anchors.
- Forms keep their existing POST endpoint and CSRF protection. Validation on that public endpoint uses English labels and messages.
- The unused legacy content-data.js script is no longer loaded by public pages.

## Existing CMS content: local and hosting

Source changes alone do not update records already in a production database. After deployment and a normal database backup, inspect the proposed update:

```bash
php artisan hsnco:translate-frontend-en
```

Apply on hosting only when ready:

```bash
php artisan hsnco:translate-frontend-en --apply --force
php artisan view:clear
php artisan config:cache
```

Local application has already been updated. Production has not been modified by this session. No new database table or schema migration is required for the language change.

The command updates recognised copy in Pages/sections, Practice Areas, Industries, Professionals, Insights, Client descriptions and navigation/footer/SEO settings. It preserves names of people and client organisations, IDs, slugs, links, media IDs, relation IDs, publication status, visibility, roles and source evidence. It does not create accounts, publish drafts, change professional categories, send email or call an external translation service.

Before any writes, a JSON content backup is saved under storage/app/private/content-backups on the default local disk. Preserve storage across deployments and keep a complete database backup separately. Database writes use a transaction and reject records changed during inspection. Original editorial revisions remain; translated published and pending working revisions are added separately. Repeating the command after a successful conversion makes no additional changes.

The command only knows reviewed source wording. New or substantially edited content needs an English editorial review in the CMS; it does not silently rewrite unknown factual content. Enter future public copy in English. Original-language proper names and internal provenance text are expected to remain.

## Verification

- 43 targeted tests, 671 assertions pass with route cache enabled. Covers English document/navigation/UI, dry run, backup, idempotence, original revision preservation, pending drafts, biography matching, identifiers and source evidence, CMS publication and frontend regressions.
- All 100 frontend assets pass docker/check-assets.php. The Docker build was not run.
- Browser audit script: tools/english-frontend-qa.mjs. Reports and screenshots are written under storage/app/qa-english.
- An existing professional media URL /storage/media/01M5E47N7WFVN83FF49HKDFJJP.jpg has returned HTTP 403 during earlier audits. This media permission issue is independent of the copy changes and is not reported as fixed.
Final browser verification: 10 public pages x 6 widths (1920/1440/1366/768/390/375); all eight practice modals, published professional biography data and portfolio modal, mobile navigation and client heading checked. No detected Indonesian UI text, horizontal/page/navbar overflow or JavaScript runtime errors. The previously noted media 403 persists and is recorded in the QA report. The client carousel heading remains clickable and contains only Our Clients, without an arrow.
