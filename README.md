# Webdev Assessment — Milorad Djukovic

Senior Webdev Assessment submission: a **Website Studio** marketing landing page,
served by a small, production-shaped **Docker** stack (nginx in front of PHP-FPM).

- 🐳 **Infrastructure:** nginx + PHP-FPM via Docker Compose — see [Docker infrastructure](#-docker-infrastructure)
- 🌐 **The page:** a single-file, framework-free HTML/CSS/JS landing page — see [The HTML project](#-the-html-project)

```
┌─────────────┐        :80          ┌──────────────────┐   fastcgi :9000   ┌────────────────┐
│  Your other │  ───────────────▶   │  nginx (alpine)  │  ───────────────▶ │  php-fpm 8.3   │
│  PC / LAN   │   http / SSH tunnel │  static + proxy  │   .php requests   │  (alpine)      │
└─────────────┘                     └──────────────────┘                   └────────────────┘
                                       serves /var/www/html  ◀── mounted ──  ./webdev-assessment-sr
```

---

## Repository layout

```
│                                    ← repo root (this README)
├── docker-compose.yml               ← orchestrates the two services
├── .env                             ← host UID/GID for non-root file ownership
├── docker/
│   ├── php/
│   │   └── Dockerfile               ← PHP 8.3-FPM (alpine) + opcache
│   └── nginx/
│       ├── Dockerfile               ← nginx 1.27 (alpine)
│       └── default.conf             ← serves static files, proxies .php → php:9000
└── webdev-assessment-sr/            ← the web root (mounted into both containers)
    ├── index.php                    ← landing/index (lists the pages, proves PHP works)
    ├── candidate-brief-senior.html  ← the assessment brief
    └── segment-1/
        ├── starter-page.html        ← ⭐ the deliverable landing page
        ├── figma-spec-section.html  ← Task A design reference
        ├── README.md                ← deep-dive docs for starter-page.html
        └── CLAUDE.md                ← editing conventions for the page
```

---

## 🐳 Docker infrastructure

Two single-purpose images wired together with Docker Compose. **nginx is the only
service exposed to the host**; PHP-FPM is reachable only on the internal Docker
network, which is how you'd run it in production.

### Services

| Service | Image | Exposed | Role |
|---|---|---|---|
| `nginx` | `nginx:1.27-alpine` | host **`:80`** → container `:80` | Public entrypoint. Serves static HTML/CSS/JS directly; reverse-proxies `*.php` to PHP-FPM. |
| `php` | `php:8.3-fpm-alpine` + opcache | internal `:9000` only | Executes PHP. Never published to the host — nginx talks to it over the `attest` bridge network. |

Both containers mount `./webdev-assessment-sr` at `/var/www/html` **read-only**
(`:ro`), so the running site can't be modified from inside a container — edits
happen on the host and are picked up live (no rebuild needed for content changes).

### How requests flow

- `GET /segment-1/starter-page.html` → nginx serves the file from disk.
- `GET /index.php` → nginx matches `location ~ \.php$`, hands it to `php:9000`
  via FastCGI, returns the rendered HTML.
- `/.git` is explicitly denied in `default.conf`.
- Directory listing (`autoindex`) is on, so you can browse the tree.

### Why this shape

- **Separation of concerns** — a battle-tested static server (nginx) does TLS/
  static/routing; a dedicated FPM pool does PHP. Either can be scaled or swapped
  independently.
- **Smaller attack surface** — only port 80 is published; PHP isn't internet-facing.
- **Reproducible** — alpine bases keep images tiny; `opcache` speeds PHP; the
  `.env` `UID`/`GID` make mounted files owned by *you*, not root.

### Run it

From the repo root:

```bash
docker compose up -d --build      # build images + start (detached)
docker compose ps                 # check status
docker compose logs -f nginx      # tail nginx logs
docker compose down               # stop & remove containers
```

Then open **http://localhost/** (index) or
**http://localhost/segment-1/starter-page.html** (the page).

> **`.env`** holds `UID`/`GID` (default `1000`). Compose reads it automatically so
> the PHP image builds a `www-data` user matching your host user — that keeps
> mounted files from being owned by root. Regenerate with:
> `printf 'UID=%s\nGID=%s\n' "$(id -u)" "$(id -g)" > .env`

### Reach it from another machine

The site binds to `0.0.0.0:80`, so on the same LAN just visit
`http://<host-lan-ip>/`. To avoid exposing it on the network, use an **SSH tunnel**
from the other machine instead:

```bash
ssh -L 8080:localhost:80 <user>@<host-lan-ip>
# then browse http://localhost:8080 on the second machine
```

A `local.atlas.com` hosts-file entry pointing at the host IP also works (port 80,
no suffix needed).

---

## 🌐 The HTML project

`webdev-assessment-sr/segment-1/starter-page.html` is the deliverable: a marketing
landing page for **Website Studio**, Search Atlas's AI landing-page builder.

### Principles

- **Single file** — markup + a single `<style>` block + a little vanilla JS. No
  framework, no bundler, no build step. Double-click to open.
- **Token-driven CSS** — colors and spacing live as CSS custom properties on
  `:root`; everything else is `var(--token)`.
- **Reuse over re-create** — sections compose existing primitives (`.section`,
  `.section-inner`, `.section-heading`, `.btn-primary`, …) rather than inventing
  one-off classes.
- **Semantic + accessible** — real `<section>`/`<nav>`/`<footer>`, a correct
  single-rooted heading outline, meaningful `alt` text.

### Page sections (top → bottom)

Nav · Hero · Testimonial · Stats · What-Is · Features · **Getting Started** ·
Comparison table · FAQ accordion · HubSpot form · Footer.

> A much deeper breakdown — design tokens, every section's anatomy, the CSS
> architecture, the heading outline, and the HubSpot/GTM wiring — lives in
> [`webdev-assessment-sr/segment-1/README.md`](webdev-assessment-sr/segment-1/README.md).

### Assessment work completed

| Task | Title | What was done |
|---|---|---|
| **A** | Match the Figma section | Built the **Getting Started** 3-step section from `figma-spec-section.html`, reusing existing classes (only `.step-text` is new) with **zero inline styles**. |
| **B** | Fix heading hierarchy & SEO | Descriptive `<title>` + `<meta description>`; collapsed two `<h1>`s to one; promoted FAQ `<h4>`→`<h3>` to remove the level skip; added meaningful image `alt` text. |
| **C** | Form + GTM tracking | Embedded the HubSpot form and wired an `onFormSubmitted` → `dataLayer.push('hubspot_form_submit')` so GTM fires a conversion off a **Custom Event** trigger (reliable across HubSpot's cross-origin iframe). |

---

## Tech summary

| Layer | Choice |
|---|---|
| Web server | nginx 1.27 (alpine) |
| App runtime | PHP 8.3-FPM (alpine) + opcache |
| Orchestration | Docker Compose |
| Frontend | Semantic HTML5, vanilla CSS (custom properties), vanilla JS |
| Fonts / icons | Inter Tight (Google Fonts), Font Awesome 6.5 |
| Forms / analytics | HubSpot Forms `v2.js`, Google Tag Manager (`dataLayer`) |

---

## Author

**Milorad Djukovic** — Senior Webdev Assessment
Repo: https://github.com/donmilorad87/webdev-assessment-milroad-djukovic
