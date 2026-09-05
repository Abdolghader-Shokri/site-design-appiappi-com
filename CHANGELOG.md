# Changelog

All notable changes to this project. Dated by day; most recent first.

## 2026-09-05 — Phase 2: Services/How It Works/About/Contact/FAQ/Portfolio/Blog

### Added
- Three new static-content page templates (`page-services.php`, `page-how-it-works.php`, `page-about.php`), auto-applied via the `page-{slug}.php` convention, sharing a new `appiappi_page_header()` helper and `assets/css/pages.css` (loaded on every non-front-page page).
- `page-pricing.php`: dedicated Pricing page reusing the existing Pricing Plans shortcode/fallback plus a Pricing-specific FAQ section.
- New companion plugin `appiappi-faq`: CPT `appiappi_faq` (title=question, native content=answer) + taxonomy `appiappi_faq_category`, shortcode `[appiappi_faq category="" limit="-1"]`. Shared `appiappi_render_faq()` in the theme; accordion open/close via a new IIFE in `assets/js/main.js`. `page-faq.php` added; seeded the 12 launch FAQs.
- New companion plugin `appiappi-portfolio`: CPT `appiappi_project` (title, native content=description, Featured Image) + taxonomy `appiappi_portfolio_industry`; meta box for client/location/external URL/services/results/a "Concept Project" flag (never fabricate results — a project can be explicitly marked as illustrative instead). Shortcode `[appiappi_portfolio count="6" industry=""]`. Shared `appiappi_render_portfolio_grid()`. `page-portfolio.php` added; seeded 3 concept projects.
- New companion plugin `appiappi-contact`: CPT `appiappi_lead` (every submission stored + visible/manageable in wp-admin, with an editable Status field), shortcode `[appiappi_contact_form]` (name, business, email, phone, business type, interested service, budget range, message + honeypot spam field). Submission handling on `template_redirect` (not the shortcode — too late in the lifecycle to redirect from there): validates, creates the Lead, emails the admin, then Post/Redirect/Gets back with a success/error flag. `page-contact.php` added, showing the form when the plugin is active or a mailto/phone fallback when it isn't (deliberately not a fake copy of the form).
- Native WordPress Blog support: `home.php` (paginated blog index, used as the `page_for_posts` page), `single.php`, `archive.php`, `template-parts/content/post-card.php`, and a new `appiappi_pagination()` helper (wraps `paginate_links()` in the design system's `.appiappi-pagination` markup instead of WP core's default).
- 9 real WordPress Pages created (Home, Blog, Services, How It Works, About, Contact, FAQ, Portfolio, Pricing); Reading settings configured (`page_on_front`/`page_for_posts`) so `/blog/` shows the real posts index while `front-page.php` still owns the site root unconditionally.
- A real "Primary Menu" nav menu, assigned to the theme's `primary` location — replaces reliance on `appiappi_nav_fallback()` for everyday navigation (the fallback function still exists for any site that hasn't set up a menu).

### Changed
- `.gitignore`: un-ignored `wp-content/plugins/appiappi-faq/`, `appiappi-portfolio/`, `appiappi-contact/`.
- Deleted WordPress's default "Hello world!" post, "Sample Page", and default comment (one-time cleanup, not app behaviour).

### Files Modified
- `wp-content/plugins/appiappi-faq/**`, `appiappi-portfolio/**`, `appiappi-contact/**` (new)
- `wp-content/themes/appiappi-theme/page-services.php`, `page-how-it-works.php`, `page-about.php`, `page-contact.php`, `page-faq.php`, `page-portfolio.php`, `page-pricing.php`, `home.php`, `single.php`, `archive.php` (new)
- `wp-content/themes/appiappi-theme/template-parts/content/post-card.php` (new)
- `wp-content/themes/appiappi-theme/assets/css/pages.css` (new), `inc/enqueue.php`, `inc/template-tags.php`, `assets/js/main.js`
- `.gitignore`, `PROJECT_MASTER.md`, `MASTER_PROMPT.md`, `DEVELOPMENT_LOG.md`

### Notes
- Hit and fixed a real bug during Portfolio plugin setup: `appiappi_portfolio_project` (26 chars) exceeded WordPress's 20-character `post_type` column limit, causing silent DB-level insert failures (`wp_insert_post()` returned `0`, not caught by a plain `is_wp_error()` check). Renamed to `appiappi_project`. Documented in `PROJECT_MASTER.md` §25 Troubleshooting.
- Verified every new page (`/services/`, `/how-it-works/`, `/about/`, `/contact/`, `/faq/`, `/portfolio/`, `/pricing/`, `/blog/`) returns HTTP 200 with correct content via curl/WP-CLI/browser; verified Lead creation logic directly (test lead created then deleted); confirmed no PHP errors in `debug.log` throughout.
- Browser-tool permissions became inconsistent partway through QA (some calls declined) — switched remaining verification to WP-CLI/curl, which covers the same ground without needing the browser.
- Phase 2 (per MASTER_PROMPT.md's Development Phases) is now complete. Remaining known gaps: no `/templates/` archive/detail pages (Phase 3), no admin Settings page (Phase 4), Portfolio has only concept/placeholder projects until real client work exists.

## 2026-09-05 — Hero Slider companion plugin (third and last of the three)

### Added
- New plugin `wp-content/plugins/appiappi-hero-slider/`: registers the `appiappi_slide` CPT (post Title = slide headline, native Featured Image = slide visual) with a "Slide Details" meta box (subheadline, CTA button text/URL, optional image alt text) and the `[appiappi_hero_slider]` shortcode.
- Shared renderer `appiappi_render_hero_slides( $slides )` added to the theme's `inc/template-tags.php`, same pattern as the other two plugins. Refactored `template-parts/sections/hero.php` down to just the shortcode-or-fallback check.
- Real slider behaviour in `assets/js/main.js` (new, self-contained IIFE): pill-shaped dot navigation, 7s auto-advance, pause on hover/focus, and auto-advance is skipped entirely under `prefers-reduced-motion`. A single slide renders no dots and the script does nothing (no unnecessary JS runs).
- New CSS in `assets/css/home.css`: `.hero-slide`/`.hero-dots` plus an absolute-positioned crossfade for the slide images (`.hero__visual-frame img`).
- Seeded 2 sample slides (the original static hero content, plus a second "Grow With Ongoing SEO & Support" slide) to demonstrate rotation.

### Changed
- Only the headline, subheadline, image and primary CTA rotate per slide — the eyebrow, 4 feature chips, "View Our Plans" secondary CTA, and the (still never-fabricated) Google-rating card stay constant, simply repeated inside each slide's markup since only one is visible at a time.
- `.gitignore`: un-ignored `wp-content/plugins/appiappi-hero-slider/`.

### Files Modified
- `wp-content/plugins/appiappi-hero-slider/**` (new)
- `wp-content/themes/appiappi-theme/inc/template-tags.php`, `template-parts/sections/hero.php`, `assets/css/home.css`, `assets/js/main.js`
- `.gitignore`, `PROJECT_MASTER.md`, `MASTER_PROMPT.md`, `DEVELOPMENT_LOG.md`

### Notes
- **All three companion plugins are now built** (Pricing Plans, Template Showcase, Hero Slideshow) — the Companion Plugin Architecture phase (1.5) is complete.
- Hit and resolved a Local-by-WP-Engine environment issue mid-session: a stale/leftover instance-id folder under `AppData\Roaming\Local\run\` was mistaken for the current one, causing WP-CLI to report a false "database connection" error even though the live site's actual `nginx`/`php-cgi`/`mysqld` processes were fine. Root-caused via `Get-CimInstance Win32_Process`, corrected the local `wpcli.sh` helper, and documented the diagnostic method in `PROJECT_MASTER.md` §25 Troubleshooting so it isn't re-debugged from scratch next time.
- Verified via shortcode render (both slides + dots present), a full homepage check, a live browser screenshot (desktop + mobile), and console/debug-log checks — no errors.
- Next step (pending user direction): package theme + all three plugins as installable zips, or move on to Phase 2 (Services, How It Works, About, Contact, FAQ, Portfolio, Blog).

## 2026-09-04 — Template Showcase companion plugin

### Added
- New plugin `wp-content/plugins/appiappi-template-showcase/`: registers the `appiappi_template` CPT (native Featured Image as the design preview) and two taxonomies — `appiappi_template_category` (hierarchical, with a custom per-term `icon` field) and `appiappi_template_style` (flat) — plus a "Design Details" meta box (description, price, rating, rating count, demo/details URLs, and original vendor/source fields) and the `[appiappi_templates count="" category="" show_sidebar=""]` shortcode.
- Shared renderer `appiappi_render_template_showcase( $templates, $categories, $styles, $show_sidebar )` added to the theme's `inc/template-tags.php`, mirroring the Pricing Plans pattern — the single source of the sidebar+grid HTML, called by both the plugin's shortcode and the theme's placeholder fallback. Added a `$show_sidebar` toggle (with a `.templates-layout--no-sidebar` CSS variant) so a future shortcode instance elsewhere on the site can render just the grid.
- Real category filtering: sidebar category links carry `?appiappi_category=<slug>`; the theme reads that and passes it to the shortcode's `category` attribute, which runs a real `tax_query`. No JavaScript involved — the style checkboxes and search box remain visual-only.
- Seeded 6 categories (with icons), 4 styles, and 3 sample designs (Construction Pro / Justice Law / Dental Clinic) via a one-time `wp eval-file` script, matching the old placeholder content.

### Changed
- `template-parts/sections/templates-preview.php` now calls `[appiappi_templates]` via `shortcode_exists()` when the plugin is active (passing through the `?appiappi_category=` query string), falling back to placeholder data otherwise.
- `.gitignore`: un-ignored `wp-content/plugins/appiappi-template-showcase/`.

### Files Modified
- `wp-content/plugins/appiappi-template-showcase/**` (new)
- `wp-content/themes/appiappi-theme/inc/template-tags.php`, `template-parts/sections/templates-preview.php`, `assets/css/home.css`
- `.gitignore`, `PROJECT_MASTER.md`, `MASTER_PROMPT.md`, `DEVELOPMENT_LOG.md`

### Notes
- Verified via direct shortcode render, a full homepage check, and a `?appiappi_category=legal` check (correctly narrowed to 1 result) — all matching expectations, no PHP errors logged.
- Next step (pending user approval): Hero Slideshow plugin (`appiappi-hero-slider`), then packaging theme + all three plugins as installable zips.

## 2026-09-04 — Pricing Plans companion plugin

### Added
- New plugin `wp-content/plugins/appiappi-pricing-plans/`: registers the `appiappi_plan` CPT (non-public, wp-admin only), a native "Plan Details" meta box (price, period, note, colour, icon, featured flag, badge, CTA text/url, one-feature-per-line textarea — no ACF dependency), and the `[appiappi_pricing]` shortcode.
- Shared renderer `appiappi_render_pricing_cards( $plans )` added to the theme's `inc/template-tags.php` — the single source of pricing-card HTML, called by both the plugin's shortcode and the theme's own placeholder fallback.
- `favicon.svg` (previous session) and this plugin are both junctioned into the Local site the same way as the theme, and tracked in `.gitignore` with an explicit exception.
- Seeded the 4 launch plans (Starter/Business/Professional/Growth) as real `appiappi_plan` posts via a one-time `wp eval-file` script (not committed — dev setup, not app code), matching the values the old placeholder function used.

### Changed
- `template-parts/sections/pricing-preview.php` now calls `[appiappi_pricing]` via `shortcode_exists()` when the plugin is active, falling back to `appiappi_get_pricing_plans()` otherwise — same visible output either way.
- `.gitignore`: un-ignored `wp-content/plugins/appiappi-pricing-plans/`.

### Files Modified
- `wp-content/plugins/appiappi-pricing-plans/**` (new)
- `wp-content/themes/appiappi-theme/inc/template-tags.php`, `template-parts/sections/pricing-preview.php`
- `.gitignore`, `PROJECT_MASTER.md`, `MASTER_PROMPT.md`, `DEVELOPMENT_LOG.md`

### Notes
- Verified via `wp eval 'echo do_shortcode("[appiappi_pricing]");'` and a full homepage text/structure check — output identical to the old placeholder-driven homepage, now backed by real, wp-admin-editable data.
- Next step (pending user approval): Template Showcase plugin (`appiappi_template` CPT + category taxonomy).

## 2026-09-03 (theme rebuild)

### Added
- Full sidebar + filter layout for the homepage "Featured Website Designs" section (`template-parts/sections/templates-preview.php`): search box, category list (`appiappi_get_template_categories()`), style checkboxes (`appiappi_get_template_styles()`) — matching `design-reference/appiappiSimple.png` 1:1. Sidebar is presentational only; no live filtering yet.
- New icons (`briefcase`, `home`, `heart`, `shopping-bag`, `hammer`, `scale`, `grid`) for the category list.
- Companion Plugin Architecture Plan documented in `MASTER_PROMPT.md` / `PROJECT_MASTER.md`: `appiappi-hero-slider`, `appiappi-pricing-plans`, `appiappi-template-showcase` — decided, not yet built.

### Changed
- Rebuilt the `leaf` icon (header/footer logo mark) as a bolder, filled maple-leaf silhouette instead of a thin line-art outline.
- Bumped the default icon stroke-width from 1.75 to 2 for a bolder, cleaner look across the icon set.
- Repositioned the hero Google-rating card to float over the top of the hero visual's right edge (was previously offset in a way that didn't read correctly), matching the reference.
- Trust bar icons now each carry their own accent colour (green/purple/orange/blue) instead of one uniform blue tint, matching the reference's variety.
- `.template-grid`'s 3-column breakpoint moved from 1024px to 1400px now that it shares row width with the new 280px sidebar at 1024px+.

### Files Modified
- `wp-content/themes/appiappi-theme/inc/template-tags.php`, `assets/css/home.css`, `assets/css/components.css`, `template-parts/sections/trust-bar.php`, `template-parts/sections/templates-preview.php`
- `PROJECT_MASTER.md`, `MASTER_PROMPT.md`, `DEVELOPMENT_LOG.md`

### Notes
- This was a rebuild triggered by user feedback that the first Phase 1 pass didn't match the Photoshop reference closely enough, and a request to restructure future work as theme (shell) + companion plugins (dynamic content) rather than everything living in the theme. See `DEVELOPMENT_LOG.md` for the reasoning.
- Next step (pending user approval of this visual pass): build the `appiappi-pricing-plans` plugin.

## 2026-09-03

### Added
- Custom `appiappi-theme` WordPress theme skeleton: design tokens (`assets/css/tokens.css`), base/layout/component/home stylesheets, mobile-first responsive rules at 640/768/1024/1280px.
- Theme bootstrap (`functions.php`, `inc/setup.php`, `inc/enqueue.php`, `inc/customizer.php`, `inc/template-tags.php`).
- Native Customizer settings: brand primary colour, header CTA text/URL, contact info, social links, footer tagline.
- Inline SVG icon system (`appiappi_icon()`) — no icon-font/CDN dependency.
- Sticky header with primary nav (menu-location fallback), mobile hamburger nav, footer with 4-column layout.
- Homepage (`front-page.php`) assembled from template parts: Hero, Pricing preview, Featured Website Designs preview, Trust bar, Final CTA — translated into English from the user's Photoshop reference (`design-reference/appiappiSimple.png`).
- Placeholder data providers for pricing plans and featured templates (`inc/template-tags.php`), explicitly marked `TODO(Phase 2/3)` to be replaced by real Custom Post Types.
- Hero visual placeholder: hand-drawn inline SVG skyline (`assets/images/hero-placeholder.svg`) — no stock photo used.
- Google-rating hero card wired to `appiappi_get_google_rating()`, which returns `null` on purpose so no rating is ever fabricated; renders as a neutral "coming soon" placeholder.
- Project documentation: `PROJECT_MASTER.md`, `MASTER_PROMPT.md`, `DEVELOPMENT_LOG.md`, this changelog.
- Design reference image (`design-reference/appiappiSimple.png`) and empty theme scaffold, junctioned into the local WordPress site.
- `.gitignore` and `README.md` for the repository.

### Changed
- `.gitignore` updated to track `wp-content/themes/appiappi-theme/` while continuing to ignore WordPress core and all other themes/plugins.

### Files Modified
- `.gitignore`, `README.md`, `design-reference/appiappiSimple.png`
- `wp-content/themes/appiappi-theme/**` (new)
- `PROJECT_MASTER.md`, `MASTER_PROMPT.md`, `DEVELOPMENT_LOG.md`, `CHANGELOG.md` (new)

### Notes
- Local WordPress dev environment set up: Local (by WP Engine) site `appiappicom` at `https://appiappicom.local`; theme folder connected to this repo via an NTFS directory junction (see PROJECT_MASTER.md §20). Theme activated via WP-CLI.
- Homepage verified rendering correctly (text content + interactive elements checked via automated browser tooling); visual/responsive spot-check to be done by the user in an actual browser.
- No CPTs, admin settings page, or non-homepage pages exist yet — see PROJECT_MASTER.md §21 Known Limitations.

## 2026-09-03 (earlier)

### Added
- Initial repository scaffolding: `.gitignore` (WordPress-aware) and `README.md` describing the project.
- Git remote connected to `https://github.com/Abdolghader-Shokri/site-design-appiappi-com.git`; initial commits pushed.

### Files Modified
- `.gitignore`, `README.md`
