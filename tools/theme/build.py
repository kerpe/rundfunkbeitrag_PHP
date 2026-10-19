"""Rebuild the site stylesheet from the original bundle CSS plus a theme layer.

Usage: python3 tools/theme/build.py [theme]   (default: salvija)

base-entry.css is the untouched CSS from the compiled frontend bundle. The
script recolours its blue palette to the theme (hue/lightness mapping), raises
any font size below 13px, appends <theme>.css and shared.css, writes the result
to public_html/assets/entry-<hash>.css and points .vite/manifest.json at it.
"""
import colorsys, hashlib, json, os, re, sys

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(os.path.dirname(HERE))
PUBLIC = os.path.join(ROOT, 'public_html')
theme = sys.argv[1] if len(sys.argv) > 1 else 'salvija'
t = json.load(open(os.path.join(HERE, 'themes.json')))[theme]
css = open(os.path.join(HERE, 'base-entry.css')).read()


def clamp(v):
    return max(0, min(1, v))


def recolour(m):
    c = m.group(0)
    x = c[1:]
    if len(x) in (3, 4):
        x = ''.join(ch * 2 for ch in x)
    r, g, b = (int(x[i:i + 2], 16) / 255 for i in (0, 2, 4))
    alpha = x[6:8]
    h, l, s = colorsys.rgb_to_hls(r, g, b)
    if not 195 <= h * 360 <= 232 or s < 0.04:
        return c  # leave non-blue colours (status green/red/amber, greys) alone
    if s >= 0.55 and 0.3 < l < 0.75:  # brand colour
        v = t['vivid']; nl = l * v['l']
    elif s >= 0.55 and l >= 0.75:  # brand tints
        v = t['tint']; nl = 1 - (1 - l) * v['l']
    elif l < 0.3:  # ink and dark blocks
        v = t['dark']; nl = l * v['l']
    else:  # blue-grey neutrals
        v = t['neutral']; nl = l
    r, g, b = colorsys.hls_to_rgb(v['h'] / 360, clamp(nl), clamp(s * v['s']))
    return '#' + ''.join('%02x' % round(c * 255) for c in (r, g, b)) + alpha


def min_size(m):
    v = m.group(1)
    px = float(v[:-3]) * 16 if v.endswith('rem') else float(v[:-2])
    if 0 < px < 12.9:
        return 'font-size:.8125rem'
    if 12.9 <= px < 13.9:
        return 'font-size:.875rem'
    return m.group(0)


css = re.sub(r'#[0-9a-fA-F]{3,8}\b', recolour, css)
css = re.sub(r'font-size:(\d*\.?\d+(?:rem|px))', min_size, css)
css += '\n' + open(os.path.join(HERE, theme + '.css')).read() + '\n' + open(os.path.join(HERE, 'shared.css')).read()

name = 'assets/entry-%s.css' % hashlib.sha256(css.encode()).hexdigest()[:8]
manifest_path = os.path.join(PUBLIC, '.vite', 'manifest.json')
manifest = json.load(open(manifest_path))
for old in manifest['entry.tsx'].get('css', []):
    if old != name and os.path.exists(os.path.join(PUBLIC, old)):
        os.remove(os.path.join(PUBLIC, old))
open(os.path.join(PUBLIC, name), 'w').write(css)
manifest['entry.tsx']['css'] = [name]
open(manifest_path, 'w').write(json.dumps(manifest, indent=2) + '\n')
print('wrote', name)
