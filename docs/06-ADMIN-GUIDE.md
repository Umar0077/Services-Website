# Admin guide

Sign in at `/admin`. What you see depends on your role.

| Role | Can do |
| --- | --- |
| Super Admin | Everything, including managing other admins |
| Admin | Everything except managing Super Admins; settings, disclaimer, users, redirects, tracking numbers |
| Editor | All content (pages, services, locations, blog, media) and leads |
| Author | Write and edit their own draft posts; upload images. An editor publishes. |

## Blog

**Blog → Posts → New post.** Write the title (the slug fills in automatically), excerpt, and article. The editor supports H2–H6, lists, tables, links, images, alignment and **Callout** blocks (Tip, Safety note, Key takeaway) from the `+` block button.

- **HTML mode:** under *Write the article with*, choose **HTML code** to paste or write HTML in a code editor instead of the visual editor. Switching to HTML starts from the current article. For security, scripts, styles, iframes, forms and `on…` attributes are removed when the page is shown; use **Preview** to check the result.
- **Publishing:** Draft, Scheduled (goes live automatically at the publish time), or Published. *Publish now* and *Unpublish* are at the top of the edit screen.
- **Preview** opens a private preview link, valid for 2 hours, for drafts and scheduled posts.
- **Revision history** (bottom of the edit screen) keeps every previous version; *Restore this version* is reversible.
- **Keywords & editorial checks** list practical SEO checks (title length, description, depth, headings, internal links, featured image). They guide; they do not block.
- **Targeting & claims review** warns about commercial/B2B wording and claims the referral service cannot make ("guaranteed", "licensed and insured", arrival times). This site targets homeowners only.

## Services, pages and locations

- **Content → Services:** every section of a service page, plus related services and FAQs. Drag rows to reorder.
- **Content → Pages:** About, legal pages, the `/call-hvac` paid landing page, and any new page. Pages are built from controlled section blocks (Heading + text, Feature cards, Numbered steps, Image + text).
- **Locations → States / Cities:** a location can be *published* (visible) and separately *verified* (indexed by search engines). Turn on verification only once the network actually serves that area. Cities also need at least 120 words of city-specific content; the editor shows the count. **Import CSV** on the Cities list adds cities in bulk (created unpublished for review).

## Media

**Content → Media library → Upload images.** Files are renamed safely and resized into WebP versions automatically. Always write alt text. Images that are in use cannot be deleted.

## Leads

**Leads → Form leads** lists website submissions with status, notes and campaign source. Filter by status, service, state or date; select rows to change status or **Export CSV**.

## SEO & tracking

- **Redirects:** created automatically when a slug changes; add others for old URLs.
- **Tracking numbers:** approved campaign phone numbers. Link to `…/call-hvac?pn=<code>` to show one. Numbers not on this list can never appear on the site.
- Sitemap, `robots.txt` and `llms.txt` update by themselves.

## Settings (Admins)

**Administration → Settings:** contact details, logos, homepage copy and FAQs, default SEO, analytics IDs, lead email, form consent text, and the **Disclaimer**. Changing the disclaimer requires a reason and an approval confirmation, and every version is kept in its history.

**Activity log** shows sign-ins and changes, with who made them.

## Alerts

Every problem shows a pop-up toast in the top corner instead of an error page: fields that need fixing, an expired session (with a *Refresh page* button), missing permission, too many requests, a server error ("your last change may not have been saved"), or a lost internet connection. Server errors are also written to the application log for the developer.

## Lists and pagination

All lists show 25 rows by default (10, 25, 50 or 100 can be chosen at the bottom), keep their search, filters and sorting while you move between pages, and have first/last page buttons. The activity log uses simple Previous/Next paging because it grows very large.
