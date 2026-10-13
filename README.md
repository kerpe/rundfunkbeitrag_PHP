# Rundfunkbeitrag Support — PHP / MySQL deployment

Hostinger deployment branch: **hostinger-php-2026-10-12**. This branch contains PHP application files, bundled PHP PDF dependencies and precompiled browser JavaScript/CSS. There are no npm manifests, Vite configuration files or Node server/test programs in this branch. No hosting build or start command is required.

The editable React/Vite client sources and development tests are preserved on [php-source-2026-10-12](https://github.com/evaldas0905/business-mvp/tree/php-source-2026-10-12). The existing default `main` branch still contains the older Node application. Always select the PHP deployment branch explicitly in Hostinger.

Use Hostinger's PHP/HTML Git integration: **Websites → your website → Dashboard → Advanced → Git**. Select repository `evaldas0905/business-mvp` and branch `hostinger-php-2026-10-12`. Do not use the Node Web App import workflow for this PHP application.

The repository contains sibling directories `public_html` and `private`. The server document root must point to the repository's `public_html`; `private` must remain outside the public web root. Hostinger's “Root directory” in Git settings is a deployment destination, not a source subfolder selector. Deploy to the domain directory one level above its public_html if your account allows this. If it only allows cloning inside public_html, clone to a non-live temporary directory, then use Hostinger File Manager to move the repository's public_html contents into the real public_html and move private alongside it, outside the web root. Confirm the actual destination with your Hostinger account; do not blindly overwrite existing files or enable automatic deployment while using a manual move workflow.

Read [the Lithuanian Hostinger installation guide](README_HOSTINGER_PHP_LT.md) and [validation results](VALIDATION.md).

GitHub intentionally contains no installation secret or production configuration. Copy `private/installation-key.example.php` to `private/installation-key.php` in Hostinger File Manager and replace its placeholder with at least 24 cryptographically random characters retained privately. Then open `https://YOUR_DOMAIN/install`. The key file is deleted after successful installation.

Choose an administrator email/password during installation; sign in at `/admin/login`. Configure your business details, bank-transfer instructions and verified mail sender before enabling paid orders. No credentials, real client records or uploads are included.
