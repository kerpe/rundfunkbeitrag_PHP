# Self-hosted website typography

Instrument Sans (weight axis 400–700) is the active family for the logo
wordmark, headings, body text, customer forms and the admin login. Fraunces
(weight 300–800, italic look via the theme) is used only for the emphasised
phrase in the homepage headline. Both use the bundled SIL Open Font License 1.1
notices (`InstrumentSans-OFL.txt`, `Fraunces-OFL.txt`).

Original variable TTFs were obtained from the Google Fonts upstream repository
and converted to WOFF2 without changing glyphs, names, outlines or axes:

- https://github.com/google/fonts/tree/main/ofl/instrumentsans
- https://github.com/google/fonts/tree/main/ofl/fraunces

German and English characters and the euro symbol are included. Fonts are
served from this site; the browser does not contact Google Fonts or another
external font service.

CSS uses `font-display: swap` and system fallbacks. Text is never smaller than
13 px; supporting text is 14 px, body text 16–17 px. Colours and sizes are set
by `tools/theme/` (see `tools/theme/build.py`).
