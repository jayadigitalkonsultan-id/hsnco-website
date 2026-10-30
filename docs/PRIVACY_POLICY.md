# Privacy Policy deployment

Public route: /privacy-policy (privacy-policy). English content is in resources/views/frontend/privacy-policy.blade.php. Footer links directly to it; XML sitemap includes it. No database migration or OAuth scope changes.

The policy reflects enquiry fields, account security, encrypted Gmail credentials and send-only Gmail access observed in the code. HSNCO must maintain the stated operational commitments (including no sale, advertising use or general-purpose AI training with Google data); hosting providers, backup handling and retention practices should be kept consistent with this page. No unverified provider names or fixed legal retention periods were invented.

After deploying, verify HTTP 200 at https://hsncoatlaw.com/privacy-policy without login. Configure that URL in Google Auth Platform Branding, replacing /contact. Domain ownership verification is a separate Search Console/DNS step; this page does not establish ownership or guarantee Google approval. Rebuild route/view caches on deployment using the existing entrypoint.

Checks: php artisan test --filter=PrivacyPolicyTest. Last updated 30 October 2026.
