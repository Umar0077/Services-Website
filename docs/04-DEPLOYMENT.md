# Deployment

## Server requirements

- PHP 8.4 with `pdo_mysql`, `intl`, `gd`, `exif`, `zip`, `bcmath`, `mbstring`, `fileinfo`, OPcache enabled
- MySQL 8.0+
- Composer 2, Node 20+ (build step; Node is not needed on the server if you build in CI)
- HTTPS (required — the app forces HTTPS URLs in production)
- Web root pointed at `public/` (never the project root)
- A cron entry and a queue worker (below)

The current host (Hostinger shared hosting) supports this if the domain's document root can be set to the `public/` folder and SSH is available.

## Environment (`.env`)

```
APP_NAME="HVAC Services"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=            # php artisan key:generate (once)

DB_CONNECTION=mysql
DB_HOST=...
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database      # or redis
QUEUE_CONNECTION=database

MAIL_MAILER=smtp          # needed for lead alerts and admin password resets
MAIL_HOST=...
MAIL_FROM_ADDRESS=...

ANALYTICS_ENABLED=true    # loads GTM / GA4 / Google Ads / MarketCall in production
LEAD_NOTIFICATION_EMAIL=...
TRUSTED_PROXIES=          # set to * only behind a trusted CDN/load balancer (e.g. Cloudflare)

ADMIN_NAME="..."          # first super admin, created by db:seed
ADMIN_EMAIL=...
ADMIN_PASSWORD=           # leave empty to generate; reset later with: php artisan app:reset-password you@example.com
```

Never set `DEMO_PASSWORD` in production, and never upload `.env` to a public folder.

## First deploy

```bash
composer install --no-dev --optimize-autoloader
```

```bash
npm ci && npm run build
```

```bash
php artisan key:generate
```

```bash
php artisan migrate --force
```

```bash
php artisan db:seed --class=CoreSeeder --force
```

```bash
php artisan db:seed --class=LaunchContentSeeder --force
```

```bash
php artisan storage:link
```

```bash
php artisan optimize
```

`DemoSeeder` refuses to run in production. Re-running `CoreSeeder` / `LaunchContentSeeder` later would overwrite edited launch content, so run them only once.

## Updates

```bash
php artisan down --retry=30
```

```bash
git pull && composer install --no-dev --optimize-autoloader && npm ci && npm run build
```

```bash
php artisan migrate --force && php artisan optimize
```

```bash
php artisan up
```

## Scheduler and queue

The scheduler publishes scheduled posts every minute, processes queued lead emails, and prunes old data:

```
* * * * * cd /path/to/site && php artisan schedule:run >> /dev/null 2>&1
```

On hosts that allow a long-running worker, run `php artisan queue:work --tries=3` under Supervisor instead of relying on the scheduler's queue step.

## Web server

**Apache / LiteSpeed:** `public/.htaccess` already handles clean URLs, blocks dotfiles, prevents scripts from running in `/storage`, and enables compression and caching.

**Nginx:**

```nginx
server {
    server_name your-domain.com;
    root /var/www/hvac/public;
    index index.php;

    gzip on;
    gzip_types text/css application/javascript application/json image/svg+xml application/xml text/plain;

    location ~ /\.(?!well-known) { deny all; }
    location ^~ /storage/ { location ~ \.php$ { deny all; } }
    location ^~ /build/ { expires 1y; add_header Cache-Control "public, immutable"; }
    location ~* \.(jpe?g|png|webp|gif|ico|svg|woff2)$ { expires 30d; }

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

A CDN (e.g. Cloudflare) in front of the site is recommended for image delivery; set `TRUSTED_PROXIES` accordingly.

## After deploying

1. Visit `/robots.txt` — in production it allows crawling and lists the sitemap. (Locally and on staging it blocks everything.)
2. Submit `https://your-domain.com/sitemap.xml` in Google Search Console.
3. Check that the old URLs redirect: `/about.html`, `/contact.html`, `/call-hvac.html`, `/index.html`.
4. Run Lighthouse on the live homepage. Local results (dev server inside Docker) were Accessibility 100, Best Practices 100, SEO 92 (local noindex); performance must be measured on the real server.
5. Validate structured data with Google's Rich Results Test on a service page and a blog post.
