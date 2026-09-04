# Changelog

All notable changes to this project. Dated by day; most recent first.

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
