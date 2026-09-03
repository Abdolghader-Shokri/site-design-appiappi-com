# Appiappi — Managed Website Platform for Canadian Businesses

Custom WordPress platform for a Canadian company that designs, builds, hosts, and manages
websites (with ongoing SEO and content support) for small and medium-sized Canadian
businesses — construction, legal, medical, real estate, professional services, and similar
non-marketplace industries.

**Status:** Pre-development. No code has been written yet. This repository currently
contains only project scaffolding (`.gitignore`, this `README.md`).

## Business model

Customers pick a professionally curated website design, and the company customizes it with
their branding and content, launches it, and (optionally) manages it long-term.

| Plan | Type | Summary |
|---|---|---|
| Starter | $199 one-time | WordPress install, theme install, SSL, domain connection |
| Business | $399 one-time | Full customization, content, forms, basic on-page SEO |
| Professional | $699 one-time | Premium theme, 1yr hosting, custom logo, speed/security setup — **Most Popular** |
| Growth | $599/month | Ongoing managed hosting, maintenance, SEO, content, support |
| SEO Growth *(future)* | $899/month | Advanced SEO, content strategy, local SEO — not yet public |

All pricing, plan features, and template/service data are intended to live in WordPress
(custom post types / settings), not be hard-coded in templates.

## Target outcome

A premium, conversion-focused, English-language site (Canadian business audience) covering:
homepage, pricing, a browsable **Website Designs** template library, services, portfolio,
blog, FAQ, and contact/lead capture — backed by a modular, documented WordPress theme
architecture that can scale toward a future customer portal, support-ticket system, staff
accounts, and payments.

## Tech stack (planned)

- WordPress (custom theme, no page builder dependency)
- Custom post types & taxonomies for templates, portfolio, FAQs, pricing plans
- Modular PHP (`/inc/`), separated CSS/JS, mobile-first responsive design
- No hard-coded business data (pricing, contact info, templates, categories all editable
  from wp-admin)

## Project documentation

Full architecture and specification docs (`PROJECT_MASTER.md`, `MASTER_PROMPT.md`,
`CHANGELOG.md`, `DEVELOPMENT_LOG.md`) will be added once implementation begins.

## Language

The entire site is built and copywritten in English for a Canadian business audience.
