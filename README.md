# Rundfunkbeitrag Support (PHP / MySQL)

Live site: https://rundfunkbeitragsupport.de (Hostinger). Plain PHP 8.3 + MySQL, no build step on the server.

## Layout

| Path | What it is |
|---|---|
| `public_html/` | Web root: `index.php` (router), compiled frontend in `assets/`, `.vite/manifest.json` (names the current bundle files), fonts, icons, brand images. |
| `private/app/` | Server code: `api.php` (customer API), `staff.php` (admin), `web.php` (installer, login, server pages), `validation.php`, `mail*.php`, `documents.php` (PDF), `seo.php`, `services.php` and `articles.php` (server-rendered service pages and guides), `rules.json` (form rules). |
| `private/cron.php` | Hourly job (mail retries, clean-up). |
| `private/vendor/` | Bundled dompdf. |
| `tools/` | Scripts that change the compiled frontend (see below). |

`private/config.php`, `private/installation-key.php` and `private/data/` exist only on the server and are never committed.

## Deploy

Every push to `main` is uploaded by `.github/workflows/deploy.yml` over FTP (about 10 minutes). It never touches `config.php` or `data/`, and it does not delete files on the server. Hostinger's own Git deployment must stay **off**: it once copied the repository into `public_html` and broke the site.

A fresh installation: copy `private/installation-key.example.php` to `private/installation-key.php` with your own random key (24+ characters), open `https://<domain>/install`, then fill in the business, bank and legal texts under `/admin` → Nustatymai.

## Changing the frontend

The React sources are not in this repository; `public_html/assets/entry-<hash>.js` is the compiled bundle and changes are made to it directly by small scripts:

- CSS: edit `tools/theme/shared.css` (or `tools/admin/*.css`), then run `python3 tools/theme/build.py`. Never edit the generated `entry-*.css`.
- Form rules: after changing form fields in the bundle, run `node tools/rules/generate-rules.js public_html/assets/entry-<hash>.js private/app/rules.json private/app/rules.json` so the server validates the same fields.
- Admin texts in Lithuanian: `tools/admin/lt.json`.
- `tools/admin/*-patch.py` and `tools/datefield/patch.js` hold earlier bundle changes; they are applied already and only needed when rebuilding from an older bundle.

When the bundle changes, save it under a new hash name and update `.vite/manifest.json`, so browsers do not keep the old file.

## Testing locally

Install MariaDB, create a database, write a `private/config.php` like `config.example.php` and run `php -S localhost:8080 <router>` from `public_html`, where the router serves existing files and otherwise includes `index.php`. Defining `RB_TEST_MAIL_CAPTURE` (a file path) in the router writes outgoing mails to that file instead of sending them.
