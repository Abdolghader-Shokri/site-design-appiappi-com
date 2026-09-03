# Development Log

Why non-obvious technical decisions were made. Not a duplicate of
CHANGELOG.md (the *what*) — this is the *why*, for future developers/AI
who'd otherwise have to reverse-engineer the reasoning.

## Documentation Rule

Every change that affects architecture, functionality, configuration, file
locations, or behaviour must update `PROJECT_MASTER.md`. Every change must
update `CHANGELOG.md`. A significant technical decision must be logged
here. A change to scope/business rules must update `MASTER_PROMPT.md`.
Documentation is part of the deliverable, not optional cleanup.

## 2026-09-03 — Local dev via directory junction, not symlink

Attempted an NTFS symlink to connect the git repo's theme folder to the
Local (by WP Engine) site's `wp-content/themes/` folder; Windows refused it
without admin elevation (`NewItemSymbolicLinkElevationRequired`). Switched
to an NTFS **directory junction** (`New-Item -ItemType Junction`), which
Windows allows for directories without elevation and behaves identically
for this use case (both directories reflect the same underlying files).
Trade-off: junctions aren't portable — a second machine must recreate one
manually; there is no way to commit a junction into git in a way another OS
could use.

## 2026-09-03 — Placeholder data behind single functions, not scattered

The project's master spec forbids hard-coded business data, but Phase 1
explicitly builds the homepage (pricing preview, featured designs) before
the Pricing/Template Library CPTs exist (those are Phase 2/3). Rather than
either (a) blocking the whole homepage on CPTs existing, or (b)
hard-coding data directly into template markup, placeholder data was
isolated behind exactly two functions —
`appiappi_get_pricing_plans()` and `appiappi_get_featured_templates()` in
`inc/template-tags.php` — each marked `TODO(Phase N)`. Every template that
needs this data calls through these functions, so migrating to real CPT
queries later is a one-file change with zero template edits.

## 2026-09-03 — Never fabricate the Google rating badge

The user's Photoshop reference includes a hero-section badge reading
"5.0 ★★★★★ Based on 120+ reviews". The master spec explicitly forbids
fabricating reviews/ratings/awards. Kept the *component* (it's a good,
proven conversion pattern) but built it data-driven:
`appiappi_get_google_rating()` returns `null` until real review data
exists, and the template renders a neutral "Google Reviews — coming soon"
placeholder in that case rather than inventing numbers. Swap that function
to pull a real value (manual entry or a Google Places API integration) when
the business has reviews.

## 2026-09-03 — No hero photograph yet; hand-drawn SVG placeholder instead

Rather than fetch a stock photo from an external source (which this
project's operating rules avoid — no downloading files from untrusted
sources without the user providing them) or ship a broken `<img>`
reference, the hero visual is a small hand-authored inline-referenced SVG
skyline (`assets/images/hero-placeholder.svg`) using the theme's own colour
tokens. Swap for a real photograph (or a licensed stock image the user
supplies) before this goes live — flagged in PROJECT_MASTER.md §21.

## 2026-09-03 — Google Fonts over self-hosting, for now

`inc/enqueue.php` loads Inter from Google Fonts (with a `preconnect`
resource hint) rather than self-hosting the font files. This is faster to
ship correctly and Google's edge network is fast in practice, but it is a
third-party request that a strict Core Web Vitals / privacy review would
flag. Marked `TODO(perf)` in the file itself — self-host once the type
choice is final and unlikely to change again.

## 2026-09-03 — Dequeued `wp-block-library` / `global-styles`

The theme has no Gutenberg-frontend dependency (no block-based templates,
no `theme.json`), so WordPress's default block-library CSS and global
styles were dequeued in `inc/setup.php` to avoid shipping unused CSS on
every page load. Revisit if/when block patterns or the block editor's
frontend styling are intentionally adopted.

## 2026-09-03 — Theme + companion plugins, not one monolithic theme

The user rejected the first Phase 1 visual pass as not matching the
Photoshop reference closely enough, and separately asked for a structural
change: the theme should hold only the permanent shell (header, footer,
nav, setup), while each dynamic content block (hero, pricing, template
showcase) becomes its own **installable plugin** with a shortcode, so its
output can be placed anywhere later — not just its current homepage slot.
Adopted this as the standing architecture (see MASTER_PROMPT.md §
Companion Plugin Architecture) rather than treating it as a one-off
request, since it affects how every future dynamic section gets built:
theme templates should call through a shortcode/function-exists check with
a graceful fallback to today's placeholder data, not assume the plugin
exists. Sequencing (theme visuals first, one plugin at a time after
approval, package everything at the end) is the user's explicit
requirement, tracked in MASTER_PROMPT.md § Development Workflow Rules.

## 2026-09-03 — Homepage template sidebar ships now, filtering doesn't

Confirmed with the user (via AskUserQuestion during planning) that the
homepage's "Featured Website Designs" section should get the full
sidebar + filter visual treatment now, matching the reference exactly,
rather than deferring that whole section to a simpler teaser. Built the
sidebar (search, categories, style checkboxes) as real markup driven by
two new placeholder-data functions
(`appiappi_get_template_categories()`, `appiappi_get_template_styles()`
in `inc/template-tags.php`), but deliberately did **not** wire up any
client-side filtering — that's real functionality that belongs to the
future Template Showcase plugin's actual query logic, and faking it now
(e.g. with JS that filters the 3 placeholder cards) would create a false
impression of a finished feature. Documented the gap explicitly in
PROJECT_MASTER.md § 21 and in a code comment at the top of
`templates-preview.php`.

## 2026-09-03 — Client Login reserved as a header link, portal not built

The header includes a "Client Login" link to `/account/` as a secondary
(ghost-style) action next to the primary CTA, even though no customer
portal exists yet (that's Phase 5). This costs nothing now and avoids a
header redesign later — the link currently 404s until a portal or at least
a "coming soon" page exists at that URL.
