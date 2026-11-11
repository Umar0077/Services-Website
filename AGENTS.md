# Project notes for AI coding agents

HVAC Services — Laravel 13 + Filament 5 + Tailwind 4 + Alpine. Read README.md and docs/ first.

- PHP is not installed on the host. Run PHP, Composer and Artisan in Docker: `docker compose exec app php artisan ...` (vendor/ lives in a Docker volume). Do not install PHP from remote scripts.
- Frontend: `npm run build` / `npm run dev` on the host.
- Tests: `docker compose exec app php artisan test` (SQLite in-memory; image variants disabled via MEDIA_VARIANTS=false).
- The site targets U.S. homeowners only (residential HVAC). No commercial/B2B content. HVAC Services is an independent referral service: never claim it employs technicians, guarantees work, verifies licenses, or guarantees availability.
- The disclaimer is client-approved legal text. Read it via App\Support\Disclaimer; change it only through Admin → Settings (versioned). Never reword it in code, seeders or content.
- CMS HTML must be rendered through App\Support\RichText::render (sanitized). Never echo CMS HTML directly.
- Filament closures inject by parameter name: use `$query`, `$record`, `$state`, `$get`, `$set`.
- Rich editor form state is TipTap JSON; use App\Filament\Support\Fields::toText() before text checks.
- Never commit .env or credentials.
