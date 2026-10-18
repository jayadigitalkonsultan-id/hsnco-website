# Backend enquiry

## Public submissions

`POST /enquiries` (`enquiries.store`) uses Laravel's `web` middleware, including CSRF validation, and a configurable IP rate limit (`ENQUIRY_RATE_LIMIT_PER_MINUTE`, default 5 per minute). The FormRequest accepts contact fields, an optional published practice-area ULID, a source (`home` or `contact`), and a per-form ULID submission key. Reusing a key returns the existing record instead of creating a duplicate. The server sets the initial `new` status, IP, and user agent; those fields cannot be supplied by the visitor.

## Inbox and access

Only active content managers can list, search, read, update, assign, or add internal notes to enquiries. Opening a new record marks it `read`. Inbox search covers name, email, phone, area, and message. Message and note text are rendered escaped by Blade.

## Email and delivery state

Set `ENQUIRY_NOTIFY_TO` to one or more approved comma-separated inbox addresses, plus a working `MAIL_MAILER` and its credentials. The default `log` mailer is intentionally not treated as delivery and does not log enquiry contents as if they were email. `ENQUIRY_NOTIFICATION_QUEUE` selects the queue name; `QUEUE_CONNECTION=sync` delivers during the request, while an asynchronous connection requires its queue worker to process jobs. Notification jobs retry three times with backoff. A failed attempt is reported without undoing the saved enquiry; `notification_sent_at` is set only after a successful mail send. The public success response means saved, not emailed.

Email recipients are deliberately not defaulted to a placeholder address. Until a verified inbox and mail transport are configured, enquiries still save and remain available in the admin inbox, while a notice without message contents is written to the application log.

## Retention

Closed and spam enquiries are permanently removed 365 days after `closed_at` (or `updated_at` if no closure timestamp exists), configurable through `ENQUIRY_RETENTION_DAYS`. The daily `enquiries:prune` schedule removes matching records and their notes through the database cascade. Review a count first with `php artisan enquiries:prune --dry-run`. A scheduler runner must execute Laravel's scheduled tasks in the target environment.
