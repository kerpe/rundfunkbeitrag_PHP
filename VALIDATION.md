# PHP migration validation — 2026-10-12

Source: Rundfunkbeitrag Support — Version 19 review, project `appgprj_6ac743a978048191812f3a0520b66a57`, saved source commit `00cf71e076afe7fa338a5510bb7cfbf4ff115182`.

## Completed checks

- Clean frontend dependency installation from the standalone lockfile: `npm ci`. No dependency on the previous Node backend.
- TypeScript: `npm run check` passed for the client sources.
- Production Vite build passed; the compiled JavaScript and CSS are included under `public_html`. The existing Manrope font URL is served by the included local file. Vite reports a bundle-size advisory; this does not prevent the build.
- PHP syntax: all 13 application, entry-point, example configuration and test-router files passed PHP 8.3 lint.
- HTTP integration against actual PHP 8.3.6 and MariaDB 10.11.14 using PDO MySQL, including installation from an empty database through the browser-facing installer.
- Administrator authentication, incorrect password, sign-out, persistent sessions after a PHP restart, client/admin access separation, ignored forged authentication headers, CSRF and same-origin checks, and inaccessible private files.
- Live-order setup gate, required-field/date validation, all seven service routes, document upload/download protection, one-time email verification, order creation and idempotent replay, frozen contract snapshots and real PDF downloads.
- Full administrator workflow for the abroad service: claim, manually recorded bank payment, authority and evidence review, invoice, submission receipt, recorded submission, official response document and closed state.
- Payment-required invoice guard; single-use email access recovery, replacement of the previous customer's browser access, withdrawal request, immediate hold, administrative review and closure when no payment was received.
- Actual Dompdf PDF rendering. Three-page sample was visually reviewed using Poppler. Font fallback was corrected; form choice values use readable translated labels. The final version was regenerated and reviewed.
- Data survived a PHP HTTP server restart; seven submitted cases remained accessible to the administrator.

## Test boundaries

No customer data or production configuration was imported. All integration records, passwords, bank references and uploaded file contents were local fixtures and were removed after testing. The MySQL fixture database is separate from any user database.

Email requests were captured by a test-only PHP router; no real emails, bank transfers or official submissions were sent. The production entry point does not load this router or offer a test switch. Real Resend delivery and the user's sender-domain configuration still require verification after Hostinger installation.

Interactive browser visual QA was unavailable in this environment. The client components, styles, logo and font files are retained from the approved site, and the production build and PHP HTTP flows were checked. Check desktop/mobile appearance and the client steps in a browser after uploading.

This validates the local PHP/MySQL implementation, not a deployment to the user's Hostinger account. Hostinger's PHP extensions, directory permissions, HTTPS, rewrite rules, database credentials and cron configuration must be set in that account as described in the Lithuanian README.

## Deployment packaging check — 2026-10-12

The PHP deployment branch excludes the entire optional `frontend` and `tests` directories. Their original source is preserved on `php-source-2026-10-12`, pointing to the fully tested source commit `e68828c22a9d2842e0a11e2a1d2c6b0f70a83f15`. All production PHP files, private schema/rules/dependencies and public browser assets are unchanged byte for byte from that commit. Documentation was updated for GitHub-only deployment, explicit branch selection and correct Hostinger directory handling.

The final remote tree was checked for Node build/runtime markers, installation secrets and customer-data directories. Hostinger runtime/visual checks still require the user account; this change does not establish which branch or import workflow the failed Hostinger attempt used.

## Source build and test commands (source branch only)

Frontend, on a development computer with Node.js 24: run `npm ci`, `npm run check`, `npm run build` in `frontend`. This is an optional source-build step; the deployed website does not require Node.js.

The integration runner `tests/http.mjs` uses an isolated MariaDB distribution (`RB_TEST_RUNTIME`) and PHP executable (`RB_PHP_BIN`), creates only the fixture database `rb_php_test`, and temporarily creates/removes local `private/config.php` and `private/data`. Run it only in a disposable source copy, never against an installed production copy. Its PHP router simulates the Resend endpoint for tests and must never be configured as a production router.

Hosting: PHP 8.3+, PDO MySQL and the bundled PHP PDF dependencies. There is no hosted npm/build/start command.
