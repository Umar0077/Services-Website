# Design system

Tokens are defined once in `resources/css/app.css` (`@theme`) and used as Tailwind utilities.

## Color

| Token | Hex | Use |
| --- | --- | --- |
| `orange` | `#FF5722` | Primary buttons, highlights in large headings, icons on dark backgrounds |
| `orange-600` | `#E64A19` | Gradient end, admin primary |
| `orange-ink` | `#C63D0F` | Small orange text on light backgrounds (5.1:1) |
| `teal-900` | `#0E2938` | Hero, footer, dark panels, button text on orange |
| `teal-800` | `#153445` | Dark panels |
| `teal-700` | `#1E4155` | Dark surfaces, icons on light backgrounds |
| `mint` | `#EBF5F6` | Alternate section background, tips |
| `page` / white | `#F8F9FA` / `#FFFFFF` | Backgrounds |
| `ink` / `body` / `muted` / `line` | `#111827` / `#4B5563` / `#6B7280` / `#E5E7EB` | Text and borders |

**Contrast decision:** white text on `#FF5722` is only 3.2:1, which fails WCAG AA for button labels. Primary buttons therefore use deep-teal text on orange (4.7:1). Orange is used for text only at display sizes or on dark backgrounds.

Gradients (used sparingly): `--gradient-orange` (primary buttons), `--gradient-teal` (dark sections), `--gradient-hero` (photo overlays), `--gradient-light`.

## Typography

- **Bebas Neue** (self-hosted, OFL) — hero, page titles, section headings, numbers. Classes: `.h-hero`, `.h-page`, `.h-section`, `.h-card`, `.display`. Never used for paragraphs, forms, navigation or article body headings.
- **Inter** (self-hosted variable font) — everything else. Article titles and in-article headings use Inter for readability.
- Fluid sizes with `clamp()`: hero 52–88px, page 46–72px, section 38–60px, body 16–17px.

## Components (`resources/views/components`)

`layouts.app`, `header`, `mobile-nav`, `footer`, `mobile-call-bar`, `logo`, `page-hero`, `section-heading`, `breadcrumbs`, `service-card` (image / compact), `blog-card` (standard / featured), `faq`, `contact-form`, `cta-band`, `sections` (CMS blocks), `disclaimer`, `availability-note`, `phone`, `picture` (responsive WebP), `glyph` (Lucide icons), `pagination`, `empty-state`, `seo`, `tracking`.

CSS component classes: `.btn` + `.btn-primary | -dark | -outline | -ghost-light` (+ `-lg`, `-sm`), `.card`, `.card-hover`, `.icon-chip`, `.eyebrow`, `.lead`, `.input`, `.field-label`, `.field-error`, `.prose-article`, `.callout-*`, `.surface-teal`, `.container-site` (max 1320px), `.section`.

## Motion

150–350ms transitions on hover, dropdowns, accordion and drawer; a one-time 500ms fade-up on scroll (`.reveal`). Everything is disabled under `prefers-reduced-motion`, and content is visible without JavaScript.

## Accessibility

Skip link, landmarks, one H1 per page, visible focus rings, 44px+ tap targets, labelled form fields with `aria-invalid` and error summaries that receive focus, accordion buttons with `aria-expanded`/`aria-controls`, mobile drawer with focus trap and inert background, descriptive alt text from the media library. Lighthouse accessibility: 100.

## Brand assets

`public/images/brand/`: `logo-hvac-services(.png|.webp)` for light backgrounds, `logo-hvac-services-white(.png|.webp)` for dark backgrounds (navy turned white, orange kept), `favicon.png`, `icon-512.png`; plus `public/favicon.ico` and `apple-touch-icon.png`. Originals are in `docs/brand/`. All can be replaced in Admin → Settings → Branding.
