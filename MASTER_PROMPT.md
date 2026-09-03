# MASTER_PROMPT.md

The current, living product/business specification for the Appiappi
platform. This is the source of truth for *what* to build and *why* — see
[PROJECT_MASTER.md](PROJECT_MASTER.md) for *how* it's currently built and
where things live.

**Sync rule:** whenever the user requests a change to scope, pricing,
architecture, or business rules, this file must be updated in the same
change as the code — see [DEVELOPMENT_LOG.md § Documentation Rule](DEVELOPMENT_LOG.md).
This file must never go stale.

---

## Role

Build and maintain this project acting as: Senior WordPress Developer,
Senior Full-Stack Developer, UI/UX Designer, WordPress Architect, SEO
Technical Specialist, Conversion Rate Optimization Specialist, Performance
Specialist, and Project Documentation Manager — not just a code generator.

## Development Philosophy

- This is a professional, scalable web **platform**, not a theme install. Custom visual identity, custom architecture, modular components, real documentation.
- The public site must **not** look like a generic WordPress template. Visual identity is inspired by modern premium Canadian digital agencies / SaaS / professional tech companies: premium, modern, trustworthy, clean, fast, sophisticated, conversion-focused, business-oriented.
- Avoid: excessive animation, unnecessary gradients, visual clutter, generic "AI startup" look, cheap-template look.
- Bar to clear: a company Canadian businesses would trust with a $5,000–$20,000+ website and long-term SEO management.
- Entire site in **English** (Canadian English where the distinction matters — e.g. "optimisation", "centre", "customised" — but stay internally consistent). No Persian/Arabic/RTL anywhere in the shipped product, even though project discussion happens in Persian.

## Core Business Model

Appiappi provides website design, hosting, SEO, content management and
ongoing support to Canadian SMBs. Customers pick a professionally curated
website design from Appiappi's template library; Appiappi installs it,
customizes it (branding, content, services, mobile optimization, technical
setup), launches it, and — on the recurring plan — keeps managing it.

**Initial goal:** 100 active Growth (recurring) customers.

**Initial target industries** (non-marketplace / not e-commerce-heavy):
construction, contractors, roofing, plumbing, HVAC, electricians, law
firms, accounting firms, dental clinics, medical practices, real estate,
consultants, marketing companies, restaurants, automotive, professional
services, other local service businesses.

Core message: *"We take care of your website so you can take care of your
business."* Headline direction: *"Your Website. Professionally Managed.
Every Day."* Three-stage story: **01 — Choose Your Design → 02 — We Build
It For You → 03 — We Grow It For You.**

Position Appiappi as a **managed website and digital growth service**, not
"another web design agency."

## Visual Direction

- Primary palette: deep navy/near-black, white, light neutral backgrounds.
- Accent: professional blue; optional green (success), purple/orange (secondary accents, e.g. plan tiers) — used sparingly.
- Typography: modern, highly readable sans-serif (Inter, Manrope, or DM Sans class). Large headlines, strong hierarchy, generous whitespace, clean cards, subtle shadows, moderate radius, professional iconography, high-quality imagery, fully responsive.

## Homepage Structure

Header: logo, nav (Home / Website Designs / Services / How It Works /
Portfolio / Pricing / About / Contact), primary CTA "Get Started", secondary
CTA "Book a Free Consultation" where appropriate. Sticky on desktop,
excellent mobile nav.

Hero: headline "Your Website. Professionally Managed. Every Day.",
supporting copy about managed design/hosting/SEO/support, primary CTA
"Explore Website Designs", secondary CTA "View Our Plans", strong visual
(website mockups or a sophisticated preview). Keep it simple, not
overcomplicated.

Trust section (immediately after hero): Canadian Business Focus,
Professional Website Design, Managed Hosting, Ongoing SEO, Dedicated
Support. **Never fabricate reviews, ratings, awards, or client logos** —
use placeholders replaceable from wp-admin.

Recommended full homepage order (may be refined with UX rationale):
Header → Hero → Trust indicators → How It Works → Featured Website Designs
→ Services → Pricing → Portfolio → Why Choose Us → SEO/Growth section →
FAQ → Final CTA → Footer. *(Phase 1 ships a reduced set — Hero, Pricing,
Featured Designs, Trust Bar, Final CTA — matching the approved Photoshop
reference; How It Works / Services teaser / Portfolio / Why Choose Us / FAQ
are added once those systems exist in Phase 2, per PROJECT_MASTER.md §27.)*

## Pricing System

Pricing must be **fully manageable from wp-admin**: add/remove/edit plans,
change price/billing frequency/description/features/order, mark "Most
Popular", show/hide, badge colour, CTA text/link. Never hard-code in
templates (Phase 1 exception: a placeholder function is used until the
Pricing CPT exists — see PROJECT_MASTER.md §12).

| Plan | Price | Includes |
|---|---|---|
| **Starter** | $199 one-time | WordPress install, theme + demo install, basic config, SSL, domain connection assistance, essential plugins, launch |
| **Business** | $399 one-time | Everything in Starter + full theme customization, business info/services/about/contact pages, nav, contact forms, customer content/images/brand colours, mobile optimization, basic on-page SEO |
| **Professional** — **Most Popular** | $699 one-time | Everything in Business + premium theme license included, 1 year managed cPanel hosting, custom logo + graphics, image optimization, advanced customization, speed optimization, security + backup config, GSC + GA setup, basic technical SEO, launch support |
| **Growth** | $599/month | Managed website, hosting, WP/theme/plugin maintenance & updates, security monitoring, backups, website changes, content management, ongoing on-page + technical SEO, keyword/content/image optimization, GSC monitoring, performance optimization, SEO reporting, ongoing support. **Do not describe support as "unlimited"** unless the business owner explicitly enables that policy — scope must stay configurable. |
| **SEO Growth** *(future, not public at launch)* | $899/month | Advanced keyword research, content strategy, monthly SEO content plan, competitor analysis, local SEO, Google Business Profile optimization, advanced technical SEO, conversion optimization, advanced reporting. Architecture must allow adding this later without rework. |

## Pricing Page

Dedicated `/pricing/` page: plan comparison, monthly/one-time pricing,
features, FAQs, CTA, "who is this plan for", upgrade path. Make Growth
visually attractive ("Most Popular" or "Best for Businesses That Want
Ongoing Growth" as appropriate — Professional currently holds "Most
Popular" per the table above; do not double-badge without reason).

## Website Template Library

One of the most important features. Visitors browse professionally
selected third-party designs (e.g. from ThemeForest/Envato) presented
inside Appiappi's own site. **Never claim authorship of third-party
designs** — show original vendor/source where legally appropriate
("Design Preview", "Original Design", "Premium Theme", "Theme Source",
"Included with selected plans"; use "Theme Included" rather than "free"
when Appiappi covers the cost for a plan).

**Template data model** (per template): ID, name, slug, category,
industry, description, external marketplace URL, live demo URL, original
author/vendor, original theme URL, current theme price, currency, preview
image, gallery images, tags, features, responsive status, WP/WooCommerce
compatibility, dates added/updated, active/featured status, sort order —
all admin-editable.

**Categories** (configurable, not hard-coded): Construction, Law, Dental,
Medical, Real Estate, Accounting, Consulting, Restaurant, Automotive,
Plumbing, HVAC, Roofing, Electrical, Professional Services, Corporate,
Agency, Beauty, Fitness, Hospitality, Other. Admin can add/delete/rename/
reorder categories and multi-assign templates.

**Search/filters**: industry, style, business type, features, price range,
popular, newest, featured — extensible.

**Template card**: preview image, name, industry, short description,
original price, source/vendor, "View Details" / "Live Demo" / "Choose This
Design" buttons.

**Template detail page**: large preview + gallery, description, ideal
business types, features, responsive info, original source, Live Demo +
Choose This Design buttons, CTA to pricing, and an explanation that this is
the *starting* design — Appiappi then adds the customer's logo, colours,
content, services, images, contact info, and SEO structure.

**External data policy**: no unauthorized scraping. Admin can always
manually override price/name/description/URLs/images/vendor/availability.
Any future automated price-sync system must be modular/replaceable and the
site must keep working if it's disabled.

**Selection workflow**: "Choose This Design" starts a lead/onboarding flow
(name, business name, email, phone, province, website/domain, industry,
selected design, selected plan, notes, preferred launch date) — **not** an
immediate purchase. Shows "You selected: [Template] — Recommended plan:
[Plan]" with a Continue CTA. Admin sees the request in wp-admin.

**Licensing**: track per-customer which theme/vendor/license/purchase
date/price/domain a template selection maps to — do not assume one license
covers unlimited customer sites.

## Services / How It Works / Portfolio / Case Studies / About / Contact / FAQ / Blog

- **Services page**: Website Design, Website Management, SEO, Content Management, Managed Hosting, Website Support.
- **How It Works**: Choose design → Choose plan → Provide business info → We customize → We launch → We manage & optimize. Strong visual treatment.
- **Portfolio**: admin-manageable entries (title, client, industry, location, description, before/after images, screenshots, services provided, SEO info, results, external URL, gallery, featured). No fabricated results — use clearly-labelled concept projects if no real ones exist yet.
- **Case Studies**: flexible system (problem, strategy, design, development, SEO, content, results, screenshots, timeline, services) — addable without code.
- **About**: Canadian market focus, reliability, long-term support/SEO/growth — avoid generic agency clichés.
- **Contact**: form (name, business, email, phone, business type, interested service, budget range, message) + email/phone/location placeholders (no invented real company info) + CTA.
- **FAQ**: fully admin-manageable (add/edit/delete/reorder/categorize). Starter topics: what's included, domain ownership, hosting, design choice/customization, is SEO included, how monthly support works, cancellation, later changes, e-commerce support, timeline, what happens if the customer leaves. No unconfirmed legal claims.
- **Blog**: categories, tags, featured images, authors, search, related posts, SEO metadata, social sharing, schema where appropriate — built for long-term SEO growth.

## WordPress Admin

Admin must be able to manage, without code: Website (logo, favicon,
colours, typography, header, footer, social links, contact info, global
CTA), Pricing (plans/prices/features/visibility/order/badges), Templates
(templates/categories/vendors/prices/links/images/features/status),
Portfolio (projects/categories/images/case studies), FAQs
(questions/answers/categories), Blog (posts/categories/tags), Leads
(contact submissions, template selections, consultation requests).

## Site Settings

Central settings, all optional (don't force configuration of unused
features): company name, logo, phone, email, address, hours, social media,
default SEO title/description, Google Analytics ID, Search Console
verification, Meta Pixel, tracking scripts (header/footer), cookie
settings, default CTA, currency, pricing display, Canadian province
options.

## Optional Feature Architecture

Every non-core feature must be modular/toggleable and not tightly coupled
to the theme core: reviews, testimonials, live chat, WhatsApp, Google
Reviews, newsletter, popup, cookie banner, analytics, Facebook Pixel, GTM,
booking system, CRM integration, payment system, client portal, support
tickets.

## Customer Portal — Future Ready

Not required at launch, but the architecture must not block adding it
later: customer login, dashboard, website status, support requests, SEO
reports, billing, documents, messages, files, approvals, task history,
website change history. (The header's "Client Login" link already reserves
`/account/` for this.)

## Support / Request Management — Future System

Future flow: Client → New Request → Assigned to Admin → In Progress →
Waiting for Client → Completed. Every message needs timestamp, sender,
recipient, status, attachments, internal notes where applicable. **Messages
must never be silently deleted** — support an immutable audit log.

## Admin / Staff Architecture

Future: staff accounts, roles, permissions, assigned customers/tasks,
activity history, internal notes, workload tracking. Target ratio: ~1
administrator per 6 Growth customers. An administrator should only see
customers assigned to them unless higher-privileged.

## Security

Best practices throughout: authentication, authorization, nonces,
capability checks, input validation, output escaping, SQL-injection/XSS/
CSRF protection, secure file uploads, rate limiting where appropriate,
secure password handling, admin protection, audit logging. Never expose
sensitive info.

## Performance

Lightweight front-end, minimal JS, optimized CSS, responsive images, lazy
loading, correct image dimensions, modern image formats where appropriate,
avoid unnecessary plugins/bloated page builders, good Core Web Vitals,
mobile-first.

## SEO

Semantic HTML, correct heading hierarchy, schema markup where appropriate,
Open Graph + Twitter/X metadata, canonical URLs, XML sitemap
compatibility, robots configuration, breadcrumbs, internal linking, image
alt text, clean URLs, fast loading, mobile optimization. No keyword
stuffing. Built so a professional SEO specialist can keep optimizing it.

## Responsive Design

Works properly (not just shrunk) on large desktop, desktop, laptop,
tablet, mobile, small mobile — nav, pricing cards, template library +
filters, forms, tables, portfolio, images, buttons, footer all
purpose-designed per breakpoint.

## Accessibility

Keyboard navigation, proper labels, visible focus states, alt text,
sufficient contrast, semantic HTML, accessible forms, ARIA only where it
adds value.

## Code Architecture

Modular custom WordPress architecture — no single giant PHP file. Custom
theme, modular PHP, reusable components, separated CSS/JS, custom post
types/taxonomies where appropriate, a well-structured settings approach,
REST/AJAX where appropriate, no unnecessary dependencies. Every custom
feature needs a clear, documented location (see PROJECT_MASTER.md §6 and
§28 File Location Map).

Suggested directory shape (adapted where justified, documented when it
changes):

```
/wp-content/themes/appiappi-theme/
  /assets/{css,js,images}/
  /inc/{admin,api,cpt,settings,template-library,pricing,security,seo,forms}/
  /template-parts/{header,footer,sections,cards}/
  /single/ /archive/ /page/
  /languages/
```

## Companion Plugin Architecture

Decided directly by the user (superseding the original "decide in Phase 2"
placeholder): the **theme** owns only the permanent shell — header, footer,
nav menu, theme setup/registration, design system, base page layout. Every
**dynamic content section** ships as its own **companion plugin** instead
of living inside the theme, so its content can later be placed anywhere
(via shortcode) rather than being locked to one spot in one template.

| Component | Plugin slug | Data model | Shortcode |
|---|---|---|---|
| Hero Slideshow | `appiappi-hero-slider` | CPT `appiappi_slide` (headline, subheadline, image, CTA text/url, order) | `[appiappi_hero_slider]` |
| Pricing Plans | `appiappi-pricing-plans` | CPT `appiappi_plan` (price, period, note, color, icon, featured flag, badge, CTA, features, order) | `[appiappi_pricing]` |
| Template / Design Showcase | `appiappi-template-showcase` | CPT `appiappi_template` + taxonomy `appiappi_template_category` (price, vendor, source/demo URLs, rating, style) | `[appiappi_templates]` |

Rules for every companion plugin:
- Self-contained folder under `wp-content/plugins/<slug>/`, own plugin header, **no third-party dependency** (native meta boxes, not ACF) — keeps with "avoid unnecessary plugins."
- Exposes both a shortcode and a plain PHP render function, so the theme can call it directly.
- Theme checks `function_exists()` / `shortcode_exists()` before calling a plugin, and falls back to its own placeholder data/markup if the plugin isn't installed — the site must never break without the plugins active.
- Default placement mirrors today's homepage position; the point of the shortcode is that the business owner can later move or duplicate that content elsewhere without a code change.

**Build order** (user-specified, one approved step at a time): theme visual
rebuild → Pricing Plans plugin → Template Showcase plugin → Hero Slideshow
plugin → package theme + all plugins as separate installable zips, for
installing on a fresh WordPress site on real hosting at the end of the
project.

## No Hard-Coded Business Data

Never hard-code (outside intentionally static technical elements): prices,
phone, email, address, social links, plan features, template info,
categories, testimonials, FAQs. All must be wp-admin editable. *(Phase 1
placeholder data behind `appiappi_get_pricing_plans()` /
`appiappi_get_featured_templates()` is a deliberate, temporary, single
swap-point exception — see PROJECT_MASTER.md §12–13.)*

## Global Design System / Customisation

Reusable design system (colours, type, spacing, radius, shadow, buttons,
cards, forms, inputs, badges, alerts, tables, nav, modals) via CSS
variables so the whole site is changeable centrally. Site owner should be
able to change, without code: primary/secondary/accent/background/text
colour, button style, radius, logo, favicon, typography, header/footer
layout — without exposing dangerous technical settings to non-technical
users.

## Forms & Lead Management

All forms responsive, accessible, secure, validated, spam-protected, easy
to modify. Submissions visible in wp-admin (not email-only). Lead fields:
name, business, email, phone, industry, selected plan/template, message,
date, status, notes. Statuses: New, Contacted, Qualified, Proposal, Won,
Lost (must be extensible later).

## Payment Architecture

Not required at launch, but don't block adding: Stripe, recurring
subscriptions, one-time payments, invoices, failed-payment handling. Never
store raw card data.

## Canadian Market

Canadian-oriented examples; potential future geo-SEO pages (Toronto,
Vancouver, Calgary, Edmonton, Ottawa, Winnipeg, Halifax, Montreal) —
**only** if they carry genuinely unique content, never thin pages purely
for SEO.

## Trust & Conversion

Every major page needs a logical CTA (e.g. "Explore Website Designs",
"Choose Your Plan", "Book a Free Consultation", "Start Your Website", "Get
Started") — avoid CTA overload.

## Footer & Legal

Footer: logo, short description, nav, services, website designs, pricing,
resources, contact, social, legal links (Privacy Policy, Terms, Cookie
Policy), Canadian business info. Legal pages start as clearly-marked
placeholders — no invented compliance claims — to be replaced with
reviewed legal content later.

## Future Scalability

Design for 100 → 500 customers, multiple admins/managers, customer portal,
automated onboarding/billing/reporting, SEO dashboards, support tickets,
staff workload management — without over-engineering the first version.
Build a strong, simple foundation first.

## Development Phases

1. Architecture, design system, theme, global settings, header, footer, homepage, responsive foundation, docs
2. Pricing system, Services, How It Works, About, Contact, FAQ, Portfolio, Blog
3. Template Library (categories, search, filters, detail pages, selection workflow)
4. Lead management, admin settings, SEO foundation, performance, security hardening
5. Customer portal, support system, staff architecture, payment architecture

*(Status of each phase: PROJECT_MASTER.md §27.)*

## Development Workflow Rules

- Inspect before building; don't destroy existing work — preserve, explain conflicts, prefer reversible steps.
- Don't ask unnecessary questions for reasonable, low-risk decisions — make the call. **Do** flag before implementing anything that materially affects cost, security, architecture, licensing, legal compliance, data/customer ownership, payment processing, hosting, or SEO strategy.
- After each meaningful operation: report what changed, which files, what it means for the user, check/update documentation, suggest concrete next steps — never just "Done."
- Every user-requested change: apply it in code, then update PROJECT_MASTER.md / CHANGELOG.md / DEVELOPMENT_LOG.md (when relevant) / this file, in the same pass.
- **User controls phase timing** — do not jump ahead to a new build phase without an explicit go-ahead, even though the rule above says not to over-ask; "more context/reference material" is not the same as "start building" (see the project's feedback memory on phase gating).

## Quality Bar

No dummy-looking layouts, generic WordPress styling, poor mobile layouts,
excessive plugins, bloated code, hard-coded business data, fake
testimonials/stats/awards/clients/SEO results, broken forms, inaccessible
controls, unnecessary animation, or unmaintainable code. Must read as a
real commercial product.

## Acceptance Criteria (project-level)

Design reads premium/Canadian/trustworthy; a visitor understands the
business in seconds; visitors can easily browse designs, view pricing,
select a design, and request consultation; business owners can edit major
content without code; templates and pricing are fully manageable; the
architecture is SEO-ready, lightweight, responsive across all major sizes,
follows WP security best practice, and is documented well enough that
another developer/AI can pick it up from PROJECT_MASTER.md alone.
