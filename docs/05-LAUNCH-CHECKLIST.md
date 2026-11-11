# Launch checklist

Items the software cannot decide. Each needs a person at HVAC Services to confirm or supply it.

## Urgent

- [ ] **Change the hosting password.** The legacy ZIP's `deploy.py` contains the SSH host, username and password for the Hostinger account in plain text. Treat it as exposed: change it, and switch deploys to SSH keys.

## Brand

- [ ] The new **HVAC Services** logo and app icon are installed. The footer uses a derived white version (navy parts turned white, orange kept) so it reads on dark teal. Confirm it, or upload an official dark-background version in Admin → Settings → Branding.
- [ ] Several stock photos show uniforms or patches (for example a "NATE" patch). The disclaimer states people shown are actors or models; replace any image the business is not comfortable with.

## Legal and claims

- [ ] Have counsel review **Privacy Policy, Terms, Cookie Policy, Service Disclosures** (Admin → Pages). They expand the legacy site's modal text and have not been legally reviewed.
- [ ] The **disclaimer** is the client-approved text (Part 22.3), stored centrally with a version history. Changes go through Admin → Settings → Disclaimer only.
- [ ] Confirm the **"free to use"** statement (homepage, About, landing page) is accurate for every channel.
- [ ] Confirm whether calls are **recorded**; the Privacy Policy currently says calls "may be" recorded where permitted.
- [ ] Removed from the legacy site because they could not be verified: 4.9-star / 1,480-review rating, "NATE-certified", "licensed and insured", "60–90 minute arrival", "flat-rate pricing", "free estimates", 24/7 dispatch claims. Re-add only with proof.

## Service coverage

- [ ] Six states (TX, CA, FL, NY, AZ, GA) and 11 cities have written content and are **published but not indexed**. For each, confirm the provider network actually serves it, then switch on **Coverage verified** / **Service availability verified** (bulk actions exist). Only then are they indexed and added to the sitemap.
- [ ] The other 44 states exist as unpublished records. Publish a state only with real, state-specific content.
- [ ] Optional: set call-center **hours** in Settings (left empty so nothing unverified is promised).

## Tracking

- [ ] In production set `ANALYTICS_ENABLED=true`, or enable tracking in Settings → Analytics. IDs carried over: GTM-5THTR8PS, G-CZ86LBQJFP, AW-18542742635.
- [ ] Confirm the **MarketCall program ID** (the legacy site used `program_id=0`).
- [ ] Add each campaign's tracking number under **Tracking numbers** and change paid ad links to `…/call-hvac?pn=<code>`. Unapproved numbers in URLs are now ignored.
- [ ] Phone-call leads stay in MarketCall. Showing them in this admin needs MarketCall API credentials and an integration (not built).

## Email

- [ ] Configure SMTP and `LEAD_NOTIFICATION_EMAIL` so new form leads trigger an alert and admin password resets work.

## Content

- [ ] Review the 12 service pages and 8 launch articles in Admin. They are written without prices, guarantees or credentials; adjust to the business's voice.
- [ ] The launch byline is "HVAC Services Editorial Team" (a non-login account). Add real authors only with accurate bios.
- [ ] Do keyword research and add articles over time; the 8 launch topics are editorial starting points.

## Technical go-live

- [ ] Follow [04-DEPLOYMENT.md](04-DEPLOYMENT.md), point the domain's document root to `public/`, enable HTTPS.
- [ ] Submit the sitemap in Search Console; check the old `.html` URLs redirect.
- [ ] Run Lighthouse and the Rich Results Test on the live site.
