# Technical audit — legacy "HVAC Lander" site

Source: `HVAC Lander.zip` (static HTML/CSS/JS, deployed to `hvac.hashxtech.com`).

## What exists

| Item | Finding |
| --- | --- |
| Pages | `index.html`, `about.html`, `contact.html`, `call-hvac.html` (paid-traffic call landing). A `landing/` folder duplicates the same pages with older CSS. |
| Brand name | "HVAC Services" (footer also says "HVAC Services Network"). |
| Logos | `logo-hr.png` (white "HVAC Repair" text, for dark backgrounds), `logo-footer-hr.png` (red "HVAC Repair" text, for light backgrounds), `fav.png` favicon. `logo.png` / `logo-footer.png` read "Plumbing Repair" and are **not** used. |
| Phone | Default `(888) 217-5445`. |
| Colors | `#FF5722` orange, `#0E2938` / `#153445` / `#1E4155` teals, `#EBF5F6` mint, gray text scale. Carried into the new design tokens unchanged. |
| Fonts | Plus Jakarta Sans + Inter. Replaced by Bebas Neue (display) + Inter (body) per brief. |
| Analytics | Google Tag Manager `GTM-5THTR8PS`, GA4 `G-CZ86LBQJFP`, Google Ads `AW-18542742635`. |
| Call tracking | MarketCall scripts `landing.js`, `mclead.js?program_id=0`, `mclegal.js?program_id=0` (homepage), `landing.js` on other pages. Comments mention Ringba, but no Ringba script is loaded. |
| Disclosures | Footer "MANDATORY VERBATIM DISCLAIMERS REQUIRED FOR MARKETCALL & GOOGLE ADS" (referral-service disclaimer + availability disclaimer). Preserved verbatim as editable settings. |
| Legal | Privacy, Terms, How It Works, Contractor Licensing Notice — shown only in modal dialogs. Rebuilt as real, indexable, CMS-editable pages. |
| Images | Mix of HVAC photos and plumbing photos (`servise-cont-*`). Several filenames do not match content (e.g. `hvac-ducts.jpg` is an engineering classroom). Only HVAC-relevant photos were carried over. |

## Problems found

1. **Plaintext server credentials.** `deploy.py` contains the SSH host, port, username and password for the Hostinger account. Not copied into the new project. **The password should be changed now**, since the ZIP has been shared.
2. **Fabricated structured data.** Homepage JSON-LD declares `aggregateRating` 4.9 / 1,480 reviews and an `HVACBusiness` with 24/7 hours. Removed — no verified review source exists.
3. **Unverified claims in copy.** "NATE-certified", "licensed and insured", "60–90 minute arrival", "upfront flat-rate pricing", "free estimates". Removed from new copy; the business must verify before re-adding.
4. **Open phone-number injection.** `app.js` replaces every phone number with *any* value from `?phone=`, `?tel=`, `?p=`, `?mc_phone=`, `?call=`. Anyone could craft a link showing a scammer's number on your domain. Replaced with an allowlist of approved tracking numbers managed in the admin.
5. **Third-party IP geolocation** (`ipapi.co`) called on every visit without disclosure. Dropped.
6. **Broken links.** Navigation and footer link to `services.html`, which does not exist.
7. **No backend.** The contact form does not store submissions.
8. Canonicals use `.html` URLs; new site uses clean URLs with 301 redirects from the old ones.

## Old URLs → new URLs (301)

| Old | New |
| --- | --- |
| `/index.html` | `/` |
| `/about.html`, `/about` | `/about/` |
| `/contact.html`, `/contact` | `/contact/` |
| `/services.html`, `/services` | `/services/` |
| `/call-hvac.html` | `/call-hvac/` (kept as the paid landing page) |
| `/landing/*` | matching new page |
