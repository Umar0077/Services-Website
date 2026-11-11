# Final audit — HVAC Services website

**Date:** November 12, 2026 · **Scope:** full codebase, live local site, admin panel · **Method:** automated tests, database content scan, HTTP checks, real-browser checks (Chromium, emulated phone/tablet/desktop)

## Verdict

**Ready for CEO/client review now. Not yet ready for public launch.** The application is feature-complete and the code passed every check in this audit after three fixes. Launch is blocked only by production setup (server, email, scheduler, HTTPS, backups, version control) and by client approvals that software cannot give (legal text, service coverage, tracking numbers).

| Check | Result |
| --- | --- |
| Automated tests | **51 passed, 0 failed** (461 assertions) — 48 before the audit, all passing; 3 added |
| Public pages (HTTP) | 19 of 19 return 200; legacy `.html` URLs return 301 |
| Responsive (no sideways scrolling) | 320, 375, 768, 1440 px on `/call-hvac`; 375 px on 7 key templates; 768 px admin |
| Content scan (54 items: services, posts, pages, states, cities, images) | 0 commercial-targeting or unsupported-claim flags |
| Disclaimer text vs. client-approved wording | Exact match, no database override |
| Production asset build (`npm run build`) | Compiles |

## Fixed during this audit

1. **Admin "connection lost" alerts never appeared.** Filament's toasts are rendered by the server, so they could not show when the connection was down (or when the session had expired). Admin error toasts are now drawn in the browser. Browser-verified: lost connection and server error (HTTP 500) both show a toast; Livewire's raw error screen no longer appears. Test updated.
2. **Claims checker false positive.** The editor warning flagged "same-day service *cannot be guaranteed*" as a guarantee claim. Negated wording is now ignored, real claims are still flagged. 3 unit tests added.
3. **No production environment template.** Added `.env.production.example` with safe production values (debug off, secure and encrypted session cookies, SMTP, demo login off).

## 1. Fully implemented and verified

| Area | Evidence |
| --- | --- |
| `/call-hvac` paid landing page | Browser at 320/375/768/1440 px: no sideways scrolling; 3 call buttons; sticky call bar on phones only; full disclaimer in footer; availability note under the "same-day" badge; `noindex`; excluded from the sitemap |
| Logo and favicon | New logo in header (WebP), white version in footer and landing header, new favicon (`favicon.png`, `favicon.ico`, Apple icon); no reference to the old "Plumbing/HVAC Repair" files anywhere |
| Phone CTAs and tracking numbers | `?pn=demo` replaced the number on all CTAs; unapproved `?phone=` values are ignored (test); campaign number remembered for 30 days (by design). Every CTA reads the number from one place (the standalone 500 error page shows the default number on purpose, since it must not touch the database) |
| Residential-only targeting | Database scan of all live content and image alt text: 0 flags; `/services/commercial-hvac` returns 404; automated test scans published content |
| Approved disclaimer | Exact text in footer (every page), `/disclaimer`, `/call-hvac` footer, next to every same-day/24/7 mention, contact form link, `llms.txt`; admin-only editing with version history (tests) |
| Sitemap, robots.txt, llms.txt | Sitemap index lists only non-empty child sitemaps (pages 11, services 12, posts 8, categories 4); unverified states/cities correctly excluded; robots blocks crawling outside production and lists the sitemap in production (test); llms.txt and llms-full.txt update when content is published (test) |
| Blog visual + HTML editor | Switch between editors in the browser; switching keeps the article (test); HTML posts saved, rendered, heading anchors kept; scripts and `onerror` removed; meta descriptions no longer contain script text (tests) |
| Admin pagination | Page 2 of all 13 admin lists loads without errors (test); correct rows on page 2 (test); States list page 2 in the browser in 1.8 s locally; sort, filters and search kept between pages |
| Error alerts | Admin: server error and lost connection (browser), validation errors (test). Website: form errors (browser), expired session, too many requests, server error (tests) |
| Permissions | Guests redirected; inactive users blocked; authors limited to own drafts and cannot publish via crafted requests; editors blocked from users/settings; admins cannot edit super admins (tests) |
| Media handling | Safe renaming, responsive WebP sizes, rejects PHP/SVG/fake images, deletes all sizes, in-use images protected (tests) |
| Contact form / leads | Valid submission saved with attribution and notification queued; validation; honeypot and timing traps; rate limit; thank-you page only after a real submission (tests) |
| Scheduled publishing, queue | Scheduled posts go live when due (test); scheduler running locally with 3 tasks; 0 failed queue jobs |
| Database | 7 migrations applied, including list indexes and HTML mode; no lazy-loading errors (strict mode is on outside production) |

## 2. Implemented but not independently tested

- **Admin "session expired" (419) toast.** Laravel 13 accepts same-origin requests without the token, so a real 419 is now rare; an expired login was verified to redirect cleanly to the sign-in page. The 419 toast shares code with the verified toasts.
- **Offline/online browser events** in the admin (not simulated).
- **MarketCall scripts** (load only when enabled; behavior of MarketCall's own scripts is outside this codebase). **GTM / GA4 / Google Ads** firing (disabled locally on purpose).
- **Real email delivery** (lead alerts, admin password reset) — only tested with the log mailer and fakes.
- **Admin UI flows not clicked through in this audit:** city CSV import, lead CSV export, revision restore, media upload dialog, callout block insertion. Their code paths are reviewed; the underlying services are tested.
- **Real devices and Safari/iOS.** Responsive checks used Chromium with emulated sizes.
- **Production performance.** The last Lighthouse run (Nov 11) was against the local Docker server; it must be re-run on the real host.

## 3. Requires production configuration

| Item | Where |
| --- | --- |
| `.env` from `.env.production.example` (APP_KEY, APP_URL, `APP_DEBUG=false`, database) | Server |
| HTTPS and document root pointed at `public/` | Hosting panel |
| SMTP credentials and `LEAD_NOTIFICATION_EMAIL` | `.env` |
| Cron: `* * * * * php artisan schedule:run` (publishes scheduled posts and sends queued emails) | Server |
| `php artisan storage:link` (uploaded images) | Server, once |
| First super admin (`CoreSeeder`), launch content (`LaunchContentSeeder`) — run once | Server |
| `ANALYTICS_ENABLED=true`; MarketCall program ID; campaign tracking numbers | `.env` / Admin |
| Automated database **and** `storage/` backups | Hosting (not part of the app) |
| Google Search Console: submit `/sitemap.xml` | After launch |
| Stop `npm run dev` locally before building; deploy with `npm run build` (a `public/hot` file must never be uploaded) | Deploy step |

## 4. Missing or broken functionality

No broken functionality remains from this audit. Known gaps, by design or out of scope:

- **The project is not under version control (Git).** This is the biggest handover risk: there is no history and no rollback. Initialize a repository before deployment.
- **Phone-call leads are not shown in the admin.** Needs MarketCall API credentials and an integration.
- **No Content-Security-Policy header.** Other security headers are set; a CSP is recommended hardening once the tracking scripts are finalized.
- **Backups and error monitoring** are not provided by the app; use the host's backups and, optionally, an error-tracking service.
- Contact forms submitted in under 3 seconds (for example by a password-manager autofill plus instant click) are asked to submit again. This is the anti-spam rule and shows a clear message.

## 5. Remaining client approvals

- Legal review of Privacy Policy, Terms, Cookie Policy and Service Disclosures.
- Confirm provider coverage for 6 states and 11 cities, then mark them verified (they stay hidden from search until then).
- Confirm "free to use" wording, call-recording statement, and call-center hours (left blank).
- Approve the derived white footer logo, or supply an official dark-background version.
- Approve stock photos (some show uniform patches; the disclaimer covers actors/models).
- MarketCall program ID and campaign tracking numbers.
- Review the 12 service pages and 8 launch articles.
- **Change the Hostinger password** exposed in the legacy `deploy.py`.

## Final deployment readiness

| Question | Answer |
| --- | --- |
| Is the application complete? | Yes — all required modules exist and work |
| Is it ready to hand to the CEO/client for review? | **Yes** |
| Is it ready to go live? | **Not yet** — complete Section 3 and the approvals in Section 5 first; initialize Git and backups |
| Were any production changes made? | No. Nothing was deployed; only local code, tests and documentation changed |
