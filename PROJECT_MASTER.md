# PROJECT_MASTER.md

Living architecture reference for the Appiappi WordPress platform. If this
project is handed to another developer or AI, this file should be enough to
understand what exists, where it lives, and how to extend it safely.

Companion files:
- [MASTER_PROMPT.md](MASTER_PROMPT.md) — the current business/product specification (the "what and why")
- [CHANGELOG.md](CHANGELOG.md) — dated record of what changed
- [DEVELOPMENT_LOG.md](DEVELOPMENT_LOG.md) — why non-obvious technical decisions were made

**Update this file whenever architecture, file locations, or behaviour change.** Do not let it drift from the code.

---

## 1. Project Overview

Appiappi is a custom WordPress platform for a Canadian company that sells and
manages websites for small/medium Canadian businesses (construction, legal,
dental, medical, real estate, professional services, restaurants, automotive,
etc.). Customers pick a curated third-party website design, the company
customizes and launches it, and — on the recurring "Growth" plan — manages
hosting, SEO, content and support long-term. Full detail in [MASTER_PROMPT.md](MASTER_PROMPT.md).

**Status: Phase 1 in progress.** A custom theme skeleton exists with a working,
responsive homepage. No custom post types, admin settings pages, or
non-homepage pages exist yet.

## 2. Business Model

See [MASTER_PROMPT.md § Core Business Model](MASTER_PROMPT.md#core-business-model) and [§ Pricing System](MASTER_PROMPT.md#pricing-system).

## 3. Target Customers

Canadian SMBs in non-marketplace, non-heavy-ecommerce industries. See [MASTER_PROMPT.md § Core Business Model](MASTER_PROMPT.md#core-business-model).

## 4. Technology Stack

- WordPress (custom theme, no page builder, no Gutenberg-frontend dependency — `wp-block-library`/`global-styles` are dequeued)
- PHP 8.0+ (theme declares `Requires PHP: 8.0` in `style.css`)
- Vanilla JS (no jQuery, no build step yet — see § 21 Known Limitations)
- Native WordPress Customizer for global/brand settings (Phase 1); a dedicated Settings API page is planned for Phase 4 for advanced options (Analytics ID, GTM, tracking scripts, currency)
- Local dev: [Local by WP Engine](https://localwp.com) — see § 20 Local Development

## 5. WordPress / Theme Architecture

Custom theme at `wp-content/themes/appiappi-theme/` (text domain `appiappi`,
theme slug `appiappi-theme`). No plugins are required for the current
homepage. Plugin architecture (contact form handling, spam protection, CPT
registration) will be decided in Phase 2 — see [DEVELOPMENT_LOG.md](DEVELOPMENT_LOG.md).

`functions.php` only requires files from `/inc/` — it holds no hooks itself.
Each `/inc/` file owns one concern (setup, enqueue, customizer, template
tags). This is enforced by convention, not tooling; keep it that way when
adding files.

## 6. Directory Structure

```
site-design-appiappi-com/               (git repo root — NOT the WP root)
├── PROJECT_MASTER.md
├── MASTER_PROMPT.md
├── CHANGELOG.md
├── DEVELOPMENT_LOG.md
├── README.md
├── .gitignore
├── design-reference/
│   └── appiappiSimple.png              Photoshop layout reference (Persian; translated to English in the build)
└── wp-content/
    └── themes/
        └── appiappi-theme/             The only tracked WP path — junctioned into the Local site, see § 20
            ├── style.css               Theme header block ONLY — no rules (see file's own comment)
            ├── functions.php           Bootstrap: requires /inc/ files only
            ├── index.php               Fallback template (WP requirement)
            ├── front-page.php          Homepage — assembles template-parts/sections/*
            ├── header.php              <head>, wp_head, opens <body>, includes site-header part
            ├── footer.php              Includes site-footer part, wp_footer, closes </body></html>
            ├── inc/
            │   ├── setup.php           Theme supports, nav menu locations, image sizes, dequeues core block CSS
            │   ├── enqueue.php         All CSS/JS registration, in dependency order; Google Fonts preconnect+enqueue
            │   ├── customizer.php      Native Customizer: brand colour, header CTA, contact info, social links, footer tagline
            │   └── template-tags.php   appiappi_icon(), nav fallback, placeholder data providers (see § 12, § 13)
            ├── template-parts/
            │   ├── header/site-header.php
            │   ├── footer/site-footer.php
            │   └── sections/           hero.php, pricing-preview.php, templates-preview.php, trust-bar.php, final-cta.php
            └── assets/
                ├── css/                tokens.css → base.css → layout.css → components.css → home.css (load order matters, see enqueue.php)
                ├── js/main.js          Mobile nav toggle + sticky header shadow, no dependencies
                └── images/hero-placeholder.svg   Inline-drawn skyline placeholder — swap for a real photo, see § 21
```

WordPress core (`wp-admin/`, `wp-includes/`, `wp-*.php`) and all non-custom
themes/plugins are intentionally **not tracked** — see `.gitignore`. The repo
root is not the WordPress root; only `wp-content/themes/appiappi-theme/`
inside it corresponds to a real path on the running site.

## 7. Database Structure / Custom Post Types / Custom Taxonomies

None yet. Planned:
- **Pricing Plan** CPT (Phase 2) — replaces `appiappi_get_pricing_plans()` placeholder, see § 12
- **Website Template** CPT + **Template Category** taxonomy (Phase 3) — replaces `appiappi_get_featured_templates()` placeholder, see § 13
- **Portfolio Project**, **Case Study**, **FAQ** CPTs (Phase 2/3)
- **Lead** CPT or custom table (Phase 4) — see [MASTER_PROMPT.md § Lead Management](MASTER_PROMPT.md#lead-management)

## 8. Settings

Implemented now via the native Customizer (`inc/customizer.php`):

| Section | Settings |
|---|---|
| Brand Colour | Primary colour (hex) — cascades to `--color-primary`, `--color-primary-dark`, `--color-primary-50` via inline `wp_head` CSS |
| Header Call to Action | Button text + URL (used in header and mobile nav) |
| Contact Information | Phone, email, address — used in footer |
| Social Links | Facebook, LinkedIn, Instagram, YouTube — used in footer |
| Footer | Footer tagline text |

Logo/favicon use core's built-in `custom-logo` theme support and Site Icon —
no custom code needed; set both under **Appearance → Customize → Site
Identity**.

Advanced settings (Analytics ID, Search Console verification, tracking
scripts, currency/pricing display, cookie settings) are **not implemented**
— planned for a dedicated Settings API page in Phase 4 per [MASTER_PROMPT.md § Site Settings](MASTER_PROMPT.md#site-settings).

## 9. Design System

Tokens live in `assets/css/tokens.css` as CSS custom properties — this is
the single source of truth for colour, type scale, spacing, radius and
shadow. Do not hardcode colour/spacing values in component files; reference
a token. See that file for the full list. Key groups:

- **Neutrals**: `--color-neutral-50` … `--color-neutral-900`
- **Brand**: `--color-navy-900/800/700`, `--color-primary(-dark/-50)`, `--color-maple` (small accent only, e.g. the leaf mark)
- **Plan accents**: `--color-plan-starter` (green), `-business` (blue), `-professional` (purple), `-growth` (orange)
- **Type**: `--font-sans` (Inter, loaded via Google Fonts + system fallback stack), `--fs-xs` … `--fs-5xl`
- **Spacing**: 4px-based scale, `--space-1` … `--space-24`
- **Radius**: `--radius-sm/md/lg/xl/full`
- **Shadow**: `--shadow-sm/md/lg`

The admin-chosen primary colour (Customizer) overrides `--color-primary` at
runtime via an inline `<style>` block injected in `wp_head` — see
`appiappi_customizer_css_vars()` in `inc/customizer.php`.

## 10. Components

Generic, reusable pieces live in `assets/css/components.css`: `.btn` (+
`-primary/-secondary/-outline-inverse/-link`, `-sm`, `-block`), `.badge`,
`.chip` (hero feature chips), `.icon-tile` (trust-bar items), `.card`, and
form field styles. Section-specific layout (hero, pricing cards, template
cards, trust bar grid, final CTA) lives in `assets/css/home.css` — keep that
split when adding new sections.

Icons are inline SVG only (no icon font, no external CDN) via
`appiappi_icon( $name, $class = '' )` in `inc/template-tags.php`. Add new
icons to the `$icons` array there; keep them simple/stroke-based/on-brand.

## 11. Header & Footer

- **Header** (`template-parts/header/site-header.php`): sticky, logo (custom
  logo or text fallback + leaf mark), primary nav (`wp_nav_menu`, location
  `primary`, falls back to `appiappi_nav_fallback()` until a real menu is
  assigned in **Appearance → Menus**), "Client Login" secondary link
  (placeholder target `/account/` for the future customer portal, Phase 5),
  primary CTA button (Customizer-controlled text/URL), mobile hamburger
  toggle. Desktop nav appears ≥1024px; below that, a full-screen mobile nav
  panel (`#mobile-nav`) opens via `assets/js/main.js`.
- **Footer** (`template-parts/footer/site-footer.php`): 4-column grid (brand
  + social, Quick Links, Services, Contact) collapsing to 2 columns ≥640px
  and 1 column below that, plus a bottom bar (copyright + legal links).
  Contact/social fields pull from the Customizer and simply don't render
  when empty (with a hint prompting the admin to fill them in).

## 12. Pricing System

**Not yet a real system** — Phase 1 renders a static preview from
`appiappi_get_pricing_plans()` in `inc/template-tags.php`, explicitly marked
`TODO(Phase 2)`. It returns the 4 launch plans (Starter $199, Business $399,
Professional $699 "Most Popular", Growth $599/mo) as a PHP array consumed by
`template-parts/sections/pricing-preview.php`. When the Pricing Plan CPT is
built, only this one function needs to change — the template already reads
through it, so no markup changes are needed at that point. A dedicated
`/pricing/` page with full comparison + FAQ (per [MASTER_PROMPT.md § Pricing Page](MASTER_PROMPT.md#pricing-page)) does not exist yet.

## 13. Template Library

**Not yet a real system.** Phase 1 renders a 3-card "Featured Website
Designs" teaser on the homepage from `appiappi_get_featured_templates()`,
explicitly marked `TODO(Phase 3)`, feeding
`template-parts/sections/templates-preview.php`. The full browsable
library — search, category sidebar, filters, `/templates/` archive, template
detail pages — is not built; it will use a **Website Template** CPT + a
category taxonomy per [MASTER_PROMPT.md § Website Template Library](MASTER_PROMPT.md#website-template-library).

## 14. Template Detail Pages / Portfolio / Case Studies / Blog / FAQ / Contact Forms / Lead Management

Not yet built. Each is specified in [MASTER_PROMPT.md](MASTER_PROMPT.md) and scheduled per the phased plan (§ 19 below).

## 15. Security

Standard WordPress escaping/sanitization conventions are followed throughout
the theme (`esc_html`, `esc_attr`, `esc_url`, `sanitize_text_field`,
`sanitize_hex_color`, `esc_url_raw` on every Customizer setting). No forms,
CPT admin screens, or AJAX endpoints exist yet, so nonce/capability
review will happen when those are added (Phase 2+).

## 16. SEO

Semantic HTML (`<header>`, `<main>`, `<footer>`, single `<h1>` on the
homepage, logical heading order), `title-tag` theme support (lets WordPress
manage `<title>`), responsive images, no keyword stuffing. No SEO plugin,
schema markup, sitemap, or meta-description system yet — planned for Phase
4 per [MASTER_PROMPT.md § SEO](MASTER_PROMPT.md#seo).

## 17. Performance

`wp-block-library`, `classic-theme-styles` and `global-styles` core CSS are
dequeued (theme has no Gutenberg-frontend dependency). CSS is split into
small, cacheable files loaded in dependency order; homepage-only CSS
(`home.css`) is enqueued conditionally via `is_front_page()`. JS is a single
small vanilla file, deferred via `wp_enqueue_script(..., true)` (footer).
Fonts load from Google Fonts with a `preconnect` resource hint — flagged as
a `TODO(perf)` in `inc/enqueue.php` to self-host once the design is locked.
The hero visual is a hand-drawn inline-referenced SVG (no large photo yet).

## 18. Responsive Design

Mobile-first throughout: base rules target small screens, with `min-width`
breakpoints at 640px, 768px, 1024px, 1280px added where needed (see any file
in `assets/css/`). Verified breakpoints: mobile nav panel (<1024px) vs. inline
nav (≥1024px), pricing grid 1→2→4 columns, template grid 1→2→3 columns,
footer grid 1→2→4 columns, hero stacked→side-by-side at 1024px.

## 19. Accessibility

Skip link (`.skip-link` → `#main-content`), visible `:focus-visible` outline
on all interactive elements, `aria-label`/`aria-expanded`/`aria-controls` on
the mobile nav toggle, `aria-hidden="true"` + `focusable="false"` on
decorative icons, alt text on the hero image. Not yet audited with a screen
reader or automated tool — recommended before Phase 2 sign-off.

## 20. Local Development

Local (by WP Engine) site `appiappicom` at `https://appiappicom.local`.
Site files: `C:\Users\GHADER\Local Sites\appiappicom\app\public`. The
theme folder there is an **NTFS directory junction** (not a symlink — this
machine requires admin elevation for symlinks) pointing at this repo's
`wp-content/themes/appiappi-theme/`:

```
New-Item -ItemType Junction -Path "C:\Users\GHADER\Local Sites\appiappicom\app\public\wp-content\themes\appiappi-theme" -Target "C:\Users\GHADER\Documents\ProjectGit\site-design-appiappi-com\wp-content\themes\appiappi-theme"
```

WP-CLI is available (bundled with Local) but not on PATH by default:

```bash
PHP="/c/Users/GHADER/AppData/Roaming/Local/lightning-services/php-8.2.29+0/bin/win64/php.exe"
WPCLI="/c/Users/GHADER/AppData/Local/Programs/Local/resources/extraResources/bin/wp-cli/wp-cli.phar"
SITE="/c/Users/GHADER/Local Sites/appiappicom/app/public"
INI="/c/Users/GHADER/AppData/Roaming/Local/run/<instance-id>/conf/php/php.ini"   # instance id changes per Local restart — check `find "/c/Users/GHADER/AppData/Roaming/Local/run" -maxdepth 1`
"$PHP" -c "$INI" "$WPCLI" <command> --path="$SITE" --allow-root
```

Theme was activated this way (`wp theme activate appiappi-theme`). Junctions
aren't portable/committable — a second machine needs Local installed, a site
created, and the junction recreated manually.

## 21. Known Limitations

- No CPTs — pricing and template-library content is hard-coded placeholder data behind a single function each (by design, see § 12–13), **not** meant to stay that way past Phase 2/3.
- No admin Settings page — only Customizer options exist.
- Hero image is a placeholder SVG illustration, not a real photograph — replace before launch.
- No nav menu created in wp-admin yet — header/mobile nav render via `appiappi_nav_fallback()` with best-guess page slugs (`/templates/`, `/services/`, `/pricing/`, etc.) that don't exist as real pages yet.
- Homepage hero's Google-rating card renders as an empty/neutral placeholder — `appiappi_get_google_rating()` returns `null` on purpose, do not hardcode a rating (see [DEVELOPMENT_LOG.md](DEVELOPMENT_LOG.md)).
- No build step / asset bundling — files are enqueued individually; fine at current size, revisit if the CSS/JS footprint grows significantly.
- No automated tests, no CI.
- Not yet accessibility-audited beyond the manual measures in § 19.

## 22. Future: Customer Portal / Support System / Payment System

Not started. Requirements captured in [MASTER_PROMPT.md § Customer Portal](MASTER_PROMPT.md#customer-portal--future-ready), [§ Support / Request Management](MASTER_PROMPT.md#support--request-management--future-system), and [§ Payment Architecture](MASTER_PROMPT.md#payment-architecture). The "Client Login" header link already reserves `/account/` as a URL target so nothing structural blocks adding this later.

## 23. Deployment Instructions

Not yet defined — no staging/production host is connected. When ready: the
theme folder (`wp-content/themes/appiappi-theme/`) is the only thing that
needs to move; WordPress core, plugins, and the database are separate
concerns per environment. Document the actual process here once a host is
chosen.

## 24. Backup Instructions

Not yet defined (no live site exists). Local's own **Backups** tab handles
local dev snapshots in the meantime.

## 25. Troubleshooting

- **Site shows the wrong theme / edits don't appear**: confirm the junction still exists (`Get-Item` on the Local theme path should show `LinkType: Junction`) and that you're editing files in the git repo path, not a copy elsewhere.
- **WP-CLI "missing MySQL extension" error**: you ran the bare `php.exe` without `-c <site php.ini>` — see § 20 for the exact invocation.
- **New CSS/JS not showing**: bump `APPIAPPI_VERSION` in `inc/enqueue.php` (browser cache-busting).

## 26. Known Limitations → Future Improvements

Tracked inline in § 21 and as `TODO(Phase N)` / `TODO(perf)` comments in the
relevant source files (`inc/template-tags.php`, `inc/enqueue.php`) rather
than duplicated here — grep for `TODO` to find them all.

## 27. Phased Build Plan

| Phase | Scope | Status |
|---|---|---|
| 1 | Architecture, design system, theme skeleton, Customizer settings, header, footer, homepage, responsive foundation, docs | **In progress** — homepage done, docs being written now |
| 2 | Pricing CPT, Services/How It Works/About/Contact pages, FAQ, Portfolio, Blog | Not started |
| 3 | Template Library CPT + taxonomy, search/filters, detail pages, selection workflow | Not started |
| 4 | Lead management, admin Settings page, SEO foundation, performance/security hardening | Not started |
| 5 | Customer portal, support system, staff accounts, payment architecture | Not started |

## 28. File Location Map

| Feature | File / Folder | Purpose | How to Modify |
|---|---|---|---|
| Theme header (WP requirement) | `wp-content/themes/appiappi-theme/style.css` | Theme name/version metadata only | Edit the comment block; do not add CSS rules here |
| Design tokens | `assets/css/tokens.css` | Colour/type/spacing/radius/shadow source of truth | Change a variable once, it cascades everywhere |
| Base reset/typography | `assets/css/base.css` | Global element defaults | Edit for site-wide type/element changes |
| Header/footer/site shell layout | `assets/css/layout.css` | `.site-header`, `.mobile-nav`, `.site-footer`, `.container`, `.section` | Edit for structural/shell changes |
| Buttons, badges, chips, cards, forms | `assets/css/components.css` | Reusable UI pieces | Edit for a component used on multiple pages |
| Homepage section styling | `assets/css/home.css` | Hero, pricing cards, template cards, trust bar, final CTA | Edit for homepage-specific visual changes |
| Theme supports / nav locations / image sizes | `inc/setup.php` | `add_theme_support`, `register_nav_menus` | Add new image sizes or theme features here |
| Asset loading | `inc/enqueue.php` | Registers/enqueues all CSS/JS, Google Fonts | Add new stylesheets/scripts here, respecting dependency order |
| Global settings | `inc/customizer.php` | Brand colour, header CTA, contact info, social links, footer tagline | Add new Customizer sections/settings here |
| Icons, nav fallback, placeholder data | `inc/template-tags.php` | `appiappi_icon()`, `appiappi_nav_fallback()`, `appiappi_get_pricing_plans()`, `appiappi_get_featured_templates()`, `appiappi_get_google_rating()` | Add icons to the `$icons` array; replace placeholder functions with CPT queries in Phase 2/3 |
| Homepage assembly | `front-page.php` | Section order | Add/remove `get_template_part()` calls |
| Homepage hero | `template-parts/sections/hero.php` | Headline, chips, CTAs, visual, rating card | Edit copy/layout here |
| Homepage pricing preview | `template-parts/sections/pricing-preview.php` | Renders `appiappi_get_pricing_plans()` | Edit card markup here; edit plan data in `template-tags.php` |
| Homepage template library preview | `template-parts/sections/templates-preview.php` | Renders `appiappi_get_featured_templates()` | Edit card markup here; edit template data in `template-tags.php` |
| Homepage trust bar | `template-parts/sections/trust-bar.php` | 4-item icon strip | Edit `$items` array in the file |
| Homepage final CTA | `template-parts/sections/final-cta.php` | Closing conversion band | Edit copy/links here |
| Site header | `template-parts/header/site-header.php` | Logo, nav, CTA, mobile toggle | Edit markup here; menu items via **Appearance → Menus** once created |
| Site footer | `template-parts/footer/site-footer.php` | 4-column footer | Edit link lists here; contact/social via Customizer |
| Mobile nav + sticky header behaviour | `assets/js/main.js` | Toggle open/close, scroll shadow | Edit here; no dependencies to manage |
