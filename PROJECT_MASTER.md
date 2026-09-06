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
theme slug `appiappi-theme`) provides the permanent shell only: header,
footer, nav menu, theme setup/registration, design tokens, and the base
homepage layout. **Dynamic content sections ship as companion plugins** —
decided per the user's explicit direction. See
[MASTER_PROMPT.md § Companion Plugin Architecture](MASTER_PROMPT.md#companion-plugin-architecture)
for the full spec, and [DEVELOPMENT_LOG.md](DEVELOPMENT_LOG.md) for why.

| Component | Plugin slug | Shortcode | Status |
|---|---|---|---|
| Pricing Plans | `appiappi-pricing-plans` | `[appiappi_pricing]` | **Built** — CPT `appiappi_plan`, native meta box, active on the local site with 4 seeded plans |
| Template / Design Showcase | `appiappi-template-showcase` | `[appiappi_templates count="3" category="" show_sidebar="1"]` | **Built** — CPT `appiappi_template` + taxonomies `appiappi_template_category`/`appiappi_template_style`, active with 13 seeded designs across 6 categories/4 styles; see §13 |
| Hero Slideshow | `appiappi-hero-slider` | `[appiappi_hero_slider]` | **Built** — CPT `appiappi_slide`, native meta box, active with 2 seeded slides; see §11a |
| FAQ | `appiappi-faq` | `[appiappi_faq category="" limit="-1"]` | **Built** — CPT `appiappi_faq` + taxonomy `appiappi_faq_category`, active with the 12 launch questions seeded; see §14a |
| Portfolio | `appiappi-portfolio` | `[appiappi_portfolio count="6" industry=""]` | **Built** — CPT `appiappi_project` + taxonomy `appiappi_portfolio_industry`, active with 3 concept projects seeded; see §14b |
| Contact / Leads | `appiappi-contact` | `[appiappi_contact_form]` | **Built** — CPT `appiappi_lead`, form + spam honeypot + email notification; the one plugin that intentionally does *not* use the shared-render-function pattern (see §14c) |
| Services | `appiappi-services` | `[appiappi_services]` | **Built** (2026-09-06) — CPT `appiappi_service`, native meta box (icon, Hook, Breakdown Items, Closing Line), active with the 6 seeded services; see §14 |

Phase 1.5 build order: theme visuals → Pricing Plans → Template Showcase
→ Hero Slideshow → package as installable zips (not yet done). **Phase 2
build order (this pass): Services/How It Works/About pages → FAQ plugin →
Portfolio plugin → Contact plugin → Blog templates → real Pages + Reading
settings + primary nav menu.** All six companion plugins plus the Blog
templates are now built — see §27 for what Phase 2 leaves incomplete.
Each plugin uses native meta boxes (no ACF/third-party dependency) and
exposes both a shortcode and a plain PHP render function; the theme checks
`shortcode_exists()`/`function_exists()` before calling a plugin so it
never breaks if a plugin isn't installed. The **shared-markup pattern**
(established with Pricing Plans, to reuse for the other two): the theme
owns one render function per section (e.g.
`appiappi_render_pricing_cards( $plans )` in `inc/template-tags.php`) that
takes a plain data array; both the theme's own placeholder data and the
plugin's CPT-backed data get mapped to that same array shape and rendered
through the one function, so card/section markup is never duplicated
between theme and plugin.

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
    ├── themes/
    │   └── appiappi-theme/             Junctioned into the Local site, see § 20
    │       ├── style.css               Theme header block ONLY — no rules (see file's own comment)
    │       ├── functions.php           Bootstrap: requires /inc/ files only
    │       ├── index.php               Fallback template (WP requirement)
    │       ├── front-page.php          Homepage — assembles template-parts/sections/*
    │       ├── home.php                Blog index (used for the `page_for_posts` page — see § 14d)
    │       ├── single.php               Single blog post
    │       ├── archive.php              Category/tag/date archives
    │       ├── page-services.php        page-{slug}.php auto-applies by Page slug — see § 14
    │       ├── page-how-it-works.php
    │       ├── page-about.php
    │       ├── page-contact.php
    │       ├── page-faq.php
    │       ├── page-portfolio.php
    │       ├── page-pricing.php
    │       ├── archive-appiappi_template.php   Website Designs archive (/templates/) — see § 13
    │       ├── single-appiappi_template.php    Website Design detail page — see § 13
    │       ├── header.php              <head>, wp_head, opens <body>, includes site-header part
    │       ├── footer.php              Includes site-footer part, wp_footer, closes </body></html>
    │       ├── inc/
    │       │   ├── setup.php           Theme supports, nav menu locations, image sizes, favicon, dequeues core block CSS
    │       │   ├── enqueue.php         All CSS/JS registration, in dependency order; Google Fonts preconnect+enqueue
    │       │   ├── customizer.php      Native Customizer: brand colour, header CTA, contact info, social links, footer tagline
    │       │   ├── template-tags.php   Icons, nav fallback, page header, pagination, placeholder data + shared render functions (see § 12, § 13, § 11a, § 14a, § 14b)
    │       │   ├── seo.php             Meta/OG/Twitter tags, LocalBusiness JSON-LD, analytics/tracking output, breadcrumbs — see § 16
    │       │   ├── security.php        Baseline hardening (generator tag, XML-RPC, security headers) — see § 15
    │       │   └── admin/settings-page.php   Settings → Appiappi Settings (SEO defaults, analytics IDs, tracking scripts) — see § 8
    │       ├── template-parts/
    │       │   ├── header/site-header.php
    │       │   ├── footer/site-footer.php
    │       │   ├── content/post-card.php    One blog post card, used by home.php + archive.php
    │       │   └── sections/           hero.php, pricing-preview.php, templates-preview.php, trust-bar.php, final-cta.php
    │       └── assets/
    │           ├── css/                tokens → base → layout → components → home.css (front page only) / pages.css (everywhere else) — load order matters, see enqueue.php
    │           ├── js/main.js          Mobile nav, sticky header, hero slider, FAQ accordion — no dependencies
    │           └── images/hero-placeholder.svg, favicon.svg
    └── plugins/                        Each junctioned into the Local site individually, see § 20
        ├── appiappi-pricing-plans/     See § 12
        ├── appiappi-template-showcase/ See § 13
        ├── appiappi-hero-slider/       See § 11a
        ├── appiappi-faq/               See § 14a
        ├── appiappi-portfolio/         See § 14b
        ├── appiappi-contact/           See § 14c
        └── appiappi-services/          See § 14
```

WordPress core (`wp-admin/`, `wp-includes/`, `wp-*.php`) and all non-custom
themes/plugins are intentionally **not tracked** — see `.gitignore`. The repo
root is not the WordPress root; only `wp-content/themes/appiappi-theme/` and
each individual `wp-content/plugins/appiappi-*/` folder inside it correspond
to real paths on the running site.

## 7. Database Structure / Custom Post Types / Custom Taxonomies

**Built:**
- `appiappi_plan` CPT — registered in `wp-content/plugins/appiappi-pricing-plans/includes/cpt.php`. Not public (no single/archive template), managed entirely through wp-admin. Meta keys (all prefixed `_appiappi_plan_`): `price`, `period`, `note`, `color` (one of `starter`/`business`/`professional`/`growth`, mapped to the matching CSS token), `icon` (one of a curated set — see `appiappi_pricing_icon_options()`), `featured` (0/1), `badge`, `cta_text`, `cta_url`, `features` (newline-separated string, split on render). Ordering uses native `menu_order` (`page-attributes` support → the classic "Order" field).
- `appiappi_template` CPT — registered in `wp-content/plugins/appiappi-template-showcase/includes/cpt.php`. Not public, uses the native Featured Image as the design preview (`post-thumbnails` support). Meta keys (prefixed `_appiappi_template_`): `desc`, `price`, `rating`, `rating_count`, `demo_url`, `details_url`, `vendor`, `source_url` (the last two exist specifically so third-party designs credit their real source, per [MASTER_PROMPT.md § Website Template Library](MASTER_PROMPT.md#website-template-library)). Two taxonomies: `appiappi_template_category` (hierarchical; each term has an `icon` term-meta field set via custom add/edit-term fields in `includes/taxonomy-meta.php`, from the same curated icon set as the theme) and `appiappi_template_style` (flat tags, e.g. Modern/Minimal/Bold/Classic).
- `appiappi_slide` CPT — registered in `wp-content/plugins/appiappi-hero-slider/includes/cpt.php`. Not public, post Title = the slide's headline (H1), Featured Image = the slide's visual. Meta keys (prefixed `_appiappi_slide_`): `subheadline`, `cta_text`, `cta_url`, `image_alt` (optional — decorative images can leave this blank since the headline conveys the meaning). Ordering uses native `menu_order`.
- `appiappi_faq` CPT — registered in `wp-content/plugins/appiappi-faq/includes/cpt.php`. Not public, post Title = question, native editor content = answer. Taxonomy `appiappi_faq_category` (flat). Ordering uses native `menu_order`.
- `appiappi_project` CPT — registered in `wp-content/plugins/appiappi-portfolio/includes/cpt.php`. Not public, post Title = project name, native editor content = description, Featured Image = project photo. Meta keys (prefixed `_appiappi_portfolio_`): `client`, `location`, `external_url`, `services`, `results`, `is_concept`. Taxonomy `appiappi_portfolio_industry` (flat). **Note:** registered as `appiappi_project`, not the longer `appiappi_portfolio_project` — see §14b for why.
- `appiappi_lead` CPT — registered in `wp-content/plugins/appiappi-contact/includes/cpt.php`. Not public, no front-end template (admin-only). Meta keys (prefixed `_appiappi_lead_`): `email`, `business`, `phone`, `website`, `interested_service`, `selected_design`, `selected_plan`, `source`, `message`, `status`. See §14c.
- `appiappi_service` CPT — registered in `wp-content/plugins/appiappi-services/includes/cpt.php`. Not public, no `editor` support (every field, including the "Hook", renders as plain escaped text, so a plain textarea fits better than the rich-text editor). Meta keys (prefixed `_appiappi_service_`): `icon` (curated set — `appiappi_services_icon_options()`), `hook`, `breakdown` (newline-separated string, any number of items, split on render), `closing`. Ordering uses native `menu_order`. Added 2026-09-06.

Planned:
- **Case Study** CPT (Phase 2/3 follow-up, not built this pass)

## 8. Settings

**Site Domain** (Settings → Appiappi Settings, added 2026-09-06): plain text, default `appiappi.com` when empty. Used anywhere the domain needs to appear as plain display text (currently: the Privacy Policy and Terms of Service pages' "Website:" mentions) — deliberately **not** used for internal links, which always go through `home_url()`/`site_url()` so they keep working regardless of environment (local dev vs. the real production domain).

Implemented now via the native Customizer (`inc/customizer.php`):

| Section | Settings |
|---|---|
| Brand Colour | Primary colour (hex) — cascades to `--color-primary`, `--color-primary-dark`, `--color-primary-50` via inline `wp_head` CSS |
| Header Call to Action | Button text + URL (used in header and mobile nav) |
| Contact Information | Phone, email, address — used only by `inc/seo.php`'s LocalBusiness schema now (the footer's Contact column was switched to read the Contact Page Info Box fields instead, 2026-09-06 — see below and §11) |
| **Contact Page Info Box** (added 2026-09-06) | Google Maps Embed URL, Address label/value, Phone label/value/"links to" type (Call/SMS/WhatsApp/None), Support Email — drives the info card next to the form on the Contact page (`page-contact.php`) **and** the footer's Contact column (map excluded there) sitewide, §11. Each row is independently optional; the Contact page's card is omitted entirely (form goes full width) if every field is empty, and the footer's Contact column falls back to General Public Email (Settings → Appiappi Settings) or is omitted too. |
| Social Links | Facebook, LinkedIn, Instagram, YouTube — used in footer |
| **Layout Spacing** (added 2026-09-06) | Hero/Pricing preview/Website Designs preview/Footer desktop side padding (px, range sliders) — see §9 |
| Footer | Footer tagline text |

Logo/favicon use core's built-in `custom-logo` theme support and Site Icon —
no custom code needed; set both under **Appearance → Customize → Site
Identity**.

**Advanced/technical settings (Phase 4)** live on a separate native
Settings API page — **Settings → Appiappi Settings**
(`inc/admin/settings-page.php`), stored as one option (`appiappi_settings`)
read via `appiappi_get_setting( $key, $default )`: Default SEO Title,
Default Meta Description, Google Analytics Measurement ID, Google Search
Console verification code, Meta Pixel ID, Business Hours, Currency
Symbol/Code, and raw Header/Footer Scripts. Deliberately a separate page
from the Customizer — these are back-office/technical fields with no live
preview, not visual brand settings. The Header/Footer Scripts fields are
saved and echoed **unescaped by design** (their whole purpose is to inject
admin-supplied markup); access is gated by the Settings API's own
`manage_options` capability check, not by escaping.

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
- **Layout**: `--container-max` (1600px), `--container-pad` (mobile `--space-5`/20px; desktop, ≥768px, a deliberately tight **10px** — sitewide gutters were widened 2026-09-06 from a 1200px/32px combo to hug the viewport edges like themeforest.net, per explicit request. Per-row item counts in every grid (pricing cards, template grid, etc.) are controlled by their own `--cols`/`--pricing-cols`/`--template-cols` custom properties, not by container width — widening the container only makes each column/card wider, never changes how many fit per row.) Both drive the single `.container` class (`layout.css`), used by every section/page.
- **Per-section desktop padding overrides (added 2026-09-06):** the sitewide 10px gutter above was judged too tight for four specific spots, which each get their own larger, **admin-configurable** desktop-only side padding instead — Customizer → **Layout Spacing**: Hero (`--hero-pad-desktop`, default 30px, applied at hero's own ≥1024px breakpoint since that's where it becomes two-column), Pricing preview (`--pricing-preview-pad-desktop`, default 20px, `#pricing > .container`, home.css only), Website Designs preview (`--templates-preview-pad-desktop`, default 20px, `#templates > .container`, home.css only), and Footer (`--footer-pad-desktop`, default 50px, applied to both `.final-cta` and `.site-footer .container` — treated as one closing "footer area" since Final CTA sits directly above the footer on every page). All four are `range` controls (0–120px) in `inc/customizer.php`, injected via the same inline `wp_head` `<style>` block that already carries `--color-primary`. Mobile padding is untouched everywhere.

The admin-chosen primary colour (Customizer) overrides `--color-primary` at
runtime via an inline `<style>` block injected in `wp_head` — see
`appiappi_customizer_css_vars()` in `inc/customizer.php`.

## 10. Components

Generic, reusable pieces live in `assets/css/components.css`: `.btn` (+
`-primary/-secondary/-outline-inverse/-link`, `-sm`, `-block`), `.badge`,
`.chip` (hero feature chips), `.icon-tile` (trust-bar items, centred
2026-09-06), `.card`, `.final-cta` (moved here 2026-09-06 — it's
rendered on every page via `get_template_part()`, not just the
homepage; see §11), and form field styles. Section-specific layout that
is genuinely homepage-only (hero, trust bar grid) lives in
`assets/css/home.css` — **before adding anything there, confirm the
markup is truly only ever rendered on the front page**; this file is
only enqueued when `is_front_page()` is true (`inc/enqueue.php`), and
three separate bugs this project has already hit (pricing cards,
template showcase, final CTA) were exactly this mistake — shared markup
styled in a page-conditional file, rendering completely unstyled
everywhere else.

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
  Social fields pull from the Customizer and simply don't render when empty.
  **Services column (rewritten 2026-09-06):** built dynamically from
  `appiappi_services_get_services()` (real CPT data, §14) or the theme's
  `appiappi_get_services()` placeholder — each link is `/services/#service-{id}`,
  landing exactly on that service's block. Replaces a previously
  hard-coded, incomplete 4-of-6 link list whose anchors didn't even exist
  on the page yet. **Contact column (rewritten 2026-09-06):** reuses the
  same Customizer fields as the Contact page's info box ("Contact Page
  Info Box" — address, phone with its "links to" type, support email;
  the map is Contact-page-only) via the shared `appiappi_contact_phone_href()`
  helper (`inc/template-tags.php`), so both places always show identical
  details. If none of those are set, falls back to just the **General
  Public Email** (Settings → Appiappi Settings → Legal & Company
  Information); if that's empty too, the whole column is omitted rather
  than showing a "please configure this" placeholder.
- **Bug found + fixed 2026-09-06:** `.final-cta`'s CSS (the closing band
  rendered via `get_template_part( 'template-parts/sections/final-cta' )`
  on *every* page) lived entirely in `home.css` — the exact same
  front-page-only-enqueue bug already hit twice before (pricing cards,
  template showcase; see §12, §13). It rendered completely unstyled
  (no centering, no dark background, plain text) on every page except
  the homepage. Moved to `components.css`; also gained the configurable
  `--footer-pad-desktop` side padding described in §9, shared with the
  footer's own container since the two form one visual "footer area."

## 11a. Homepage Hero

**Built as the `appiappi-hero-slider` companion plugin** (§5, §7).
`template-parts/sections/hero.php` calls the plugin's
`[appiappi_hero_slider]` shortcode via `shortcode_exists()` when active,
falling back to the theme's single-slide `appiappi_get_hero_slides()`
otherwise; both paths render through the shared
`appiappi_render_hero_slides( $slides )` function in `inc/template-tags.php`.

**What rotates per slide:** headline (H1), subheadline, visual image, and
the primary CTA button (text + URL). **What stays constant across every
slide** (simply repeated inside each slide's markup, since only one slide
is ever visible): the "Canadian Web Design & SEO" eyebrow, the 4 feature
chips, the "View Our Plans" secondary CTA, and the Google-rating card
(which still never fabricates a rating — `appiappi_get_google_rating()`).
If a slide has no Featured Image set, the render function falls back to
the theme's placeholder skyline SVG rather than showing a broken image.

**Behaviour:** plain CSS/JS, no slider library. A single slide renders no
dots and no JS runs at all (`assets/js/main.js` bails out if there are
fewer than 2 `.hero-slide` elements). With 2+ slides: pill-shaped dot
navigation, auto-advance every 7s, pauses on hover/focus, and the
auto-advance is skipped entirely under `prefers-reduced-motion`. The local
site has the plugin active with 2 seeded slides — manage them under
**Hero Slides** in wp-admin (Featured Image = slide visual, Page
Attributes → Order = slide sequence).

**Content column centred (added 2026-09-06):** `.hero__content` (eyebrow,
title, lede, chip-list, CTA buttons, dots — everything except the
visual/slides column on the right) is now centre-aligned as a unit, per
explicit request — `home.css`. The chip-list is capped to `max-width:
fit-content` + `margin-inline: auto` so the 2×2 feature-chip block
centres as one compact unit rather than stretching to the column's full
width (each chip's own icon+label row stays left-aligned internally).
Side padding on the hero's own ≥1024px desktop breakpoint is
admin-configurable — see §9's Layout Spacing note.

## 12. Pricing System

**Built as the `appiappi-pricing-plans` companion plugin** (§5, §7). The
homepage pricing section (`template-parts/sections/pricing-preview.php`)
and the dedicated `page-pricing.php` (slug `pricing`) both call the
plugin's `[appiappi_pricing]` shortcode via `shortcode_exists()` when
active, falling back to the theme's `appiappi_get_pricing_plans()` static
array (`inc/template-tags.php`) otherwise. Both paths render through the
shared `appiappi_render_pricing_cards( $plans )` function so card markup
lives in exactly one place.

**5 plans now exist**, each carrying (beyond the original price/period/
note/features): `tagline` (a short punchy line under the plan name),
`audience` ("Perfect for…", shown above the feature list), `value_driver`
(a one-line ROI statement, shown as a highlighted callout above the CTA
button), `group` (`launch` or `growth` — which section of the Pricing
page a plan appears under), and `homepage_visible` (checkbox, default on
— unchecking keeps a plan available on the full Pricing page only).
Rewritten 2026-09-06 per the user's pricing-strategist brief — premium/
authoritative language ("Optimised", "Managed", "Strategic"), explicit
audience targeting per plan, and value-driver ROI framing.
**Dollar amounts were deliberately left unchanged** ($199/$399/$699 one-
time, $599/mo Growth, $899/mo SEO Growth) — the brief's "justify higher
investment" was treated as a copy/positioning instruction, not a request
to actually raise prices, since changing real pricing is a business
decision that needs the user's explicit sign-off, not an inference from
stylistic language (see DEVELOPMENT_LOG.md).

- **Launch Tiers** (`group = launch`, one-time): Starter $199, Business $399, Professional $699 ("Most Popular").
- **Growth Tiers** (`group = growth`, monthly): Growth $599/mo (`homepage_visible = true`), **SEO Growth $899/mo (new — `homepage_visible = false`, Pricing page only)**, matching the original spec's "may not need to be publicly visible at launch" guidance for this tier.
- `[appiappi_pricing]` shortcode attributes: `homepage_only="1"` (only plans with `homepage_visible` checked — homepage teaser), `group="launch"`/`group="growth"` (Pricing page's two sections), `show_description="1"` (renders the plan's full description — Pricing page only), `link_to_pricing="1"` (CTA buttons link to `/pricing/#plan-{id}` instead of the plan's own `cta_url` — homepage teaser only).

**Full descriptions + homepage → Pricing anchor linking (added 2026-09-06):**
- `appiappi_plan` CPT gained `editor` support — the main content editor holds a fuller paragraph description of the plan, separate from the short `note`/`tagline`/`audience`/`value_driver` fields. Mapped into the `description` array key (rendered via `the_content` filter in the plugin, or a plain string in the theme placeholder).
- `appiappi_render_pricing_cards( $plans, $show_description = false, $link_to_pricing = false )` — the theme's shared renderer — only outputs `.pricing-card__description` when `$show_description` is true (Pricing page passes `true`; the homepage teaser leaves it `false`, per the user's explicit "don't need it on the homepage" instruction), and every card gets an anchor `id="plan-{id}"` regardless of context.
- When `$link_to_pricing` is true (homepage teaser only), every plan's CTA button href becomes `home_url( '/pricing/#plan-' . $plan['id'] )` instead of the plan's own `cta_url` — clicking "Choose Starter" on the homepage jumps straight to that plan's full card (with description) on the Pricing page. On the Pricing page itself, CTAs keep using the real `cta_url` (currently `#contact`; this is the button that will eventually become a real order/checkout action once payment processing is built — see [MASTER_PROMPT.md § Payment Architecture](MASTER_PROMPT.md#payment-architecture), still Phase 5/future).
- Colour is not a separate concern here — both pages already read the same `color` field from the same CPT/placeholder data through the same shared renderer, so per-plan colour has always matched between the homepage and the Pricing page.
- `.pricing-card` gained `scroll-margin-top` so the sticky header doesn't cover the target card when landing on a `#plan-*` anchor.

**Per-feature descriptions, 12 colours, yearly billing, configurable columns, solid-colour CTAs (added 2026-09-06):**
- **Feature descriptions**: the Features textarea's format is now `Feature Name | Optional description` (one per line) — `appiappi_pricing_parse_features()` in the plugin splits each line on the first `|` into `['name' => ..., 'desc' => ...]`. `appiappi_render_pricing_cards()` renders `desc` as smaller muted text under the feature name when present, and stays backward-compatible with plain-string features (name only, no description) from the theme's placeholder fallback.
- **12 plan colours**: `appiappi_pricing_color_options()` now lists 12 choices — the original 5 keep their plan-tied keys/labels (`starter`, `business`, `professional`, `growth`, `seo-growth`) for backward compatibility with already-saved plans, plus 7 new generic ones (`red`, `pink`, `indigo`, `amber`, `cyan`, `lime`, `slate`) any plan can use. New tokens in `tokens.css`: `--color-plan-red/pink/indigo/amber/cyan/lime/slate`.
- **Billing Frequency**: the free-text "Billing Period" field was replaced with a `Billing Frequency` dropdown (One-time / Monthly / Yearly). Selecting an option writes both `_appiappi_plan_billing_frequency` (the raw key) and `_appiappi_plan_period` (the display suffix — "one-time" / "/ month" / "/ year") so the renderer's `period` field needs no changes. Plans saved before this dropdown existed get a best-guess frequency inferred from their existing free-text period (`appiappi_pricing_infer_billing_frequency()`) shown as the pre-selected dropdown value until re-saved.
- **Configurable columns per row**: new **Pricing Plans → Display Settings** admin page (`includes/settings.php`) with one setting, `appiappi_pricing_columns` (1–6, default 4) — "how many plan cards sit side by side on desktop." `appiappi_render_pricing_cards( $plans, $show_description, $link_to_pricing, $columns = null )` reads this option when `$columns` isn't explicitly passed, and injects it as an inline `--pricing-cols` CSS custom property on `.pricing-grid`. `[appiappi_pricing columns="N"]` can override it per shortcode instance. `.pricing-grid` is `display:flex; flex-wrap:wrap; justify-content:center`, and each `.pricing-card`'s width is `calc((100% - (cols-1)*gap)/cols)` — so a short last row (e.g. 2 cards when the setting is 4) centres on the page instead of stretching edge-to-edge or leaving a lopsided gap, while card *text* stays left-aligned as normal. Tablet (≥640px) always caps at `min(setting, 2)` regardless of the desktop setting, since these cards are too content-heavy to comfortably fit 3+ at that width; mobile is always 1 per row.
- **Solid-colour CTA buttons on the Pricing page**: `appiappi_render_pricing_cards()` now uses `btn-primary` (solid, filled with `--plan-color`) for *every* card when `$show_description` is true (i.e. on the Pricing page), not just the featured one — the future order/checkout button should read as an equally-weighted real action per plan. The homepage teaser (`$show_description = false`) is unchanged: only the featured plan gets `btn-primary`, others stay `btn-secondary` (outline).
- **Themed description box**: `.pricing-card__description` is now a tinted callout (`color-mix(in srgb, var(--plan-color) 8%, white)` background, a `--plan-color`-tinted border, rounded corners) with an "About This Plan" eyebrow label in the plan's colour — matching the plan's own colour identity rather than a plain bordered paragraph.

**`page-pricing.php`** (rewritten 2026-09-06): Launch Tiers section →
Growth Tiers section → a "How Our Pricing Works" explainer (Setup Fees
cover the one-time build; Monthly Subscriptions cover ongoing
management/growth) → the FAQ accordion (per
[MASTER_PROMPT.md § Pricing Page](MASTER_PROMPT.md#pricing-page) —
**intentionally left untouched** in the 2026-09-06 rewrite per the user's
explicit instruction) → the shared final CTA.

**One plan per row on the Pricing page (added 2026-09-06):** both the
Launch Tiers and Growth Tiers `[appiappi_pricing]`/`appiappi_render_pricing_cards()`
calls in `page-pricing.php` now hard-code `columns="1"` / `columns: 1`,
overriding the global "Plans Per Row" admin setting on this page only —
each plan fills the full container width and stacks in its own row
rather than sitting side by side. The homepage teaser
(`template-parts/sections/pricing-preview.php`) is untouched and keeps
using the admin-configured column count.

## 13. Template Library

**Built as the `appiappi-template-showcase` companion plugin** (§5, §7).
`template-parts/sections/templates-preview.php` calls the plugin's
`[appiappi_templates]` shortcode via `shortcode_exists()` when active
(falling back to the theme's `appiappi_get_featured_templates()` +
`appiappi_get_template_categories()` + `appiappi_get_template_styles()`
placeholders otherwise), and both paths render through the shared
`appiappi_render_template_showcase( $templates, $categories, $styles, $show_sidebar, $columns = null, $total = null )`
function in `inc/template-tags.php`. `$columns` defaults to the
**Website Designs → Display Settings** option (see below) whenever
`$show_sidebar` is true — which both the homepage teaser and the
`/templates/` archive are, so they always render the identical
sidebar+grid layout the user asked for ("همینجا که در صفحه اول داره
نمایش میده... کنارش هم مثل همین صفحه اول گروهبندی‌ها"), sharing one
admin-configurable column count. `$total`, when given (only the archive
page passes it), renders "of N total" next to the per-page count.

**Now complete (Phase 3).** `appiappi_template` is a **public** CPT with
`has_archive => 'templates'` and `rewrite => ['slug' => 'templates']` — so
`/templates/` and `/templates/{design-slug}/` are real WordPress URLs, not
a shortcode embedded in a static Page.

- **`archive-appiappi_template.php`** — the full browsable library. Runs
  the **native main query/loop** (`have_posts()`/`the_post()`), so it
  paginates like the blog archive rather than dumping every design onto
  one page. The plugin's `appiappi_showcase_archive_query()`
  (`includes/cpt.php`, on `pre_get_posts`) sets `posts_per_page` to the
  admin-configured **columns × rows-per-page** (added 2026-09-06 —
  **Website Designs → Display Settings**: "Designs Per Row" default 3,
  "Rows Per Page" default 4, so 12 designs per page by default) and
  applies the `?appiappi_category=<slug>` filter as a real `tax_query` on
  that same main query, so `appiappi_pagination()` (which reads
  `$wp_query` directly) works for free. Beyond the first page, WordPress's
  standard `/templates/page/2/` archive pagination URLs apply. **Style
  checkboxes and the search box are live, client-side JS**
  (`assets/js/main.js`, third IIFE) filtering within the current page's
  results — every card already carries `data-style`/`data-search`
  attributes from the shared render function, so filtering needs no AJAX
  round trip; the visible count and an empty-state message update live
  too.
- **`single-appiappi_template.php`** — the detail page: category badge,
  featured image, a real content-editor description (falls back to the
  short "Short Description" meta field if the editor is empty — the CPT
  gained `editor` support for this), rating/price, original vendor credit
  + source link when set, Live Demo button, and **"Choose This Design"**
  — the selection workflow entry point (§14c).
- `appiappi_showcase_map_post( $post )` (in the plugin's `includes/shortcode.php`)
  is the one place a template post gets turned into the render-ready
  array shape — used by the shortcode's query loop AND both new
  templates, so nothing is duplicated.

The local site has the plugin active with 6 categories, 4 styles, and 13
seeded designs — the original 3 placeholder concepts (Construction Pro /
Justice Law / Dental Clinic) plus **10 real Real Estate designs added
2026-09-06** by pulling the top 10 (sorted by date) from ThemeForest's
`wordpress/real-estate` category via the Envato **search** API
(`api.envato.com/v1/discovery/search/search/item?site=themeforest.net&category=wordpress/real-estate&sort_by=date`
— a different endpoint/schema than the single-item lookup `price-sync.php`
uses; its `rating` field is a nested `{rating, count}` object here, unlike
the flat fields on `catalog/item`). Each of those 10 carries its real
price, rating, ThemeForest Details Page URL, the theme's own real
external demo URL (from the item's `demo-url` attribute, not the Envato
preview link), the vendor's username, and its actual marketplace preview
screenshot downloaded and set as the Featured Image
(`media_sideload_image()`) — nothing fabricated. Since these now carry
real Details Page URLs, they're automatically picked up by the
Price & Rating Sync's regular cron cycle from here on. Style wasn't set
for these 10 (not requested, and not something reliably inferable from
the API data) — manage all designs under **Website Designs** in
wp-admin.

**CSS + layout polish (added 2026-09-06):** all of `.templates-layout`,
`.templates-sidebar*`, `.templates-main*` and `.template-grid`/
`.template-card*` moved from `home.css` (front-page-only) to
`components.css` (loaded on every page) — same bug/fix as the pricing
cards (§12): this markup is shared with `/templates/`, a regular Page
template, which never loaded `home.css` and so rendered completely
unstyled there until this move. `.template-grid` also switched from CSS
Grid to the same flexbox `--cols` pattern as `.pricing-grid`, so a short
last row (e.g. 1 design alone on the final row) centres on the page
instead of leaving a lopsided gap. The homepage-only "Browse All
Designs" footer button (`.templates-preview__footer`) now only renders
when NOT on the `/templates/` archive itself (`! is_post_type_archive( 'appiappi_template' )`),
since there it was a redundant self-link now superseded by real
pagination — this check is independent of `$show_sidebar` since both the
homepage teaser and the archive render with the sidebar shown.

**Price & Rating Sync (added 2026-09-06):** each design's `price`/`rating`/`rating_count`
meta can drift out of date against its real third-party listing (currently
only `Construction Pro` has a real Details Page URL — a ThemeForest item;
the other two seeded designs' Details/Demo URLs are still `#` placeholders).
Rather than opening the listing page in a browser to check — which sits
behind ThemeForest's own Cloudflare bot-protection and cannot and should
not be automated around — `includes/price-sync.php` calls the **official
Envato API** (`api.envato.com/v3/market/catalog/item`) directly:
- `appiappi_showcase_extract_envato_item_id( $url )` pulls the numeric
  item ID from a Details Page URL, validated against a known list of
  Envato marketplace domains (`appiappi_showcase_envato_domains()`) —
  anything else (a `#` placeholder, a non-Envato URL) is silently
  skipped, not treated as an error.
- `appiappi_showcase_sync_one_item( $post_id, $token )` fetches that
  item and updates `_appiappi_template_price`/`_appiappi_template_rating`/
  `_appiappi_template_rating_count` only where they actually differ,
  logging what changed.
- **Website Designs → Price & Rating Sync** (new admin page): stores
  the Envato Personal Token (`appiappi_showcase_envato_token` — the
  user generates this free at build.envato.com; default scopes are
  enough, it only reads public catalogue data), a "Run Sync Now"
  button (checks every design in one pass — fine at the current small
  scale), and a table of the last run's per-design results.
- **Automatic background sync**: a custom 15-minute WP-Cron interval
  (`appiappi_showcase_price_sync_cron`, registered on plugin activation
  and re-armed on `admin_init` if somehow unscheduled) processes
  `APPIAPPI_SHOWCASE_SYNC_BATCH_SIZE` (50) designs per run from a
  saved, wrapping cursor — this was the whole point: the user explicitly
  flagged that the catalogue may grow to ~2,000 designs and manually
  checking each one daily isn't feasible, and a naive "check everything
  every night" job risks timing out a normal page-load-triggered cron
  run at that scale. 50-per-15-minutes cycles a ~2,000-item catalogue
  roughly every 10 hours without ever making more than a few dozen API
  calls (with 1-second pacing between them) in one PHP request.
- **Verified against a live response (2026-09-06).** The real schema
  differs from what the v3 docs implied: `rating` is a **flat float**
  and `rating_count` a **separate top-level int** — not the nested
  `{ rating: { rating, count } }` object originally guessed.
  `appiappi_showcase_parse_envato_item()` was corrected to match; see
  DEVELOPMENT_LOG.md. `price_cents` was correct as originally written.
  Confirmed end-to-end on `Construction Pro`'s real listing (Envato
  item 61829280 — "Inotek", a different theme than its `demo_url`
  happens to point at, which is fine: `demo_url` and `details_url` are
  independent fields and only `details_url` drives this sync): price
  corrected `$59 → $22`, rating `4.9 → 4.4`, rating count `128 → 5`.

## 14. Services / How It Works / About / Privacy Policy / Terms Pages

Static-content page templates in the theme root (auto-applied via the `page-{slug}.php` naming convention to Pages with matching slugs — no manual "Page Attributes → Template" selection needed, though each also declares a `Template Name:` header so it can be assigned to a differently-slugged page too):

- **`page-services.php`** (slug `services`) — full-width service blocks, one per service. **Converted to the `appiappi-services` companion plugin (2026-09-06)**: calls `[appiappi_services]` via `shortcode_exists()` when active, falling back to the theme's `appiappi_get_services()` placeholder otherwise — both paths render through the shared `appiappi_render_services( $services )` in `inc/template-tags.php`, same pattern as every other section. Each service carries `icon`, `hook` (a punchy ~30–40 word benefit statement), `breakdown` (as many concrete sub-task bullets as that service needs — no fixed count, unlike the original 4–6-item static array), and `closing` (a short line bridging "service" to "partner", rendered as a tinted callout). Every service block gets `id="service-{id}"` (the CPT post's slug, or the placeholder's hand-set `id` key) so the footer's Services column (`site-footer.php`) can link straight to the right one — `href="/services/#service-{id}"`, built dynamically from the same data source instead of a hard-coded, previously-incomplete/mismatched link list. Content rewritten 2026-09-06 per the user's copywriting brief before the plugin conversion — Canadian spelling (optimisation, colour where it comes up), "we"/"you" partnership framing, "foundational business infrastructure" positioning in the page intro; the local site has all 6 services seeded as real posts (Website Design, Website Management, SEO, Content Management, Managed Hosting, Website Support), manageable under **Services** in wp-admin.
- **`page-how-it-works.php`** (slug `how-it-works`) — 6 numbered steps from `appiappi_get_how_it_works_steps()`, same pattern. Each step carries 4 fields: `title`, `we_do` (the specific actions the agency takes), `you_provide` (what the client needs to hand over, or "nothing further" where true), and `benefit` (why the step matters to the client, styled as a highlighted callout) — rewritten 2026-09-06 per the user's UX-copywriter brief. Ends with a page-specific closing CTA ("Ready to Get Started?") rather than the generic shared one.
- **`page-about.php`** (slug `about`) — genuinely admin-editable: renders `the_content()` from the real WordPress Page, not a static array, since this is editorial copy the business owner should be able to rewrite without touching code. Content rewritten 2026-09-06 from the user's draft (cleaned up a handful of copy-paste corruption/duplication bugs in that draft — a garbled "What We Do" bullet list, a doubled "Practical Strategy" sentence — while keeping the structure and wording otherwise as given).
- **`page-privacy-policy.php`** (slug `privacy-policy`, publishes over WordPress core's own auto-created draft Privacy Policy page) and **`page-terms.php`** (slug `terms`, new Page created 2026-09-06) — added 2026-09-06. Unlike About, these are **hard-coded PHP templates, not `the_content()`** — this is legal boilerplate, not marketing copy — but every business-specific fact (legal name, incorporation province, address, emails, privacy officer, payment method/provider, exact cancellation policy, support response time, data retention period, portfolio display policy, final ownership details) is pulled live from **Settings → Appiappi Settings → Legal & Company Information** via `appiappi_get_setting()` (see §8), never hard-coded. A field left empty omits the sentence/row that depends on it (e.g. no "Privacy Contact:" row) rather than shipping a `[Insert ...]` placeholder in a published legal page; a few fields (governing law, cancellation policy, portfolio policy) fall back to generic-but-still-legally-sound wording instead. Both Page objects' own `post_content` just holds an admin-facing note explaining that the real copy lives in these templates, not the editor — editing the Page content in wp-admin does nothing to the front-end output. Content originally drafted by the user; cleaned up a few copy-paste corruption bugs in that draft in the same pass (a merged sentence/heading, a duplicated bullet, a dropped word) while keeping the wording and structure otherwise as given.

All five share a `.page-header` band (title + optional subtitle) via `appiappi_page_header( $subtitle )` in `inc/template-tags.php`, the `.single-post`/`.single-post__content` prose container (also used by blog posts — gained real `ul`/`ol` bullet/number styling 2026-09-06, since `base.css` strips list markers sitewide for the component system but long-form prose pages need them back), and end with the homepage's `final-cta` section.

## 14a. FAQ System

**Built as the `appiappi-faq` companion plugin.** CPT `appiappi_faq` (post title = question, native editor content = answer — rich text, no custom meta field needed) + flat taxonomy `appiappi_faq_category`. Shortcode `[appiappi_faq category="" limit="-1"]`. `page-faq.php` (slug `faq`) and `page-pricing.php` both call it via `shortcode_exists()`, falling back to the theme's `appiappi_get_faqs()` placeholder (the original 12 launch questions) otherwise — both paths render through the shared `appiappi_render_faq( $faqs )` in `inc/template-tags.php`. Accordion open/close is a small IIFE in `assets/js/main.js` (multiple items can be open at once — no single-open enforcement).

## 14b. Portfolio System

**Built as the `appiappi-portfolio` companion plugin.** CPT `appiappi_project` (title, native editor content = description, Featured Image = project photo) + flat taxonomy `appiappi_portfolio_industry`. Meta (prefixed `_appiappi_portfolio_`): `client`, `location`, `external_url`, `services`, `results`, and `is_concept` (a checkbox — per the project rule to never fabricate results, a project can be explicitly marked as an illustrative concept rather than a real client engagement; the theme renders a "Concept" badge over the image when checked). Shortcode `[appiappi_portfolio count="6" industry=""]`. `page-portfolio.php` (slug `portfolio`) calls it via `shortcode_exists()`, falling back to `appiappi_get_portfolio_projects()` (3 concept placeholders) otherwise — both via the shared `appiappi_render_portfolio_grid( $projects )`.

**Scope note:** before/after image pairs and a full screenshot gallery (both mentioned in the original spec) are deferred — a single Featured Image covers the MVP. Add a gallery meta field (array of attachment IDs) later if needed.

**Note on the CPT name:** registered as `appiappi_project`, not `appiappi_portfolio_project` — WordPress silently fails to insert posts (`post_type` column overflow) for post-type names over 20 characters; the taxonomy name has no such limit (32 chars) so it kept its longer, clearer name.

## 14c. Contact Form & Lead Management

**Built as the `appiappi-contact` companion plugin** — the one companion plugin that does **not** follow the shared-theme-render-function pattern the other five use, on purpose: an inert placeholder form with no working handler behind it would be actively misleading, so when this plugin is inactive, `page-contact.php` shows a simple mailto/phone message (from the Customizer contact fields) instead of a fake copy of the form.

- CPT `appiappi_lead` — every submission becomes one Lead (title `"{name} — {business}"`), fully visible/manageable in wp-admin (`_appiappi_lead_*` meta: email, business, phone, website, interested_service, selected_design, selected_plan, source, message, status). A meta box shows all fields read-only except **Status** (New/Contacted/Qualified/Proposal/Won/Lost — editable, saved via `save_post_appiappi_lead`).
- Shortcode `[appiappi_contact_form]` renders the form (name, business, email, phone, current website, interested service, message — per [MASTER_PROMPT.md § Forms & Lead Management](MASTER_PROMPT.md#forms--lead-management)) with a honeypot field for basic spam protection. **Province, Business Type, Budget Range and Preferred Launch Date were removed (2026-09-06)** per explicit user request — the form, the submission handler, and the Lead Details meta box no longer capture or display them (existing Leads submitted before this change may still carry that old meta in the database; it's simply no longer shown or collected).
- **Selection workflow (Phase 3, § Website Template Library)**: a template's detail page "Choose This Design" button links to `/contact/?design=<name>&plan=<slug>`. The shortcode reads those, shows a "You selected: X — Recommended plan: Y" banner above the form, and carries them through as hidden fields into the Lead's `selected_design`/`selected_plan` meta (`source` meta becomes `template_selection` instead of `contact_form`). No separate form/page was built for this — it reuses the same Contact form and Lead system, just pre-filled and tagged.
- Submission handling lives on `template_redirect` (`includes/handler.php`), **not** in the shortcode callback — a shortcode runs too late in the page lifecycle to `wp_safe_redirect()` before output starts. Verifies the nonce, validates required fields (name/email/message), silently no-ops obvious bot submissions (honeypot filled) while still redirecting to the success state, creates the Lead + meta, sends an admin notification email (`wp_mail`, Reply-To set to the submitter), then does a Post/Redirect/Get redirect back to the referring page with `?appiappi_contact=success` or `=error` — so a page refresh never resubmits the form.
- **Known UX gap:** on a validation error, the redirect does not preserve the visitor's entered values — they have to re-type the form. Acceptable for this pass; would need a transient or session to fix properly.

## 14d. Blog

Native WordPress posts/categories/tags — no companion plugin needed, this is core WP functionality. Reading settings are configured so the site root stays `front-page.php` (via `page_on_front` pointed at an otherwise-empty "Home" page — `front-page.php` always wins over `home.php` for the literal site root regardless of this setting, so the homepage is unaffected) while a separate "Blog" page (`page_for_posts`) at `/blog/` shows the real posts index.

- `home.php` — the blog index (used for the `page_for_posts` page). Paginated grid via `template-parts/content/post-card.php` (title, date, category, featured image, excerpt) + `appiappi_pagination()`.
- `archive.php` — category/tag/date archives, same post-card grid, title via `get_the_archive_title()`.
- `single.php` — individual post: title, date/author meta, featured image, `the_content()`.
- `appiappi_pagination()` (`inc/template-tags.php`) wraps `paginate_links()` in `.appiappi-pagination` to match the design system instead of WP core's default pagination markup.

WordPress's default "Hello world!" post, "Sample Page", and the default comment were deleted during setup — they're not somehow blocked from being recreated, just removed as one-time cleanup.

## 15. Security

Standard WordPress escaping/sanitization conventions are followed
throughout the theme and every plugin (`esc_html`, `esc_attr`, `esc_url`,
`sanitize_text_field`, `sanitize_hex_color`, `esc_url_raw`, nonces +
`current_user_can()` checks on every meta box save and the contact form
handler). `inc/security.php` (Phase 4) adds baseline hardening safe to set
from theme code:

- Removes the WordPress version generator meta tag (`wp_generator`) and empties `the_generator`.
- Disables XML-RPC (`xmlrpc_enabled` filter) — this site uses neither Jetpack nor remote publishing clients.
- Adds `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin` response headers via `send_headers`.
- Generic (non-account-confirming) login error message.

**Not settable from theme code — needs wp-config.php** (untracked, outside
this repo, per environment): add `define( 'DISALLOW_FILE_EDIT', true );`
(disables the wp-admin theme/plugin file editor) to the production
wp-config.php when deploying. Real caching/HSTS/CSP policy belongs at the
hosting/CDN layer, not hard-coded here.

## 16. SEO

Semantic HTML (`<header>`, `<main>`, `<footer>`, single `<h1>` per page,
logical heading order), `title-tag` theme support, responsive images, no
keyword stuffing, clean permalink structure. Breadcrumbs
(`appiappi_breadcrumbs()` in `inc/seo.php`) on every non-front-page
template. WordPress core's built-in XML sitemaps (`/wp-sitemap.xml`,
available since WP 5.5) are untouched/enabled.

`inc/seo.php` (Phase 4) adds, **only when no SEO plugin is active**
(`appiappi_has_seo_plugin()` checks for Yoast/Rank Math/AIOSEO and bails
if found, to avoid duplicate/conflicting tags):
- `<meta name="description">` (from the post excerpt on singular content, else the Settings page's Default Meta Description).
- Open Graph (`og:type`, `og:title`, `og:description`, `og:url`, `og:site_name`, `og:image` when a featured image exists) + Twitter Card tags.
- `LocalBusiness` JSON-LD schema built from the same Customizer contact/social fields the header/footer already use — no duplicate data entry, and nothing is output for fields the admin hasn't filled in (no fabricated address/phone).
- Google Analytics (gtag.js), Search Console verification meta tag, and Meta Pixel — **only load if configured** in Settings → Appiappi Settings, so there's zero third-party request overhead by default.

## 17. Performance

`wp-block-library`, `classic-theme-styles` and `global-styles` core CSS are
dequeued (theme has no Gutenberg-frontend dependency). CSS is split into
small, cacheable files loaded in dependency order; homepage-only CSS
(`home.css`) vs. every-other-page CSS (`pages.css`) are enqueued
conditionally via `is_front_page()`. JS is a small vanilla file (no
framework), deferred via `wp_enqueue_script(..., true)` (footer). Fonts
load from Google Fonts with a `preconnect` resource hint — flagged as a
`TODO(perf)` in `inc/enqueue.php` to self-host once the design is locked.
The hero visual is a hand-drawn inline-referenced SVG (no large photo
yet). Analytics/tracking scripts (§16) only load when explicitly
configured, so there's no unconditional third-party request cost.

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

Theme was activated this way (`wp theme activate appiappi-theme`). The same
junction pattern is used for each companion plugin — e.g.
`wp-content/plugins/appiappi-pricing-plans` is junctioned the same way and
activated with `wp plugin activate appiappi-pricing-plans`. Junctions
aren't portable/committable — a second machine needs Local installed, a site
created, and every junction (theme + each plugin) recreated manually.

One-time data seeds (4 launch pricing plans; 6 template categories + 4
styles + 3 sample designs; 2 hero slides; the 12 launch FAQs; 3 concept
portfolio projects) were run via `wp eval-file` against small scripts (not
committed — dev-environment setup, not app code) using `wp_insert_post()` /
`wp_insert_term()` + `update_post_meta()`/`update_term_meta()`, matching
the values that used to live in the theme's placeholder functions.
Recreate similarly on any new environment, or add content manually in
wp-admin.

**Real Pages + Reading settings (Phase 2):** 9 real Pages now exist —
Home (`home`, empty — exists only so `page_on_front` can point at
*something*; `front-page.php` still owns the actual homepage output
regardless), Blog (`blog`), Services, How It Works, About, Contact, FAQ,
Portfolio, Pricing. Reading settings: `show_on_front=page`,
`page_on_front=<Home page ID>`, `page_for_posts=<Blog page ID>`. A real
"Primary Menu" nav menu was created and assigned to the `primary` theme
location (replacing reliance on `appiappi_nav_fallback()` for the main
site nav — the fallback function still exists and still fires for any
site that hasn't set up a menu yet). All of this was done via
`wp eval-file` against another small, uncommitted setup script — recreate
manually (or write a fresh script) on a new environment; nothing about it
is committed to the repo since it's WordPress database state, not code.

## 21. Known Limitations

- All six companion plugins (Pricing Plans, Template Showcase, Hero Slideshow, FAQ, Portfolio, Contact) are built — none are in the hard-coded-placeholder state anymore. Theme placeholder functions/fallbacks remain only for graceful degradation if a plugin gets deactivated.
- Template Showcase's category links do a real filtered page reload; style checkboxes + search are live client-side JS (§13). Nothing presentational-only remains here.
- `/templates/` archive + detail pages are real WordPress URLs now (§13) — the header nav's "Website Designs" link and `appiappi_nav_fallback()` both correctly point at `/templates/`.
- Hero slides currently have no real photographs — the placeholder skyline SVG shows whenever a slide has no Featured Image set (§11a). Not itself a bug (it's the intended graceful fallback), but replace with real photos before launch.
- Homepage hero's Google-rating card renders as an empty/neutral placeholder — `appiappi_get_google_rating()` returns `null` on purpose, do not hardcode a rating (see [DEVELOPMENT_LOG.md](DEVELOPMENT_LOG.md)).
- Portfolio has no real client projects yet — the 3 seeded entries are explicitly labelled "Concept" (§14b); replace with real work once available, and un-check "Concept Project" per entry when you do.
- Contact form validation errors don't preserve the visitor's entered field values on redirect (§14c) — minor, documented UX gap.
- No Case Study CPT yet (Portfolio Project exists; Case Studies were listed separately in the original spec and are deferred).
- SEO/security (§15–16) are a solid baseline, not a full audit or a substitute for a dedicated SEO/security plugin if the business later wants one — `appiappi_has_seo_plugin()` already steps aside if Yoast/Rank Math/AIOSEO is installed.
- The Settings → Appiappi Settings Header/Footer Scripts fields execute exactly what's pasted into them, unescaped, by design (see §15) — this is a capability-gated power-user field, not a bug; don't paste untrusted script into it.
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
- **"Error Establishing a Database Connection" (WordPress serves this with an HTTP 200, so `curl`/basic checks can look "fine" when they aren't) / WP-CLI "Error establishing a database connection" even though the site loads**: `C:\Users\GHADER\AppData\Roaming\Local\run\` can contain more than one instance-id folder at once (e.g. after Local restarts). **The instance actually in use is whichever one the live `nginx.exe`/`php-cgi.exe`/`mysqld.exe` processes reference** — check with `Get-CimInstance Win32_Process -Filter "Name='mysqld.exe'"` (or `nginx.exe`/`php-cgi.exe`) and read the `CommandLine`'s `--defaults-file=`/`-c` path; that instance-id is the correct one to use in the `INI=`/`WPCLI` helper commands in § 20, not necessarily the most-recently-created folder. Confirmed on 2026-09-05: a stale `n5MS9u2mq` folder existed alongside the real, live `En5UgfWsJ`; assuming "newest folder = current" was wrong and caused an hour of misdiagnosis.
- **A new CPT's posts silently fail to insert** (`wp_insert_post()` returns `0`, not a `WP_Error`, unless you pass `'wp_error' => true` — so a naive `is_wp_error()` check won't catch it): check the post type name length. WordPress's `wp_posts.post_type` column is `varchar(20)` — a post type name over 20 characters fails with "WordPress database error: Processing the value for the following field failed: post_type." Taxonomy names can be up to 32 characters (a different, longer limit), which is why e.g. `appiappi_portfolio_industry` (a taxonomy) is fine but `appiappi_portfolio_project` (would-be post type name, 26 chars) had to be shortened to `appiappi_project` (§14b). When debugging a `0`/silent-failure insert, call `wp_insert_post( $args, true )` (the second `true` param) to get a real `WP_Error` back instead of a bare `0`.

## 26. Known Limitations → Future Improvements

Tracked inline in § 21 and as `TODO(Phase N)` / `TODO(perf)` comments in the
relevant source files (`inc/template-tags.php`, `inc/enqueue.php`) rather
than duplicated here — grep for `TODO` to find them all.

## 27. Phased Build Plan

| Phase | Scope | Status |
|---|---|---|
| 1 | Architecture, design system, theme skeleton, Customizer settings, header, footer, homepage, responsive foundation, docs | **Done** — visuals approved by the user |
| 1.5 | Companion Plugin Architecture: Pricing Plans, Template Showcase, Hero Slideshow plugins | **Done** — all three built, active, and seeded; next: packaging (§ below) or move to Phase 2 |
| 2 | Services/How It Works/About/Contact pages, FAQ, Portfolio, Blog | **Done** — see §14–14d. Case Studies deferred (not in the original page list's CPT set) |
| 3 | Template Showcase: live search/filters (JS), `/templates/` archive page, template detail pages, selection workflow | **Done** — see §13 |
| 4 | Admin Settings page, SEO foundation, performance, security hardening | **Done** — see §8, §15–17. Lead management CPT/form was done early as part of Phase 2 (§14c) |
| 5 | Customer portal, support system, staff accounts, payment architecture | Not started |

## 28. File Location Map

| Feature | File / Folder | Purpose | How to Modify |
|---|---|---|---|
| Theme header (WP requirement) | `wp-content/themes/appiappi-theme/style.css` | Theme name/version metadata only | Edit the comment block; do not add CSS rules here |
| Design tokens | `assets/css/tokens.css` | Colour/type/spacing/radius/shadow source of truth | Change a variable once, it cascades everywhere |
| Base reset/typography | `assets/css/base.css` | Global element defaults | Edit for site-wide type/element changes |
| Header/footer/site shell layout | `assets/css/layout.css` | `.site-header`, `.mobile-nav`, `.site-footer`, `.container`, `.section` | Edit for structural/shell changes |
| Buttons, badges, chips, cards, forms, **pricing cards** | `assets/css/components.css` | Reusable UI pieces used on more than one page — loaded on *every* page, unlike `home.css`/`pages.css` | Edit for a component used on multiple pages. Pricing card styles live here specifically because the homepage teaser and the dedicated Pricing page share `appiappi_render_pricing_cards()` — see DEVELOPMENT_LOG.md 2026-09-06 for why this matters (a component styled only in a conditionally-loaded file can silently have no effect on a page that doesn't load it) |
| Homepage-only section styling | `assets/css/home.css` | Hero, trust bar (only enqueued on `is_front_page()`) | Edit for homepage-specific visual changes. **Do not** put styles here for anything rendered outside the homepage (pricing cards, template showcase, and final CTA all made this exact mistake already) — it silently won't apply on other pages |
| Theme supports / nav locations / image sizes | `inc/setup.php` | `add_theme_support`, `register_nav_menus` | Add new image sizes or theme features here |
| Asset loading | `inc/enqueue.php` | Registers/enqueues all CSS/JS, Google Fonts | Add new stylesheets/scripts here, respecting dependency order |
| Global settings | `inc/customizer.php` | Brand colour, header CTA, contact info, social links, footer tagline | Add new Customizer sections/settings here |
| Icons, nav fallback, page header, pagination, placeholder data, shared renderers | `inc/template-tags.php` | `appiappi_icon()`, `appiappi_nav_fallback()`, `appiappi_page_header()`, `appiappi_pagination()`, `appiappi_get_*()` placeholder/fallback functions (pricing plans, featured templates + categories/styles, hero slides, services, how-it-works steps, FAQs, portfolio projects, Google rating), and the shared `appiappi_render_*()` functions (`pricing_cards`, `template_showcase`, `hero_slides`, `faq`, `portfolio_grid`) | Add icons to the `$icons` array; each `appiappi_render_*()` function is the only place its section's HTML lives — edit it, not a shortcode or a page template, when changing markup |
| Homepage assembly | `front-page.php` | Section order | Add/remove `get_template_part()` calls |
| Homepage hero | `template-parts/sections/hero.php` | Calls `[appiappi_hero_slider]` shortcode if active, else the theme placeholder — both via `appiappi_render_hero_slides()` | Edit the shortcode-vs-fallback logic here; edit actual slides via **Hero Slides** in wp-admin |
| **Hero Slider plugin** | `wp-content/plugins/appiappi-hero-slider/` | CPT `appiappi_slide`, meta box admin UI, `[appiappi_hero_slider]` shortcode | `includes/cpt.php` (post type/admin columns), `includes/meta-boxes.php` (admin fields + save/sanitize), `includes/shortcode.php` (query + data mapping, plus fallback-to-theme-default when zero slides published) |
| Homepage pricing preview | `template-parts/sections/pricing-preview.php` | Calls `[appiappi_pricing]` shortcode if active, else the theme placeholder — both via `appiappi_render_pricing_cards()` | Edit the shortcode-vs-fallback logic here; edit actual plan data via **Pricing Plans** in wp-admin |
| **Pricing Plans plugin** | `wp-content/plugins/appiappi-pricing-plans/` | CPT `appiappi_plan`, meta box admin UI, `[appiappi_pricing]` shortcode, display settings | `includes/cpt.php` (post type/admin columns), `includes/meta-boxes.php` (admin fields + save/sanitize, colour/billing-frequency/feature-format options), `includes/shortcode.php` (query + data mapping, feature parsing), `includes/settings.php` (**Pricing Plans → Display Settings** — columns-per-row) |
| Homepage template showcase preview | `template-parts/sections/templates-preview.php` | Calls `[appiappi_templates]` shortcode if active (reads `?appiappi_category=` for real filtering), else the theme placeholder — both via `appiappi_render_template_showcase()` | Edit the shortcode-vs-fallback logic here; edit actual designs/categories/styles via **Website Designs** in wp-admin |
| **Template Showcase plugin** | `wp-content/plugins/appiappi-template-showcase/` | CPT `appiappi_template`, taxonomies `appiappi_template_category`/`appiappi_template_style`, `[appiappi_templates]` shortcode | `includes/cpt.php` (post type + taxonomies + admin columns), `includes/taxonomy-meta.php` (category icon field), `includes/meta-boxes.php` (admin fields + save/sanitize), `includes/shortcode.php` (query + data mapping) |
| Hero slider behaviour | `assets/js/main.js` | Auto-advance, dots, pause-on-hover/focus, `prefers-reduced-motion` skip | Edit the second IIFE in the file (the first handles mobile nav/sticky header) |
| Homepage trust bar | `template-parts/sections/trust-bar.php` | 4-item icon strip, per-item colour | Edit `$items` array in the file |
| Homepage final CTA | `template-parts/sections/final-cta.php` | Closing conversion band | Edit copy/links here |
| Favicon | `inc/setup.php` (`appiappi_favicon()`) + `assets/images/favicon.svg` | SVG favicon (maple-leaf mark), skipped if a Customizer Site Icon is set | Edit the SVG file or the fallback condition |
| Site header | `template-parts/header/site-header.php` | Logo, nav, CTA, mobile toggle | Edit markup here; menu items via **Appearance → Menus → Primary Menu** (real menu, assigned to the `primary` location — see §20) |
| Site footer | `template-parts/footer/site-footer.php` | 4-column footer | Edit link lists here; contact/social via Customizer |
| Mobile nav + sticky header behaviour | `assets/js/main.js` | Toggle open/close, scroll shadow | Edit here; no dependencies to manage |
| Services page | `page-services.php` (slug `services`) | Calls `[appiappi_services]` if active, else theme placeholder — both via `appiappi_render_services()` | Edit actual services via **Services** in wp-admin |
| **Services plugin** | `wp-content/plugins/appiappi-services/` | CPT `appiappi_service`, meta box, `[appiappi_services]` shortcode | `includes/cpt.php`, `includes/meta-boxes.php` (icon/Hook/Breakdown Items/Closing Line), `includes/shortcode.php` |
| How It Works page | `page-how-it-works.php` (slug `how-it-works`) | 6 numbered steps | Edit `appiappi_get_how_it_works_steps()` in `template-tags.php` |
| About page | `page-about.php` (slug `about`) | Renders the real Page's `the_content()` | Edit the **About** page content in wp-admin — no code involved |
| Pricing page | `page-pricing.php` (slug `pricing`) | Full plan comparison + FAQ | Reuses the Pricing Plans + FAQ shortcodes/fallbacks already documented above |
| FAQ page | `page-faq.php` (slug `faq`) | Calls `[appiappi_faq]` if active, else theme placeholder — both via `appiappi_render_faq()` | Edit actual questions via **FAQs** in wp-admin |
| **FAQ plugin** | `wp-content/plugins/appiappi-faq/` | CPT `appiappi_faq` + taxonomy `appiappi_faq_category`, `[appiappi_faq]` shortcode | `includes/cpt.php` (post type/taxonomy), `includes/shortcode.php` (query + data mapping) |
| FAQ accordion toggle | `assets/js/main.js` | Open/close on click | Third IIFE in the file |
| Portfolio page | `page-portfolio.php` (slug `portfolio`) | Calls `[appiappi_portfolio]` if active, else theme placeholder — both via `appiappi_render_portfolio_grid()` | Edit actual projects via **Portfolio** in wp-admin |
| **Portfolio plugin** | `wp-content/plugins/appiappi-portfolio/` | CPT `appiappi_project` + taxonomy `appiappi_portfolio_industry`, meta box, `[appiappi_portfolio]` shortcode | `includes/cpt.php`, `includes/meta-boxes.php` (client/location/URL/services/results/concept-flag), `includes/shortcode.php` |
| Contact page | `page-contact.php` (slug `contact`) | Contact info card + `[appiappi_contact_form]` if active, else a mailto/phone fallback | Edit info-card markup here; contact details via Customizer |
| **Contact plugin** | `wp-content/plugins/appiappi-contact/` | CPT `appiappi_lead`, `[appiappi_contact_form]` shortcode, submission handler | `includes/cpt.php` (Lead admin UI + status meta box), `includes/handler.php` (validation, spam honeypot, email, PRG redirect — on `template_redirect`, not the shortcode), `includes/shortcode.php` (form markup only) |
| Blog index | `home.php` | Paginated post grid (the `page_for_posts` page) | Edit layout here; posts themselves are normal wp-admin **Posts** |
| Single post | `single.php` | Title, meta, featured image, content | Edit here |
| Category/tag archives | `archive.php` | Same post grid as the blog index | Edit here |
| Blog post card | `template-parts/content/post-card.php` | One card in the grid | Edit here (used by both `home.php` and `archive.php`) |
| Website Designs archive | `archive-appiappi_template.php` (native CPT archive, `/templates/`) | Full sidebar + grid of every published design | Edit layout here; actual designs/categories/styles via **Website Designs** in wp-admin |
| Website Design detail page | `single-appiappi_template.php` (native CPT single, `/templates/{slug}/`) | Preview, description, vendor credit, "Choose This Design" (selection workflow) | Edit here; content via the design's own edit screen |
| Template card/detail data mapping | `wp-content/plugins/appiappi-template-showcase/includes/shortcode.php` | `appiappi_showcase_map_post( $post )` | The one place a template post → render-array mapping happens; used by the shortcode query loop and both new templates |
| Live style/search filtering | `assets/js/main.js` | Client-side filter on `#templates-grid` | Fourth IIFE in the file; category filtering stays server-side (real page reload), see §13 |
| Selection workflow | `single-appiappi_template.php` "Choose This Design" link → `wp-content/plugins/appiappi-contact/includes/shortcode.php` (reads `?design=`/`?plan=`) | Pre-fills + tags a Contact/Lead submission with the chosen design/plan | Edit the link's `plan` query value here; edit the banner/hidden-fields logic in the contact shortcode |
| Admin Settings page | `inc/admin/settings-page.php` (Settings → Appiappi Settings) | SEO defaults, GA/GSC/Meta Pixel IDs, business hours, currency, header/footer scripts | Add new fields to `appiappi_settings_fields()`; read a value anywhere with `appiappi_get_setting( $key )` |
| SEO output (meta/OG/schema/tracking/breadcrumbs) | `inc/seo.php` | `appiappi_output_meta_tags()`, `appiappi_output_schema()`, `appiappi_output_tracking_head()`/`_footer()`, `appiappi_breadcrumbs()` | Edit here; all skip themselves automatically if a real SEO plugin (Yoast/Rank Math/AIOSEO) is active |
| Security hardening | `inc/security.php` | Generator tag removal, XML-RPC disable, security response headers | Edit here; `DISALLOW_FILE_EDIT` must go in wp-config.php instead, see §15 |
