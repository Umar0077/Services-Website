# Architecture

## Layout

```
app/
  Enums/                Role, PostStatus, LeadStatus
  Models/               Post, Category, Tag, PostRevision, Service, State, City, Page, Media,
                        Lead, Redirect, Setting, TrackingNumber, ActivityLog, DisclaimerVersion, User
  Models/Concerns/      HasSeo (SEO columns, robots, canonical, auto-redirects, cache flush),
                        HasFaqs, LogsActivity (audit trail)
  Policies/             Role permissions — enforced on the server for every admin action
  Http/Controllers/     Home, Service, Blog, Location, Page, Contact, Disclaimer, Seo
  Http/Middleware/      HandleRedirects (legacy .html, trailing slash, CMS redirects),
                        ResolveTrackingNumber (safe call tracking + UTM attribution),
                        SecurityHeaders
  Http/Requests/        StoreLeadRequest (validation + spam traps)
  Services/             MediaService (safe uploads, WebP variants), SitemapBuilder, LlmsTxtBuilder
  Support/              Settings, Disclaimer, Phone, RichText (sanitizer), ContentCache,
                        Seo/SeoData, Seo/Schema (JSON-LD), SeoChecks, TargetingCheck
  Filament/             Admin resources, Settings page, dashboard widgets, rich-text Callout block
resources/views/
  components/           Design-system components (header, footer, hero, cards, FAQ, form, disclaimer…)
  pages/                Page templates
config/site.php         Default site settings (overridable in Admin → Settings)
database/seeders/data/  Launch content: services, states & cities, blog posts, pages
```

## Data model

- **posts** — status `draft | scheduled | published`, `published_at`, soft deletes, revisions (`post_revisions`), category, tags, related posts, related services, featured image, FAQs, keywords, SEO columns.
- **services** — content blocks (overview, problems, options, signs, details, FAQs), group, icon, related services, hero image, SEO.
- **states** (all 50 seeded) → **cities**. Each has `is_published` plus a separate *verification* flag (`coverage_verified`, `service_verified`). Service coverage via `service_state` / `city_service`.
- **pages** — CMS pages with a template (`standard`, `legal`, `landing`) and controlled section blocks. `system_key` marks pages linked from fixed places (About, Privacy, Terms, Cookies, Disclosures, Call landing); they cannot be deleted.
- **media** — file metadata, alt/caption, dimensions, WebP variants.
- **leads** — form submissions with status, internal notes and UTM/gclid attribution.
- **redirects**, **tracking_numbers**, **settings** (key/JSON value), **activity_logs**, **disclaimer_versions**.

Every public content table shares the SEO columns added by `Blueprint::seo()` (title, description, canonical, robots index/follow, Open Graph title/description/image).

## URLs

Clean URLs without trailing slashes (`/services/ac-repair`). Trailing-slash and legacy `.html` URLs 301 to the canonical form. When a slug changes, the old URL is redirected automatically, and older redirects are re-pointed so chains never form.

| Route | Template |
| --- | --- |
| `/` | Homepage |
| `/services`, `/services/{slug}` | Service directory and detail |
| `/blog`, `/blog/{slug}`, `/blog/category/{slug}`, `/blog/tag/{slug}` | Resource center |
| `/locations`, `/locations/{state}`, `/locations/{state}/{city}` | Location directory |
| `/contact` (+ `/contact/thank-you`) | Lead form |
| `/disclaimer` | Renders the central disclaimer |
| `/{page}` | CMS pages: `/about`, `/call-hvac`, legal pages, custom pages |
| `/sitemap.xml`, `/sitemap-{type}.xml`, `/robots.txt`, `/llms.txt`, `/llms-full.txt` | Generated |

## Indexing rules

A record is in the sitemap and `llms.txt` only when it is published, allows indexing, and is canonical to itself. Locations add two rules:

- **States** need `coverage_verified` and an introduction.
- **Cities** need `service_verified` and at least 120 words of city-specific content (prevents doorway pages).

Unverified pages can be published for review but render `noindex`. Generated output is cached and the cache is rebuilt whenever any content, setting or disclaimer changes (`ContentCache`).

## Call tracking

The legacy site replaced the phone number with **any** value from `?phone=`. Now:

1. Admins add approved numbers under **SEO & tracking → Tracking numbers**, each with a code.
2. Campaign links use `?pn=<code>`. Legacy `?phone=` / `?mc_phone=` values only work if they match an approved number.
3. The choice is remembered in an encrypted cookie for the attribution window (30 days by default).
4. MarketCall's scripts still load (when enabled in Settings), and phone elements keep the `.phone-link` / `.phone-display` classes the legacy integration used.

Phone-call details are not shown in the admin; that needs MarketCall API access (see the launch checklist).

## Leads

`POST /contact` → `StoreLeadRequest` validates and checks two spam traps (hidden honeypot field; encrypted render timestamp that must be 3 seconds–6 hours old) → rate limit (3/minute, 20/day per IP) → saved to `leads` with attribution → optional email alert (queued). The thank-you page is only reachable after a saved submission.

## Error alerts

- **Admin:** `resources/views/filament/error-toasts.blade.php` (loaded via a panel render hook) intercepts every failed Livewire request (`Livewire.interceptRequest`) and shows a Filament toast for 403 / 404 / 413 / 419 / 429 / 5xx and network failures, instead of Livewire's error modal. Form validation failures also raise a toast (`BasePage::$reportValidationErrorUsing` in `AdminPanelProvider`).
- **Website:** `components/toasts.blade.php` shows toasts from `session('toast')` and form validation errors; `window.siteToast(type, title, message)` is available to scripts. `bootstrap/app.php` turns expired sessions (419), rate limits (429) and, when `APP_DEBUG=false`, unexpected server errors on website form posts into a redirect back to the form with the visitor's input and a toast. Exceptions are still reported to the log.

## Admin tables

`AdminPanelProvider::boot()` applies one table configuration to every list: page sizes 10/25/50/100 (default 25), first/last page links, sort/filter/search kept in the session, 500 ms search debounce. Lists eager-load their relations (no per-row queries), sorted/filtered columns are indexed (`2026_11_12_000100_add_admin_list_indexes`), the media grid shows 480px WebP thumbnails, and the activity log uses simple pagination to avoid counting a very large table on every page.

## Blog HTML mode

Posts have `body_format` (`rich` | `html`). In HTML mode the editor stores code in `body_html`, and `Post::saving` copies it into `body`, so rendering, search, revisions, reading time and SEO checks all read one column. Output always goes through `RichText::render` (sanitized).

## Security

- Policies on every model; Authors see and edit only their own drafts, and cannot publish even with a crafted request.
- CMS HTML is sanitized on output by an allowlist (no scripts, event handlers, `javascript:` links, iframes, or inline styles).
- Uploads: raster images only (JPG, PNG, WebP, GIF) checked by file content, renamed, stored under `storage/app/public/media`, never executable (`.htaccess`).
- Security headers, HSTS on HTTPS, `noindex` on admin, CSRF on all forms, Filament login throttling, audit log of sign-ins and changes.
- Tracking IDs are validated against strict patterns before being printed into the page. No secrets are stored in settings.
