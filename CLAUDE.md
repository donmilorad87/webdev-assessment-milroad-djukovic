# CLAUDE.md — repo guide for `attest/` 🤖

This repo is a **Dockerized static/PHP host** wrapping a **single-file landing page**.
Read this before changing anything; the design is deliberately small and there are
a couple of load-bearing details that look optional but aren't.

> TL;DR: nginx is the only exposed service · PHP-FPM stays internal · the web root
> is mounted **read-only** · the page is vanilla + token-driven · reuse a class
> before you write one.

---

## Mental model

```
host :80  →  nginx (serves static, proxies *.php)  →  php-fpm :9000 (internal only)
                         ↑ both mount ./webdev-assessment-sr at /var/www/html (read-only)
```

- **`docker/nginx/default.conf`** is the routing brain: static `try_files`,
  `location ~ \.php$ → php:9000`, `autoindex on`, and a `deny` on `/.git`.
- **`docker/php/Dockerfile`** builds a `www-data` user from `UID`/`GID` (via `.env`)
  so mounted files aren't root-owned.
- **`docker-compose.yml`** publishes **only** `80:80` on nginx. PHP has no `ports:`.

---

## Golden rules

1. **Don't publish PHP.** Never add a `ports:` mapping to the `php` service. nginx
   reaching it over the internal network is the whole point.
2. **Keep the web-root mount read-only (`:ro`).** Content is edited on the host,
   not inside containers. If something "needs" write access in the container,
   reconsider — it almost certainly doesn't.
3. **`.env` is required for builds.** It carries `UID`/`GID`. If a fresh clone
   fails to build, regenerate it:
   `printf 'UID=%s\nGID=%s\n' "$(id -u)" "$(id -g)" > .env`
4. **Edit the page through its own rules.** `segment-1/` has its **own CLAUDE.md** —
   follow it for any change to `starter-page.html` (reuse classes, no inline styles,
   tokens not magic numbers, one `<h1>`, no skipped heading levels).
5. **Stay framework-free, top to bottom.** No Node build step for the page; no
   bloating the images. Alpine bases, minimal layers.

---

## Common tasks

```bash
# bring the stack up / rebuild after Dockerfile or conf changes
docker compose up -d --build

# content-only change (HTML/CSS/JS)? no rebuild needed — it's a live mount.
# just refresh the browser.

# logs / status / teardown
docker compose logs -f nginx
docker compose ps
docker compose down
```

- **Change a port:** edit `nginx.ports` in `docker-compose.yml` (`"80:80"`).
- **Change routing / add a deny / tweak autoindex:** `docker/nginx/default.conf`,
  then `docker compose up -d` (recreate nginx).
- **Add a PHP extension:** add a `docker-php-ext-install …` line in
  `docker/php/Dockerfile`, then rebuild.

---

## Tripwires (ask before you yank)

- **`autoindex on`** in nginx is intentional for this assessment (browse the tree).
  Turn it off for any real deployment.
- **HubSpot `portalId`/`formId` in the page are placeholders** (`00000000…`) —
  fake on purpose, swapped at deploy time. Don't "fix" them.
- **The GTM event name `hubspot_form_submit`** is the contract between the page's
  `dataLayer.push` and the GTM Custom Event trigger. Rename one, break the other.
- **`.git` is denied in nginx** — keep that rule if you copy this config elsewhere.

---

## Health check

```bash
# is the stack serving?
curl -s -o /dev/null -w "static -> %{http_code}\n"  http://localhost/segment-1/starter-page.html
curl -s -o /dev/null -w "php    -> %{http_code}\n"  http://localhost/index.php
# both should print 200
```

If those are green and PHP renders, you're good. Deeper page-level conventions
live in [`webdev-assessment-sr/segment-1/CLAUDE.md`](webdev-assessment-sr/segment-1/CLAUDE.md). 🌱
