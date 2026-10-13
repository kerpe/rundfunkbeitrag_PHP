# Self-hosted website typography

Manrope (weight axis 200–800) is the active family across the logo wordmark,
headings, body text, customer forms and administration. It uses the bundled
SIL Open Font License 1.1 notice. Source Serif 4 is retained as an unused
previous-design asset and is no longer declared or requested by the website.

Original variable TTFs were obtained from the Google Fonts upstream repository:

- https://github.com/google/fonts/tree/main/ofl/manrope
- https://github.com/google/fonts/tree/main/ofl/sourceserif4

The WOFF2 files are format conversions of the complete upstream fonts. No
glyphs, names, outlines, or axes were changed or removed. German and English
characters and the euro symbol are included. Fonts are served from this Site;
the browser does not contact Google Fonts or another external font service.

CSS uses `font-display: swap` and system fallbacks. Variable weights distinguish
the wordmark (780/450), display headlines (720), section headings (650),
labels and actions (600), navigation (550), and body text (400). Font sizes
use responsive clamps for headlines, 16 px for form entry and normal body text,
and 14 px for supporting notices.
