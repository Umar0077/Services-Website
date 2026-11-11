# HVAC Services — website & CMS

Residential HVAC referral website for U.S. homeowners, with a full admin CMS.

- **Public site:** Laravel 13 + Blade + Tailwind CSS 4 + Alpine.js (server-rendered, SEO-first)
- **Admin:** Filament 5 at `/admin` (roles: Super Admin, Admin, Editor, Author)
- **Database:** MySQL 8
- **SEO engine:** dynamic sitemap index, `robots.txt`, `llms.txt` / `llms-full.txt`, JSON-LD, canonicals, automatic 301s on slug changes

| Document | What it covers |
| --- | --- |
| [docs/01-AUDIT.md](docs/01-AUDIT.md) | Audit of the legacy static site and what changed |
| [docs/02-ARCHITECTURE.md](docs/02-ARCHITECTURE.md) | Code structure, data model, SEO and lead flows |
| [docs/03-DESIGN-SYSTEM.md](docs/03-DESIGN-SYSTEM.md) | Colors, typography, components, accessibility rules |
| [docs/04-DEPLOYMENT.md](docs/04-DEPLOYMENT.md) | Server requirements, deploy steps, cron, queues, web server config |
| [docs/05-LAUNCH-CHECKLIST.md](docs/05-LAUNCH-CHECKLIST.md) | **Business decisions and verifications needed before launch** |
| [docs/06-ADMIN-GUIDE.md](docs/06-ADMIN-GUIDE.md) | How editors use the CMS |

## Run it locally (Docker)

Requires Docker Desktop and Node 20+. Nothing else needs to be installed.

```bash
cp .env.example .env
```

Set `ADMIN_EMAIL`, `ADMIN_PASSWORD` and `DEMO_PASSWORD` in `.env`, then:

```bash
docker compose up -d mysql
```

```bash
docker compose run --rm app sh -c "composer install && php artisan key:generate && php artisan storage:link && php artisan migrate --seed"
```

```bash
npm install && npm run build
```

```bash
docker compose up -d
```

- Website: http://localhost:8000
- Admin: http://localhost:8000/admin (sign in with `ADMIN_EMAIL` / `ADMIN_PASSWORD`)
- Demo login (local only): with `ADMIN_DEMO_LOGIN=true` in `.env`, the admin sign-in page is pre-filled and lists one-click demo accounts — Super Admin (`ADMIN_EMAIL` / `ADMIN_PASSWORD`), plus `editor@hvac.test` and `author@hvac.test` (both `DEMO_PASSWORD`). This is always switched off when `APP_ENV=production`.

`vendor/` lives in a Docker volume for speed. Run Composer and Artisan through the container:

```bash
docker compose exec app php artisan test
```

For live CSS/JS rebuilds while editing templates:

```bash
npm run dev
```

## Tests

```bash
docker compose exec app php artisan test
```

48 feature tests cover every public page, every admin screen, SEO output (sitemaps, llms.txt, robots, canonicals, redirects, scheduled publishing), the lead form (validation, spam traps, rate limits, notifications), call-tracking safety, role permissions, the versioned disclaimer, HTML sanitizing, and media upload safety.

## Repository notes

- Content seeders: `CoreSeeder` (safe for production), `LaunchContentSeeder` (launch copy), `DemoSeeder` (local only).
- Brand source files are in `docs/brand/`.
- Never commit `.env`. The legacy `deploy.py` contained a live server password and was intentionally not carried over — see the audit.
