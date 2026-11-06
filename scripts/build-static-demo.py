#!/usr/bin/env python3
"""Builds demo/ : a STATIC, read-only snapshot of the LOCAL fixture site for design review on Vercel.
Not the product: cart, search, filters, checkout are inert. Usage: build-static-demo.py http://localhost:8088 demo"""
import re, sys, os, shutil, urllib.request, urllib.parse, json
from PIL import Image
base, out = sys.argv[1].rstrip('/'), sys.argv[2]
shutil.rmtree(out, ignore_errors=True); os.makedirs(out)
get = lambda u: urllib.request.urlopen(urllib.request.Request(u)).read()
def fetch(path):
    try: return get(base + path)
    except urllib.error.HTTPError as e: return e.read()
pages = ['/', '/shop/', '/cart/', '/checkout/', '/my-account/', '/about/', '/contact/', '/shipping-policy/', '/refund-return-policy/', '/terms-conditions/', '/privacy-policy/']
for t in json.loads(get(base + '/wp-json/wc/store/v1/products/categories?per_page=100')): pages.append(urllib.parse.urlparse(t['permalink']).path)
for pr in json.loads(get(base + '/wp-json/wc/store/v1/products?per_page=100')): pages.append(urllib.parse.urlparse(pr['permalink']).path)
assets, seen = set(), set()
def clean(html):
    html = re.sub(r'<link[^>]+(wp-json|EditURI|xmlrpc|shortlink|rel=["\']alternate|rel=["\']canonical|oembed)[^>]*>\s*', '', html)
    html = html.replace('http://localhost:8088', '').replace('http:\\/\\/localhost:8088', '').replace('localhost:8088', '')
    html = re.sub(r'<meta name="robots"[^>]*>', '', html)
    html = re.sub(r'<script[^>]+(cart-fragments|add-to-cart)[^>]*></script>\s*', '', html)  # no live cart in a static snapshot
    html = html.replace('</head>', '<meta name="robots" content="noindex,nofollow"><script defer src="/demo-shim.js"></script></head>', 1)
    return html
def refs(text):
    for m in re.finditer(r'''(?:src|href|data-[a-z_-]+|content)=["']([^"']+)["']|url\(["']?([^)"']+)["']?\)|(\S+\.(?:jpe?g|png|webp|svg|woff2?))\s+\d+w''', text):
        for g in m.groups():
            if g and g.startswith(('/wp-content', '/wp-includes')) or (g and g.startswith(base)):
                yield g.replace(base, '').split('?')[0].split('#')[0]
for p in pages:
    html = fetch(p).decode('utf-8', 'replace')
    for r in re.findall(r'srcset=["\']([^"\']+)["\']', html):
        for part in r.split(','): 
            u = part.strip().split(' ')[0]; 
            if u: assets.add(u.replace(base, '').split('?')[0])
    for u in refs(html): assets.add(u)
    dest = os.path.join(out, p.strip('/'), 'index.html'); os.makedirs(os.path.dirname(dest), exist_ok=True)
    open(dest, 'w').write(clean(html))
open(os.path.join(out, '404.html'), 'w').write(clean(fetch('/definitely-missing/').decode()))
# assets incl. url() / css-imports
queue = [a for a in assets if a.startswith(('/wp-content', '/wp-includes')) and os.path.splitext(a)[1]]
while queue:
    a = queue.pop()
    if a in seen: continue
    seen.add(a)
    for q in ([a] if True else []):
        try: data = get(base + urllib.parse.quote(a))
        except Exception: continue
        dest = os.path.join(out, a.lstrip('/')); os.makedirs(os.path.dirname(dest), exist_ok=True); open(dest, 'wb').write(data)
        if a.endswith('.css'):
            css = data.decode('utf-8', 'replace')
            for u in re.findall(r'url\(["\']?([^)"\']+)["\']?\)', css):
                if u.startswith('data:'): continue
                full = urllib.parse.urljoin(a, u.split('?')[0].split('#')[0])
                if os.path.splitext(full)[1] and full.startswith(('/wp-content', '/wp-includes')): queue.append(full)
# shrink merchant images (local review copy): max 900px, quality 72
for root, _, fs in os.walk(os.path.join(out, 'wp-content/uploads')):
    for f in fs:
        p = os.path.join(root, f)
        try:
            im = Image.open(p); im.thumbnail((900, 900))
            if f.lower().endswith('.png'): im = im.convert('RGBA').quantize(256, method=Image.Quantize.FASTOCTREE); im.save(p, optimize=True)
            else: im.save(p, quality=72, optimize=True)
        except Exception: pass
shutil.copy(os.path.join(os.path.dirname(__file__), 'demo-shim.js'), os.path.join(out, 'demo-shim.js'))
json.dump({"cleanUrls": False, "trailingSlash": True, "headers": [{"source": "/(.*)", "headers": [{"key": "X-Robots-Tag", "value": "noindex, nofollow"}]}]}, open(os.path.join(out, 'vercel.json'), 'w'))
print(len(pages), 'pages', len(seen), 'assets')
