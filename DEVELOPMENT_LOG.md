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

## 2026-09-05 — Contact form intentionally breaks the shared-render-function convention

Every other companion plugin (Pricing Plans, Template Showcase, Hero
Slider, FAQ, Portfolio) follows the same shape: the theme owns a shared
`appiappi_render_*()` function, and both the plugin's real data and the
theme's placeholder data render through it, so the site looks identical
whether or not the plugin is active. `appiappi-contact` deliberately does
**not** do this. A contact form's only reason to exist is to actually
submit somewhere — a "placeholder form" with no working handler behind it
would visually look fine but silently do nothing when submitted, which is
worse than showing nothing at all (a visitor believes their message was
sent when it wasn't). Instead, `page-contact.php` checks
`shortcode_exists( 'appiappi_contact_form' )` and shows a simple
mailto/phone message (from the existing Customizer contact fields) when
the plugin isn't active, rather than a fake copy of the form. This breaks
the pattern on purpose — noted here so a future pass doesn't try to
"fix" it into conformance.

## 2026-09-05 — About page uses real Page content, not a static array

Services and How It Works both use a static PHP array
(`appiappi_get_services()` / `appiappi_get_how_it_works_steps()` in
`inc/template-tags.php`) for their content, matching the trust bar's
existing pattern — reasoned there as "fixed offering descriptions, not
business data." About breaks that pattern on purpose: it renders the real
WordPress Page's `the_content()`. The distinction: Services/How-It-Works
describe *what the company always offers* (structural, rarely-changing,
fine to require a code edit), while About is *editorial narrative copy*
the business owner will plausibly want to rewrite on their own schedule —
exactly the kind of content the project's "no hard-coded business data"
rule is meant to keep out of PHP files. Given a choice between consistency
with the other two pages and consistency with the project's actual data
rule, the data rule won.

## 2026-09-05 — CPT name length: WordPress silently truncates/fails past 20 chars

`register_post_type( 'appiappi_portfolio_project', ... )` (26 characters)
registered without error, but every `wp_insert_post()` against it failed
with a `WP_Error` "post_type. The supplied value may be too long" — only
visible because a debug script passed `wp_insert_post( $args, true )` to
get a `WP_Error` back; the plugin's own handler code (matching every other
plugin's convention) used the default single-argument form, which just
returns `0` on failure, silently. `wp_posts.post_type` is `varchar(20)`;
taxonomy names get `varchar(32)`, a different and larger limit, which is
why `appiappi_portfolio_industry` (28 chars, a taxonomy) was never a
problem. Renamed the post type to `appiappi_project`. Takeaway for the
next CPT added to this project: **check post type name length against 20
characters before registering it**, and prefer catching `wp_insert_post()`
failures with the two-argument `true` form during any manual/debug
testing, even though the plugins' production code paths don't need it
(they insert known-good, already-validated data).

## 2026-09-05 — Only headline/subheadline/image/CTA rotate in the hero slider

When building the `appiappi-hero-slider` plugin, the hero's other elements
(the "Canadian Web Design & SEO" eyebrow, the 4 feature chips, the "View
Our Plans" secondary CTA, the Google-rating card) were deliberately kept
**out** of the per-slide data model, even though it would have been just
as easy to make them per-slide fields. Reasoning: those are standing value
props / trust signals about the company, not per-slide marketing messages
— if they changed every 7 seconds along with the headline, the hero would
feel busier and less trustworthy, working against the "sophisticated, not
flashy" design brief. Implementation-wise this also kept the CPT small
(4 meta fields) instead of duplicating global site content into every
slide. The chips/eyebrow/secondary-CTA/rating-card markup is simply
repeated inside each slide's HTML (harmless, since only one slide is ever
visible) rather than being extracted into a separate always-visible DOM
region, which would have been more "correct" structurally but meaningfully
more complex for no user-visible benefit.

## 2026-09-05 — Local instance-id mismatch: diagnosed, partially misdiagnosed, then fixed

Mid-session, WP-CLI started failing with "Error establishing a database
connection" even though the site loaded in a browser (WordPress serves its
own DB-error page with an HTTP 200, so a bare `curl` status check can look
fine when it isn't — this tripped me up initially). Root cause: Local (by
WP Engine) creates a fresh instance-id folder under
`AppData\Roaming\Local\run\` on some restarts without always cleaning up
the previous one, and I had assumed "most recently created folder = the
one actually in use," which was **wrong** — the real, live `nginx.exe`/
`php-cgi.exe`/`mysqld.exe` processes were still pointing at the *older*
instance folder (`En5UgfWsJ`, confirmed via
`Get-CimInstance Win32_Process -Filter "Name='mysqld.exe'"` and reading
each process's `CommandLine`). Because of that wrong assumption, I first
stopped two `mysqld.exe` processes that were actually the correct,
functioning ones (Local's watchdog silently respawned equivalents on the
same port immediately after, so no lasting harm) before finding the real
fix: my own `wpcli.sh` dev helper script had been pointed at the wrong,
unused instance folder the whole time. **Lesson, now captured in
PROJECT_MASTER.md §25:** never assume "newest folder" — always verify
which instance is live by inspecting the actual running processes'
command lines first, especially before stopping any process.

## 2026-09-04 — Style is a taxonomy, not a hardcoded select (unlike plan colour)

For the Pricing Plans plugin, colour and icon were made curated dropdowns
because a wrong choice there breaks visual consistency with the design
system. For the Template Showcase plugin's "Style" field, the opposite
call was made: it's a real flat taxonomy (`appiappi_template_style`), so
the admin can add new styles (e.g. "Luxury", "Playful") from wp-admin
without a code change, same as categories. The difference: style is pure
descriptive metadata with no visual consequence if the admin invents a new
value, whereas plan colour directly drives a CSS custom property — so the
same "should this be curated or open?" question gets different answers
depending on whether the field controls appearance or just classifies
content. See PROJECT_MASTER.md §7 for both CPTs' field lists.

## 2026-09-04 — Category filter via query string + shortcode attribute, no JS

While building the Template Showcase shortcode, its `category` attribute
(for narrowing results) turned out to compose almost for free with the
sidebar links already in the shared render function: point each category
link at `?appiappi_category=<slug>`, have the theme's
`templates-preview.php` read that query string and interpolate it into
the shortcode tag it builds (`[appiappi_templates category="…"]`), and the
plugin's existing `tax_query` logic does the rest. This makes category
filtering genuinely work (full page reload, no JavaScript) as a nearly-free
side effect of the shortcode-attribute design, rather than staying purely
presentational like the reference mockup's sidebar. The style checkboxes
and search input were *not* wired up the same way — those need
client-side/AJAX behaviour to feel right (checking a box shouldn't reload
the page), which is real scope deliberately left for later rather than
faked.

## 2026-09-04 — Shared render function instead of duplicating card markup

The Pricing Plans plugin needed to produce the exact same `.pricing-grid`
HTML the theme was already rendering from placeholder data. Rather than
duplicating that markup inside the plugin (drift risk — the two copies
would inevitably diverge as the design changes) or moving the markup
entirely into the plugin (which would violate "theme owns visuals, plugin
owns data" and make the plugin theme-specific in an uglier way), the card
loop was extracted into one function on the theme side —
`appiappi_render_pricing_cards( array $plans )` in
`inc/template-tags.php` — that takes a plain array shaped like the old
placeholder's return value. Both the theme's own fallback and the
plugin's `[appiappi_pricing]` shortcode build that same shape (one from a
static array, one from `WP_Query` + post meta) and call the one function.
This is the pattern to repeat for the Template Showcase and Hero
Slideshow plugins: one theme-owned render function per section, fed by
whichever data source is active.

## 2026-09-04 — Plan colour/icon are curated selects, not free-form pickers

The Pricing Plan meta box offers colour and icon as fixed dropdown lists
(4 colours matching the design system's plan tokens; ~8 icons from the
theme's existing icon set) rather than a raw color picker or free-text
icon name field. A non-technical admin picking an arbitrary hex colour or
mistyping an icon name could easily break the card's visual consistency
with the rest of the site (or silently render no icon at all, since
`appiappi_icon()` returns an empty string for an unknown name). Curated
selects keep every plan visually consistent with the design system while
still being editable without touching code — matches the project rule
"don't expose dangerous technical settings to non-technical users" (
MASTER_PROMPT.md § Global Design System / Customisation).

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
