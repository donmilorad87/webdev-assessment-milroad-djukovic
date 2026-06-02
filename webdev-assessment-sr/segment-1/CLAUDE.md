# CLAUDE.md — field notes for `starter-page.html` 🤖

Hello, future Claude (or curious human). This is a single-file landing page that
likes to keep things simple. Read this before you start moving things around —
it'll save you a refactor and me an existential crisis.

> TL;DR: **One file. Vanilla everything. Reuse a class before you write one.
> Touch the `<style>` block, not the elements.**

---

## The golden rules

1. **Reuse before you create.** Almost every layout you need already has a class
   in the `<style>` block — `.section`, `.section-inner`, `.section-heading`,
   `.section-subheading`, `.btn-primary`, `.steps-grid`, `.step-card`, … Search
   first. The whole "Getting Started" section was built by reusing existing
   classes; only **one** new class (`.step-text`) had to be born. Honor that.

2. **No inline styles.** If you're reaching for `style="..."`, stop. Add a class
   (or a scoped rule like `.step-card .btn-primary { … }`) instead. The few inline
   styles that remain are legacy, not a license.

3. **Tokens, not magic numbers.** Colors and key spacing live in `:root` as CSS
   custom properties (`--teal`, `--purple-hero`, `--max-w`, `--section-pad`, …).
   Use `var(--token)`. If you need a new constant, add a token.

4. **One `<h1>`. No skipped heading levels.** The outline is H1 → H2 → H3 and it
   is *correct right now*. Don't undo that. New sub-items under an `<h2>` get an
   `<h3>`, never an `<h4>`-because-it-looked-right.

5. **Semantic HTML wins.** `<section>`, `<nav>`, `<footer>`, real headings, real
   `<a>`/`<button>`. Images get meaningful `alt`. Buttons that navigate are `<a>`.

6. **Stay framework-free.** No React, no Tailwind, no build step. This file must
   stay openable with a double-click. If a task seems to "need" a framework,
   it probably doesn't.

---

## Where things live

- **Styles:** one `<style>` block in `<head>`, divided by `/* ── NAME ── */`
  banners (NAV, HERO, SECTION WRAPPER, … FOOTER, RESPONSIVE). Put new rules in the
  matching region; if it's a new section, add a new banner.
- **Scripts:** at the very end of `<body>`. FAQ accordion + HubSpot embed. Keep
  new JS here, vanilla, and small.
- **Responsive:** two breakpoints — `1100px` and `768px`. If you add a grid,
  add its collapse rule there too.

---

## Recipes

**Add a new section** (the blessed pattern):
```html
<section class="section">
  <div class="section-inner">
    <h2 class="section-heading">Your heading</h2>
    <p class="section-subheading">Optional supporting line.</p>
    <!-- your content -->
  </div>
</section>
```
`.section` already gives you 120px vertical padding; `.section-inner` gives the
max-width + side padding. You don't need to set either.

**Add a CTA:** `<a href="#" class="btn-primary">Do The Thing</a>` (teal) or
`.btn-secondary` (outline). Need it full-width inside a card? Scope it:
`.your-card .btn-primary { width: 100%; }` — don't widen the global button.

**Change the theme:** edit a token in `:root`. One line, whole page updates.

---

## Tripwires (ask before you yank)

- The **HubSpot `portalId` / `formId` are placeholders** (`00000000…`). They're
  *supposed* to be fake here. Don't "fix" them with random values — they get
  swapped for real portal IDs at deploy time.
- The **`onFormSubmitted` → `dataLayer.push`** is load-bearing for analytics.
  If you change the `event` name (`hubspot_form_submit`), you break the GTM
  trigger that depends on it. Keep them in sync.
- **`.step-num` ≡ `.preview-step-num`** from the Figma spec — same values, cleaner
  name. Use `.step-num`. Don't reintroduce `preview-*` classes; those only exist
  in the spec file as annotation scaffolding.

---

## Sanity checks before you call it done

```bash
# exactly one h1, zero stray h4, no empty alts, no leftover TODO/BUG markers
grep -oc '<h1'  starter-page.html      # → 1
grep -oc '<h4'  starter-page.html      # → 0
grep -c  'alt=""' starter-page.html    # → 0
grep -c  'BUG'  starter-page.html      # → 0
```

If those four numbers are right and the page still opens in a browser, you're
probably fine. Now go touch some grass. 🌱
