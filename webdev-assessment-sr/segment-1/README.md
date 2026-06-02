# Website Studio — Landing Page (`starter-page.html`)

A single-file, dependency-light marketing landing page for **Website Studio**, Search Atlas's AI landing page builder. Everything — markup, styles, and behavior — lives in one `starter-page.html` so it can be dropped onto any static host (or the Docker/nginx setup in this repo) with zero build step.

> This is the Segment 1 deliverable of the Senior Webdev Assessment. It started partially built and was completed across three tasks (A, B, C) — see [What was built](#what-was-built).

---

## Table of contents

- [Quick start](#quick-start)
- [Tech & dependencies](#tech--dependencies)
- [Design tokens](#design-tokens)
- [Page structure](#page-structure)
- [CSS architecture](#css-architecture)
- [JavaScript](#javascript)
- [Heading hierarchy (SEO)](#heading-hierarchy-seo)
- [HubSpot form + GTM tracking](#hubspot-form--gtm-tracking)
- [What was built](#what-was-built)
- [File map](#file-map)

---

## Quick start

It's a static file — open it directly, or serve it.

```bash
# Option A: just open it
xdg-open starter-page.html        # Linux
open starter-page.html            # macOS

# Option B: serve over the repo's Docker stack (nginx + php-fpm)
#   from the repo root (attest/):
docker compose up -d
#   then visit:
#   http://localhost/segment-1/starter-page.html
```

No `npm install`, no bundler, no framework.

---

## Tech & dependencies

| Concern | Choice | Notes |
|---|---|---|
| Markup | Semantic HTML5 | `<nav>`, `<section>`, `<footer>`, proper heading levels |
| Styling | Vanilla CSS in a single `<style>` block | CSS custom properties for theming; no preprocessor |
| Fonts | [Inter Tight](https://fonts.google.com/specimen/Inter+Tight) via Google Fonts | preconnected for faster load |
| Icons | [Font Awesome 6.5](https://fontawesome.com/) via CDN | used in nav, features, comparison table, footer |
| Forms | HubSpot Forms embed (`v2.js`) | loaded only where needed, before `</body>` |
| Analytics | Google Tag Manager via `dataLayer` | event-driven; see [tracking](#hubspot-form--gtm-tracking) |

There is **no JS framework and no build tooling** — intentional. The page is small enough that vanilla CSS + a few lines of JS keeps it fast, cache-friendly, and trivially portable.

---

## Design tokens

All theming flows from CSS custom properties on `:root`. Change a value once and it propagates everywhere.

```css
:root {
  /* color */
  --black: #121212;
  --bg: #000000;
  --bg-alt: #0f0f0f;
  --white: #ffffff;
  --text-muted: rgba(255,255,255,0.6);
  --text-dim: rgba(255,255,255,0.48);
  --teal: #00ffd4;          /* primary CTA / accent */
  --purple-hero: #a16eff;   /* hero glow, step numbers, accents */
  --purple-link: #926bd9;
  --purple-faq: #b88fff;

  /* surfaces & borders */
  --card-border: rgba(170,133,236,0.48);
  --card-border-light: rgba(170,133,236,0.24);
  --card-bg: rgba(255,255,255,0.06);
  --card-icon-border: rgba(255,255,255,0.12);
  --divider: rgba(255,255,255,0.24);

  /* layout */
  --max-w: 1200px;          /* shared content max width */
  --section-pad: 120px 0;   /* vertical rhythm for .section */
  --font: 'Inter Tight', sans-serif;
}
```

**Palette at a glance:** near-black canvas, teal (`--teal`) for primary actions, purple (`--purple-hero`) for brand glow and accents. Text uses opacity-based whites (`--text-muted`, `--text-dim`) for hierarchy rather than separate gray values.

---

## Page structure

Top-to-bottom, every major block is a `<section>` (or `<nav>`/`<footer>`). Sections that share the standard frame use `.section` (120px vertical padding) wrapping `.section-inner` (max-width + horizontal padding).

| # | Block | Element / class | Purpose |
|---|---|---|---|
| 1 | **Nav** | `<nav class="nav">` | Sticky top bar: logo, links, teal CTA. Blurred translucent background. |
| 2 | **Hero** | `<section class="hero">` | The single `<h1>`, sub-headline, two CTAs. Radial-gradient glows via `::before`/`::after`. |
| 3 | **Testimonial** | `<section class="section">` → `.testimonial` | Centered customer quote + avatar + attribution. |
| 4 | **Stats** | `.section` → `.stats-grid` | Four `.stat-card`s (10X, 90+, 100%, 0) with a glow gradient. |
| 5 | **What Is** | `<section class="what-is">` | Two-column: copy block (`<h2>`) + product image. |
| 6 | **Features** | `.section` → `.features-row` | 3 + 4 + 3 icon/title/description items across three rows. |
| 7 | **Getting Started** | `.section` → `.steps-grid` | **Built in Task A.** Three numbered `.step-card`s, each with an `<h3>` title, description, and teal CTA. |
| 8 | **Comparison** | `.section` → `.comparison-table` | Feature matrix: Website Studio vs Wix / Framer / Webflow / WordPress. |
| 9 | **FAQ** | `.section` → `.faq-list` | Accordion of `.faq-item`s with `<h3>` questions; tab pills above. |
| 10 | **Form** | `.section` → `.form-section` | "Ready to Build?" CTA wrapping the HubSpot form target. |
| 11 | **Footer** | `<footer>` | Brand, socials, link columns, copyright + legal row. |

### Getting Started section anatomy (Task A)

```
section.section
└── div.section-inner
    ├── h2.section-heading          ← section title
    ├── p.section-subheading         ← supporting copy
    └── div.steps-grid               ← 3-col grid (margin-top: 48px)
        └── div.step-card  (×3)      ← gap: 24px, rounded card
            ├── div.step-card-top    ← gap: 32px
            │   ├── div.step-num      ← 64×64 purple circle (01/02/03)
            │   └── div.step-text     ← gap: 12px  (the one new class added)
            │       ├── h3.step-title
            │       └── p.step-desc
            └── a.btn-primary         ← full-width teal CTA
```

Every class above **already existed** in the stylesheet except `.step-text` (the title+description wrapper). Spacing comes entirely from the existing classes — `.section` provides the 120px vertical padding, `.section-heading` the 12px gap below the heading, `.steps-grid` the 48px gap above the cards — so the section needs **zero inline styles**.

---

## CSS architecture

- **Single `<style>` block** in `<head>`, organized into labelled regions with `/* ── NAME ── */` banners (NAV, HERO, SECTION WRAPPER, TESTIMONIAL, STATS, WHAT IS, SECTION HEADING, FEATURES, GETTING STARTED, COMPARISON, FAQ, FORM, FOOTER, RESPONSIVE).
- **Reset:** `*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }`
- **Tokens first:** all colors/spacing pulled from `:root` custom properties.
- **Reusable primitives:** `.section` / `.section-inner` (page frame), `.section-heading` / `.section-subheading` (centered headers), `.btn-primary` / `.btn-secondary` (CTAs). New sections compose these instead of redefining them.
- **Scoped overrides over inline styles:** e.g. full-width step CTAs use `.step-card .btn-primary { width: 100%; justify-content: center; }` so the hero's buttons stay untouched.
- **Responsive:** two breakpoints — `@media (max-width: 1100px)` and `@media (max-width: 768px)` — collapse multi-column grids to fewer columns / single column and tighten nav padding.

### Type scale (responsive headings use `clamp()`)

| Element | Size |
|---|---|
| `.hero-headline` (`h1`) | `clamp(48px, 6vw, 88px)` |
| `.section-heading` / `.what-is-heading` (`h2`) | `clamp(36–40px, 4.5vw, 64px)` |
| `.step-title` / `.faq-question-text` (`h3`) | `24px` / `clamp(20px, 2.5vw, 32px)` |

---

## JavaScript

All scripts sit at the end of `<body>`. There are two concerns, both tiny and vanilla:

1. **FAQ accordion** — `toggleFaq(el)` opens one item at a time and swaps the `+`/`−` toggle glyph; `setTab(btn)` switches the active filter pill.
2. **HubSpot form embed** — loads `v2.js`, calls `hbspt.forms.create(...)`, and pushes a GTM event on successful submit (below).

No event-delegation framework, no bundling — the handlers are wired via inline `onclick` for the accordion and a single `create()` call for the form.

---

## Heading hierarchy (SEO)

The page enforces a clean, single-rooted outline (fixed in Task B):

```
h1  Meet Website Studio                      (hero — the only h1)
├── h2  What Is Website Studio?
├── h2  What Makes Website Studio the Best…
├── h2  Getting Started…
│   ├── h3  Describe your page in a single prompt
│   ├── h3  Edit with commands or visual controls
│   └── h3  Publish and Launch instantly
├── h2  Why Website Studio Outperforms…
├── h2  Frequently Asked Questions
│   ├── h3  What is an AI landing page builder?
│   ├── h3  How does an AI landing page builder work?
│   ├── h3  Who is Website Studio built for?
│   └── h3  Do I need coding experience…?
└── h2  Ready to Build Your First Page?
```

**Exactly one `<h1>`**, no skipped levels, every `<h3>` nested under an `<h2>`. The `<head>` also carries a descriptive `<title>` and a `<meta name="description">`, and all content images have meaningful `alt` text.

---

## HubSpot form + GTM tracking

The form renders into `<div id="hubspot-form-target"></div>` via HubSpot's embed loader:

```html
<script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/embed/v2.js"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  hbspt.forms.create({
    region: "na1",
    portalId: "00000000",                            // placeholder
    formId: "00000000-0000-0000-0000-000000000000",  // placeholder
    target: "#hubspot-form-target",
    onFormSubmitted: function ($form, data) {
      window.dataLayer.push({
        event: 'hubspot_form_submit',
        form_name: 'Website Studio Demo Request',
        form_id: '00000000-0000-0000-0000-000000000000'
      });
    }
  });
</script>
```

**To go live:** replace `portalId` and `formId` with the real Hub ID and form GUID from your HubSpot portal.

**GTM setup (the tracking story):**

- The push fires from `onFormSubmitted`, which runs **only after HubSpot validates and accepts** the submission — a real conversion, not a click.
- In GTM, create a **Custom Event** trigger matching `event = hubspot_form_submit`, and fire a GA4 / Ads conversion tag off it. Map `form_name` / `form_id` to dataLayer variables → event parameters.
- **Why not GTM's built-in Form Submission trigger?** HubSpot renders the form inside a **cross-origin `<iframe>`**, so GTM's DOM-based `gtm.formSubmit` listener on the parent page can't see the submit at all. The native listener also fires on submit *attempt* (pre-validation), counting failed submits as conversions. The callback + custom event avoids both problems and is decoupled from HubSpot's markup.

---

## What was built

This page began partially complete. Three tasks finished it:

| Task | Title | Summary |
|---|---|---|
| **A** | Match the Figma section | Built the **Getting Started** 3-step section against `figma-spec-section.html`, reusing existing classes (only `.step-text` is new) and zero inline styles. |
| **B** | Fix heading hierarchy & SEO | Descriptive `<title>` + `<meta description>`; collapsed two `<h1>`s to one; promoted FAQ `<h4>`→`<h3>` to remove the level skip; added meaningful `alt` text. |
| **C** | Form + GTM tracking | Embedded the HubSpot form and wired an `onFormSubmitted` → `dataLayer.push` conversion event for GTM. |

---

## File map

```
segment-1/
├── starter-page.html       ← this page (markup + CSS + JS, all-in-one)
├── figma-spec-section.html ← Task A design reference (annotated Figma spec)
├── README.md               ← you are here
└── CLAUDE.md               ← notes/conventions for AI-assisted edits
```
