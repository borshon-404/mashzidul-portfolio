#!/usr/bin/env python3
"""MTB Portfolio — static site builder. Components + pages from content.json."""
import json, os, shutil, html

ROOT = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(ROOT, '..', 'site')
C = json.load(open(os.path.join(ROOT, 'content.json')))
S = C['site']
DOMAIN = S['domain']

def e(s): return html.escape(str(s), quote=True)

# ---------------------------------------------------------------- icons
ICONS = {
 'devices':'<rect x="2" y="4" width="13" height="9" rx="1.5"/><path d="M6 17h5M8.5 13v4M17 9h3.5a1.5 1.5 0 0 1 1.5 1.5v8a1.5 1.5 0 0 1-1.5 1.5H17a1.5 1.5 0 0 1-1.5-1.5v-8A1.5 1.5 0 0 1 17 9Z"/>',
 'bolt':'<path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z"/>',
 'shield':'<path d="M12 3l7 3v6c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6l7-3Z"/>',
 'layers':'<path d="M12 3 3 8l9 5 9-5-9-5ZM3 12l9 5 9-5M3 16l9 5 9-5"/>',
 'support':'<path d="M4 13a8 8 0 0 1 16 0"/><rect x="3" y="13" width="4" height="6" rx="1.5"/><rect x="17" y="13" width="4" height="6" rx="1.5"/>',
 'mail':'<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
 'phone':'<path d="M5 3h4l2 5-2.5 1.5a12 12 0 0 0 5 5L15 12l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2Z"/>',
 'pin':'<path d="M12 21s-7-6-7-11a7 7 0 0 1 14 0c0 5-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
 'arr':'<path d="M4 12h15M13 6l6 6-6 6"/>',
 'check':'<path d="m4 12 5 5L20 7"/>',
 'plus':'<path d="M12 5v14M5 12h14"/>',
 'search':'<circle cx="11" cy="11" r="7"/><path d="m16.5 16.5 4.5 4.5"/>',
 'clock':'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
 'cash':'<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/>',
 'code':'<path d="m8 8-4 4 4 4M16 8l4 4-4 4"/>',
 'palette':'<path d="M12 3a9 9 0 1 0 0 18h1.6a2.2 2.2 0 0 0 0-4.4H12a1.7 1.7 0 0 1 0-3.4h6.2A2.8 2.8 0 0 0 21 10.6 9 9 0 0 0 12 3Z"/><circle cx="7.5" cy="10" r="1"/><circle cx="11" cy="7" r="1"/><circle cx="15.5" cy="8.5" r="1"/>',
 'globe':'<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/>',
 'cart':'<circle cx="9" cy="20" r="1.4"/><circle cx="17" cy="20" r="1.4"/><path d="M3 4h2l2.6 12h10.8L21 8H6"/>',
 'monitor':'<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M9 20h6M12 16v4"/>',
 'refresh':'<path d="M21 12a9 9 0 1 1-3-6.7M21 3v6h-6"/>',
 'wrench':'<path d="M14.7 6.3a4.6 4.6 0 0 0-6.1 6.1L3 18l3 3 5.6-5.6a4.6 4.6 0 0 0 6.1-6.1L14.5 12.5 11.5 9.5l3.2-3.2Z"/>',
 'gauge':'<path d="M4 15a8 8 0 1 1 16 0M12 15l3.5-4.5"/>',
 'bug':'<rect x="8" y="8" width="8" height="9" rx="4"/><path d="M9 8a3 3 0 0 1 6 0M4 12h4M16 12h4M5.5 6.5 8 8.5M18.5 6.5 16 8.5M5.5 17.5 8 15.5M18.5 17.5 16 15.5M12 11v6"/>',
 'send':'<path d="M22 2 11 13M22 2 15 22l-4-9-9-4 20-7Z"/>',
 'cal':'<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/>',
 'user':'<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
}
SVC_ICON = {'custom-website-design':'palette','full-stack-web-development':'code','wordpress-website-development':'globe',
 'ecommerce-website-development':'cart','landing-page-design':'monitor','website-redesign':'refresh',
 'website-maintenance':'wrench','website-speed-optimization':'gauge','seo-optimization':'search','website-bug-fixes':'bug'}

def icon(name, size=22):
    return f'<svg width="{size}" height="{size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{ICONS[name]}</svg>'

# ---------------------------------------------------------------- shared components
NAV = [('home','Home','/'),('about','About','/about/'),('services','Services','/services/'),
       ('projects','Projects','/projects/'),('pricing','Pricing','/pricing/'),('blog','Blog','/blog/'),
       ('contact','Contact','/contact/')]

def header(active):
    links = ''.join(
        f'<a href="{p}"{" aria-current=\"page\"" if k==active else ""}>{t}</a>' for k,t,p in NAV)
    mlinks = ''.join(
        f'<a href="{p}"{" aria-current=\"page\"" if k==active else ""}>{t}</a>' for k,t,p in NAV)
    return f'''<header class="header" id="top">
  <div class="container header__in">
    <a class="logo logo--desktop" href="/" aria-label="Mashzidul Tanun Borshon — home">
      <img src="/assets/img/brand/logo-combination-900.webp" width="900" height="203" alt="Mashzidul Tanun Borshon — Web Designer &amp; Full Stack Developer logo">
    </a>
    <a class="logo logo--mobile" href="/" aria-label="Mashzidul Tanun Borshon — home">
      <img src="/assets/img/brand/logo-lettermark-240.webp" width="240" height="162" alt="MTB monogram logo">
    </a>
    <nav class="nav" aria-label="Primary">{links}</nav>
    <a class="btn btn--primary btn--sm header__cta" href="/contact/">Let's Work Together</a>
    <button class="burger" aria-expanded="false" aria-controls="mobile-menu" aria-label="Open menu"><span></span><span></span><span></span></button>
  </div>
</header>
<div class="mobile-menu" id="mobile-menu">
  <nav aria-label="Mobile">{mlinks}</nav>
  <div class="mobile-menu__foot">
    <a href="/faq/">FAQ</a><a href="/booking/">Book an Appointment</a>
    <a href="mailto:{S['email']}">{S['email']}</a><a href="tel:{S['phone'].replace(' ','')}">{S['phone']}</a>
  </div>
</div>'''

def footer():
    svc = ''.join(f'<li><a href="/services/#{s["slug"]}">{e(s["title"])}</a></li>' for s in C['services'][:5])
    quick = ''.join(f'<li><a href="{p}">{t}</a></li>' for _,t,p in NAV)
    return f'''<footer class="footer">
  <div class="container">
    <div class="footer__grid">
      <div class="footer__brand">
        <img src="/assets/img/brand/logo-combination-900.webp" width="900" height="203" alt="Mashzidul Tanun Borshon — Web Designer &amp; Full Stack Developer">
        <p>{e(C['hero']['intro'])}</p>
      </div>
      <div><h4>Quick Links</h4><ul>{quick}
        <li><a href="/booking/">Book an Appointment</a></li><li><a href="/faq/">FAQ</a></li></ul></div>
      <div><h4>Services</h4><ul>{svc}
        <li><a href="/services/">All Services</a></li></ul></div>
      <div><h4>Get in Touch</h4>
        <div class="c-line">{icon('mail',18)}<a href="mailto:{S['email']}">{S['email']}</a></div>
        <div class="c-line">{icon('phone',18)}<a href="tel:{S['phone'].replace(' ','')}">{S['phone']}</a></div>
        <div class="c-line">{icon('pin',18)}<span>{e(S['address'])}</span></div>
      </div>
    </div>
    <div class="footer__bar">
      <span>© <span data-year>2026</span> {e(S['name'])}. All rights reserved.</span>
      <nav aria-label="Legal"><a href="/terms/">Terms &amp; Conditions</a><a href="/privacy/">Privacy Policy</a><a href="/faq/">FAQ</a></nav>
    </div>
  </div>
</footer>'''

def breadcrumb(items):
    parts = ['<a href="/">Home</a>']
    for label, href in items[:-1]:
        parts.append(f'<span class="sep">/</span><a href="{href}">{e(label)}</a>')
    parts.append(f'<span class="sep">/</span><span aria-current="page">{e(items[-1][0])}</span>')
    return f'<nav class="breadcrumb" aria-label="Breadcrumb">{"".join(parts)}</nav>'

def section_head(eyebrow, title, lead=None, center=False, grad_word=None):
    cls = 'section-head section-head--center' if center else 'section-head'
    t = title
    if grad_word:
        t = title.replace(grad_word, f'<span class="grad-text">{grad_word}</span>')
    l = f'<p class="lead">{e(lead)}</p>' if lead else ''
    return f'<div class="{cls}" data-reveal><span class="eyebrow">{e(eyebrow)}</span><h2 class="h-lg">{t}</h2>{l}</div>'

def cta_band():
    return f'''<section class="cta-band" aria-label="Work with me">
  <img class="wm" src="/assets/img/brand/logo-abstract-700.webp" width="700" height="760" alt="" aria-hidden="true">
  <div class="container cta-band__in" data-reveal>
    <span class="eyebrow">Get in touch</span>
    <h2 class="h-lg">Let's Work <span class="grad-text">Together</span></h2>
    <p class="lead">{e(C['contact']['intro'])}</p>
    <div class="cta-band__btns">
      <a class="btn btn--primary" href="/booking/">{icon('cal',18)} Book a Free Consultation</a>
      <a class="btn btn--ghost" href="/contact/">Contact Me <span class="arr">{icon('arr',16)}</span></a>
    </div>
  </div>
</section>'''

def project_card(p, reveal=True):
    tech = ''.join(f'<span class="chip">{e(t)}</span>' for t in p['technologies'][:3])
    short = p['overview'].split('. ')[0] + '.'
    rv = ' data-reveal' if reveal else ''
    return f'''<article class="proj-card" data-category="{e(p['category'])}"{rv}>
  <div class="proj-card__media">
    <img src="/assets/img/projects/{p['art']}" width="1200" height="900" alt="Project artwork — {e(p['title'])}" loading="lazy">
    <span class="tag">{e(p['category'])}</span>
    <span class="view" aria-hidden="true">{icon('arr',20)}</span>
  </div>
  <div class="proj-card__body">
    <h3><a href="/projects/{p['slug']}/">{e(p['title'])}</a></h3>
    <p>{e(short)}</p>
    <div class="proj-card__meta">{tech}</div>
    <div class="proj-card__foot"><span>{icon('clock',15)}&nbsp; {e(p['duration'])}</span><a class="link-gold" href="/projects/{p['slug']}/">View Project <span class="arr">{icon('arr',14)}</span></a></div>
  </div>
</article>'''

def price_card(t):
    cls = 'price-card price-card--featured' if t['featured'] else 'price-card'
    feats = ''.join(f'<li>{icon('check',16)} {e(f)}</li>' for f in t['features'])
    return f'''<article class="{cls}" data-reveal>
  <h3>{e(t['name'])}</h3><p class="tagline">{e(t['tagline'])}</p>
  <p class="price">{e(t['price'])}</p>
  <ul>{feats}</ul>
  <a class="btn {'btn--primary' if t['featured'] else 'btn--ghost'}" href="/contact/">Choose {e(t['name'])}</a>
</article>'''

BLOG_ART = ['logo-pictorial-600.webp','logo-abstract-700.webp','logo-emblem-400.webp']
def blog_card(b, i):
    art = BLOG_ART[i % 3]
    return f'''<article class="blog-card" data-title="{e(b['title'])}" data-category="{e(b['category'])}" data-reveal>
  <div class="blog-card__media"><img src="/assets/img/brand/{art}" width="300" height="300" alt="" aria-hidden="true" loading="lazy"></div>
  <div class="blog-card__body">
    <div class="blog-card__meta"><span class="tag">{e(b['category'])}</span></div>
    <h3><a href="/blog/{b['slug']}/">{e(b['title'])}</a></h3>
    <span class="blog-status">Full article coming soon</span>
    <a class="link-gold" href="/blog/{b['slug']}/">Read Article <span class="arr">{icon('arr',14)}</span></a>
  </div>
</article>'''

def faq_item(f, i):
    return f'''<div class="faq-item">
  <h3><button class="faq-item__q" aria-expanded="false" aria-controls="faq-{i}" id="faq-q-{i}">{e(f['q'])}<span class="ic">{icon('plus',16)}</span></button></h3>
  <div class="faq-item__a" id="faq-{i}" role="region" aria-labelledby="faq-q-{i}"><div><p>{e(f['a'])}</p></div></div>
</div>'''

# ---------------------------------------------------------------- page shell
def page(path, title, desc, body, active, ld=None, og_type='website'):
    url = DOMAIN + ('/' if path == '/' else path)
    ld_json = ''
    if ld:
        ld_json = '<script type="application/ld+json">' + json.dumps(ld, ensure_ascii=False) + '</script>'
    return f'''<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{e(title)}</title>
<meta name="description" content="{e(desc)}">
<link rel="canonical" href="{url}">
<meta property="og:site_name" content="{e(S['name'])}">
<meta property="og:type" content="{og_type}">
<meta property="og:title" content="{e(title)}">
<meta property="og:description" content="{e(desc)}">
<meta property="og:url" content="{url}">
<meta property="og:image" content="{DOMAIN}/assets/img/brand/og-1200x630.png">
<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">
<meta property="og:locale" content="en_US">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{e(title)}">
<meta name="twitter:description" content="{e(desc)}">
<meta name="twitter:image" content="{DOMAIN}/assets/img/brand/og-1200x630.png">
<meta name="theme-color" content="#060605">
<link rel="icon" type="image/png" sizes="64x64" href="/assets/img/brand/favicon-64.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/img/brand/favicon-32.png">
<link rel="apple-touch-icon" href="/assets/img/brand/apple-touch-180.png">
<link rel="stylesheet" href="/assets/css/fonts.css">
<link rel="stylesheet" href="/assets/css/styles.css">
{ld_json}
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
{header(active)}
<main id="main">
{body}
</main>
{footer()}
<a class="to-top" href="#top" aria-label="Back to top">{icon('arr',20).replace('M4 12h15M13 6l6 6-6 6','M12 19V5M6 11l6-6 6 6')}</a>
<script src="/assets/js/main.js" defer></script>
</body>
</html>'''

def person_ld():
    return {
      "@context":"https://schema.org","@type":"Person","name":S['name'],
      "jobTitle":"Web Designer & Full Stack Web Developer","email":S['email'],"telephone":S['phone'],
      "url":DOMAIN,"image":DOMAIN+"/assets/img/portrait/mtb-portrait-hero-720.webp",
      "address":{"@type":"PostalAddress","addressLocality":"Khulna","addressCountry":"BD"},
      "knowsAbout":["Web Design","Full Stack Web Development","WordPress","React","Node.js","SEO"]}

def bc_ld(items):
    el = [{"@type":"ListItem","position":1,"name":"Home","item":DOMAIN+"/"}]
    for i,(label,href) in enumerate(items, start=2):
        el.append({"@type":"ListItem","position":i,"name":label,"item":DOMAIN+href})
    return {"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":el}

# ---------------------------------------------------------------- HOME
def home():
    h = C['hero']
    skills = [s for g in C['skillGroups'] for s in g['skills']]
    marquee_items = ''.join(f'<span>{e(s)}</span><i>&lt;/&gt;</i>' for s in skills)
    why = ''.join(f'''<article class="why-tile"><span class="idx">0{i+1}</span><span class="ico">{icon(w['icon'],24)}</span><h3>{e(w['title'])}</h3></article>'
''' if False else f'''<article class="why-tile"><span class="idx">0{i+1}</span><span class="ico">{icon(w['icon'],24)}</span><h3>{e(w['title'])}</h3></article>''' for i,w in enumerate(C['whyChooseMe']))
    stats = ''.join(f'''<div class="stat"><span class="val" data-count="{s['value']}">0</span><span class="lbl">{e(s['label'])}</span><span class="note">{e(s['note'])}</span></div>''' for s in C['stats'])
    svc_rows = ''.join(f'''<a class="svc-row" href="/services/#{s['slug']}"><span class="no">0{i+1}</span><span class="ico">{icon(SVC_ICON[s['slug']],24)}</span><div><h3>{e(s['title'])}</h3><p>{e(s['desc'])}</p></div><span class="arr">{icon('arr',22)}</span></a>''' for i,s in enumerate(C['services']))
    info4 = C['about']['info']
    info_html = ''.join(f'<div><dt>{e(i["label"])}</dt><dd>{e(i["value"])}</dd></div>' for i in [info4[2],info4[3],info4[1],info4[6]])
    skill_cols = ''.join(f'''<div class="skill-col"><h3>{e(g['group'])}</h3><ul>{''.join(f'<li>{e(s)}</li>' for s in g['skills'])}</ul></div>''' for g in C['skillGroups'])
    proj_cards = ''.join(project_card(p) for p in C['projects'])
    price_cards = ''.join(price_card(t) for t in C['pricing'])
    blog_cards = ''.join(blog_card(b, i) for i,b in enumerate(C['blog'][:3]))

    body = f'''
<section class="hero">
  <div class="container hero__grid">
    <div data-reveal="left">
      <span class="eyebrow">{e(h['greeting'])}</span>
      <h1 class="h-xl hero__name">{e(h['name'])}</h1>
      <p class="hero__role grad-text">{e(h['role'])}</p>
      <p class="lead hero__lead">{e(h['intro'])}</p>
      <div class="hero__ctas">
        <a class="btn btn--primary" href="/contact/">{e(h['ctaPrimary'])} <span class="arr">{icon('arr',16)}</span></a>
        <a class="btn btn--ghost" href="/projects/">{e(h['ctaSecondary'])}</a>
      </div>
      <div class="hero__meta">
        <span>{icon('pin',15)}&nbsp; <b>{e(S['location'])}</b></span>
        <span>{icon('mail',15)}&nbsp; <a href="mailto:{S['email']}"><b>{S['email']}</b></a></span>
        <span>{icon('bolt',15)}&nbsp; Available for new projects</span>
      </div>
    </div>
    <div class="portrait" data-reveal="zoom">
      <span class="portrait__glow" aria-hidden="true"></span>
      <span class="portrait__ring" aria-hidden="true"></span>
      <div class="portrait__frame">
        <img src="/assets/img/portrait/mtb-portrait-hero-1114.webp" width="1114" height="1412" fetchpriority="high"
             alt="Portrait of Mashzidul Tanun Borshon, web designer and full stack web developer">
      </div>
      <div class="portrait__badge">
        <img src="/assets/img/brand/logo-pictorial-150.webp" width="34" height="28" alt="" aria-hidden="true">
        <span class="txt">Based in <b>Khulna, Bangladesh</b><br>Web Designer &amp; Full Stack Web Developer</span>
      </div>
    </div>
  </div>
</section>

<div class="marquee" aria-hidden="true">
  <div class="marquee__track">
    <div class="marquee__group">{marquee_items}</div>
    <div class="marquee__group">{marquee_items}</div>
  </div>
</div>

<section class="section" aria-labelledby="why-h">
  <div class="container">
    <div class="section-head" data-reveal><span class="eyebrow">Why choose me</span>
      <h2 class="h-lg" id="why-h">Value I bring to <span class="grad-text">every project</span></h2>
      <p class="lead">Five commitments behind every website I design, build and maintain.</p></div>
    <div class="why-grid stagger">{why}</div>
  </div>
</section>

<section class="stats" aria-label="Facts at a glance">
  <div class="container stats__grid">{stats}</div>
</section>

<section class="section" aria-labelledby="svc-h">
  <div class="container">
    <div class="section-head" data-reveal><span class="eyebrow">Services</span>
      <h2 class="h-lg" id="svc-h">What I can <span class="grad-text">do for you</span></h2>
      <p class="lead">I offer a range of web design and development services — from first launch to ongoing care.</p></div>
    <div class="svc-rows stagger">{svc_rows}</div>
    <p style="margin-top:34px" data-reveal><a class="link-gold" href="/services/">View all services <span class="arr">{icon('arr',14)}</span></a></p>
  </div>
</section>

<section class="section bg-vignette" aria-labelledby="about-h">
  <div class="container split">
    <div class="photo-frame" data-reveal="left">
      <div class="photo-frame__in"><img src="/assets/img/portrait/mtb-portrait-headshot-1114.webp" width="1114" height="809" alt="Mashzidul Tanun Borshon — head and shoulders portrait" loading="lazy"></div>
      <img class="seal" src="/assets/img/brand/logo-emblem-200.webp" width="76" height="76" alt="" aria-hidden="true">
    </div>
    <div data-reveal="right">
      <span class="eyebrow">About</span>
      <h2 class="h-lg" id="about-h" style="margin:14px 0 18px">Turning ideas into websites that <span class="grad-text">work</span></h2>
      <p class="lead">{e(C['about']['paragraphs'][0])}</p>
      <dl class="info-list">{info_html}</dl>
      <a class="btn btn--ghost" href="/about/">More About Me <span class="arr">{icon('arr',16)}</span></a>
    </div>
  </div>
</section>

<section class="section section--tight" aria-labelledby="skills-h">
  <div class="container">
    <div class="section-head section-head--center" data-reveal><span class="eyebrow">Skills</span>
      <h2 class="h-lg" id="skills-h">My technical <span class="grad-text">toolkit</span></h2></div>
    <div class="skills-grid stagger">{skill_cols}</div>
  </div>
</section>

<section class="section" aria-labelledby="proj-h">
  <div class="container">
    <div class="section-head" data-reveal><span class="eyebrow">Portfolio</span>
      <h2 class="h-lg" id="proj-h">Selected <span class="grad-text">projects</span></h2>
      <p class="lead">{e(C['portfolioIntro'])}</p></div>
    <div class="proj-grid">{proj_cards}</div>
    <p style="margin-top:38px;text-align:center" data-reveal><a class="btn btn--ghost" href="/projects/">View All Projects <span class="arr">{icon('arr',16)}</span></a></p>
  </div>
</section>

<section class="section bg-vignette" aria-labelledby="price-h">
  <div class="container">
    <div class="section-head section-head--center" data-reveal><span class="eyebrow">Pricing</span>
      <h2 class="h-lg" id="price-h">Choose your <span class="grad-text">package</span></h2>
      <p class="lead">Transparent starting points — every project is scoped with you before work begins.</p></div>
    <div class="price-grid">{price_cards}</div>
    <p class="price-note">Project work begins after payment confirmation · Revision limits depend on the selected package.</p>
  </div>
</section>

<section class="section" aria-labelledby="blog-h">
  <div class="container">
    <div class="section-head" data-reveal><span class="eyebrow">Blog</span>
      <h2 class="h-lg" id="blog-h">Latest <span class="grad-text">articles</span></h2>
      <p class="lead">Notes on web design, development, performance and growing a business online.</p></div>
    <div class="blog-grid">{blog_cards}</div>
    <p style="margin-top:34px" data-reveal><a class="link-gold" href="/blog/">All articles <span class="arr">{icon('arr',14)}</span></a></p>
  </div>
</section>

{cta_band()}'''
    ld = [person_ld(),
      {"@context":"https://schema.org","@type":"WebSite","name":S['name'],"url":DOMAIN},
      {"@context":"https://schema.org","@type":"ProfessionalService","name":S['name'],"url":DOMAIN,
       "description":C['hero']['intro'],"areaServed":"Worldwide",
       "makesOffer":[{"@type":"Offer","name":t['name'],"price":t['price'].replace('Starting From $',''),"priceCurrency":"USD"} for t in C['pricing']]}]
    return page('/', f"{S['name']} — {S['title']} | Khulna, Bangladesh", S['metaDescription'], body, 'home', ld)

# ---------------------------------------------------------------- ABOUT
def about():
    info = ''.join(f'<div><dt>{e(i["label"])}</dt><dd>{e(i["value"])}</dd></div>' for i in C['about']['info'])
    skill_cols = ''.join(f'''<div class="skill-col"><h3>{e(g['group'])}</h3><ul>{''.join(f'<li>{e(s)}</li>' for s in g['skills'])}</ul></div>''' for g in C['skillGroups'])
    body = f'''
<section class="page-hero">
  <img class="watermark" src="/assets/img/brand/logo-abstract-700.webp" width="700" height="760" alt="" aria-hidden="true">
  <div class="container">{breadcrumb([('About','/about/')])}
    <span class="eyebrow">About me</span>
    <h1 class="h-xl" style="margin-top:12px">Mashzidul Tanun <span class="grad-text">Borshon</span></h1>
    <p class="lead" style="margin-top:16px">{e(C['hero']['role'])}</p>
  </div>
</section>
<section class="section section--tight">
  <div class="container split">
    <div class="photo-frame" data-reveal="left">
      <div class="photo-frame__in"><img src="/assets/img/portrait/mtb-portrait-headshot-1114.webp" width="1114" height="809" alt="Mashzidul Tanun Borshon — portrait"></div>
      <img class="seal" src="/assets/img/brand/logo-emblem-200.webp" width="76" height="76" alt="" aria-hidden="true">
    </div>
    <div data-reveal="right">
      <span class="eyebrow">My story</span>
      <h2 class="h-md" style="margin:12px 0 16px">Websites that are visually appealing and easy to use</h2>
      {''.join(f'<p class="lead" style="margin-bottom:16px">{e(p)}</p>' for p in C['about']['paragraphs'])}
      <dl class="info-list">{info}</dl>
      <div style="display:flex;gap:14px;flex-wrap:wrap">
        <a class="btn btn--primary" href="/contact/">Work With Me</a>
        <a class="btn btn--ghost" href="/projects/">See My Work</a>
      </div>
    </div>
  </div>
</section>
<section class="section section--tight bg-vignette" aria-labelledby="askills-h">
  <div class="container">
    <div class="section-head section-head--center" data-reveal><span class="eyebrow">Skills</span>
      <h2 class="h-lg" id="askills-h">Technologies I <span class="grad-text">work with</span></h2></div>
    <div class="skills-grid stagger">{skill_cols}</div>
  </div>
</section>
<section class="section section--tight">
  <div class="container split" style="align-items:center">
    <div data-reveal="left" style="max-width:340px;justify-self:center">
      <img src="/assets/img/brand/mascot-600.webp" width="600" height="516" alt="Illustrated mascot of Mashzidul Tanun Borshon coding on a laptop" loading="lazy" style="border-radius:16px">
    </div>
    <div data-reveal="right">
      <span class="eyebrow">How I work</span>
      <h2 class="h-md" style="margin:12px 0 16px">Design, build and improve — <span class="grad-text">end to end</span></h2>
      <p class="lead">I design and develop responsive websites, WordPress websites, landing pages, eCommerce stores, and custom web applications. I also provide website maintenance, bug fixes, and performance optimization.</p>
      <p style="margin-top:22px"><a class="link-gold" href="/services/">Explore my services <span class="arr">{icon('arr',14)}</span></a></p>
    </div>
  </div>
</section>
{cta_band()}'''
    ld = [person_ld(), bc_ld([('About','/about/')])]
    return page('/about/', f"About | {S['name']} — {S['title']}",
      f"Learn about {S['name']}, a {S['title'].lower()} based in Bangladesh — skills, background and how he works.", body, 'about', ld)

# ---------------------------------------------------------------- SERVICES
def services():
    cards = ''.join(f'''<article class="svc-card" id="{s['slug']}" data-reveal>
      <div class="top"><span class="ico">{icon(SVC_ICON[s['slug']],26)}</span><span class="no">0{i+1}</span></div>
      <h3>{e(s['title'])}</h3><p>{e(s['desc'])}</p>
      <a class="link-gold" href="/contact/">Request this service <span class="arr">{icon('arr',14)}</span></a>
    </article>''' for i,s in enumerate(C['services']))
    body = f'''
<section class="page-hero">
  <img class="watermark" src="/assets/img/brand/logo-pictorial-300.webp" width="300" height="248" alt="" aria-hidden="true">
  <div class="container">{breadcrumb([('Services','/services/')])}
    <span class="eyebrow">Services</span>
    <h1 class="h-xl" style="margin-top:12px">Web design &amp; development <span class="grad-text">services</span></h1>
    <p class="lead" style="margin-top:16px">I offer a range of web design and development services — everything your website needs from first launch to ongoing growth.</p>
  </div>
</section>
<section class="section section--tight">
  <div class="container"><div class="svc-grid">{cards}</div></div>
</section>
{cta_band()}'''
    ld = [person_ld(), bc_ld([('Services','/services/')]),
      {"@context":"https://schema.org","@type":"ItemList","itemListElement":[
        {"@type":"ListItem","position":i+1,"name":s['title'],"url":f"{DOMAIN}/services/#{s['slug']}"} for i,s in enumerate(C['services'])]}]
    return page('/services/', f"Services | {S['name']} — Web Design & Development",
      "Custom website design, full stack development, WordPress, eCommerce, landing pages, redesign, maintenance, speed and SEO — services by Mashzidul Tanun Borshon.", body, 'services', ld)

# ---------------------------------------------------------------- PROJECTS
def projects():
    cats = ['All'] + [p['category'] for p in C['projects']]
    filters = ''.join(f'<button class="filter-btn" data-filter="{ "all" if c=="All" else e(c) }" aria-pressed="{"true" if c=="All" else "false"}">{e(c)}</button>' for c in cats)
    cards = ''.join(project_card(p) for p in C['projects'])
    body = f'''
<section class="page-hero">
  <img class="watermark" src="/assets/img/brand/logo-abstract-700.webp" width="700" height="760" alt="" aria-hidden="true">
  <div class="container">{breadcrumb([('Projects','/projects/')])}
    <span class="eyebrow">Portfolio</span>
    <h1 class="h-xl" style="margin-top:12px">My <span class="grad-text">work</span></h1>
    <p class="lead" style="margin-top:16px">{e(C['portfolioIntro'])}</p>
  </div>
</section>
<section class="section section--tight">
  <div class="container">
    <div class="filters" role="group" aria-label="Filter projects by category">{filters}</div>
    <div class="proj-grid">{cards}</div>
  </div>
</section>
{cta_band()}'''
    ld = [person_ld(), bc_ld([('Projects','/projects/')]),
      {"@context":"https://schema.org","@type":"ItemList","itemListElement":[
        {"@type":"ListItem","position":i+1,"name":p['title'],"url":f"{DOMAIN}/projects/{p['slug']}/"} for i,p in enumerate(C['projects'])]}]
    return page('/projects/', f"Projects | {S['name']} — Portfolio",
      "Selected projects by Mashzidul Tanun Borshon: business websites, corporate sites, portfolios and agency websites with case studies.", body, 'projects', ld)

def project_detail(p, idx):
    nxt = C['projects'][(idx+1) % len(C['projects'])]
    prv = C['projects'][(idx-1) % len(C['projects'])]
    tech = ''.join(f'<span class="chip">{e(t)}</span>' for t in p['technologies'])
    feats = ''.join(f'<li>{icon('check',16)} {e(f)}</li>' for f in p['features'])
    live = ''
    if p.get('liveUrl'):
        live = f'<div class="row"><dt>Live URL</dt><dd><a href="{e(p["liveUrl"])}" rel="noopener">{e(p["liveUrl"])}</a></dd></div>'
    body = f'''
<section class="page-hero">
  <div class="container">{breadcrumb([('Projects','/projects/'),(p['title'],f'/projects/{p["slug"]}/')])}
    <span class="tag">{e(p['category'])}</span>
    <h1 class="h-lg" style="margin-top:14px">{e(p['title'])}</h1>
  </div>
</section>
<section class="section section--tight">
  <div class="container">
    <div class="pd-hero">
      <div class="pd-media" data-reveal="left"><img src="/assets/img/projects/{p['art']}" width="1200" height="900" alt="Project artwork — {e(p['title'])}"></div>
      <aside class="pd-meta" data-reveal="right" aria-label="Project details">
        <h2>Project Details</h2>
        <dl>
          <div class="row"><dt>Category</dt><dd>{e(p['category'])}</dd></div>
          <div class="row"><dt>Duration</dt><dd>{e(p['duration'])}</dd></div>
          <div class="row"><dt>Project Cost</dt><dd>{e(p['cost'])}</dd></div>
          <div class="row"><dt>My Role</dt><dd>{e(p['role'])}</dd></div>
          {live}
        </dl>
      </aside>
    </div>
    <div class="pd-section" data-reveal>
      <span class="eyebrow">Overview</span>
      <p class="lead" style="margin-top:14px">{e(p['overview'])}</p>
    </div>
    <div class="pd-section" data-reveal>
      <span class="eyebrow">Technologies</span>
      <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:16px">{tech}</div>
    </div>
    <div class="pd-section" data-reveal>
      <span class="eyebrow">Features</span>
      <ul class="feature-grid" style="margin-top:16px">{feats}</ul>
    </div>
    <nav class="pd-nav" aria-label="More projects">
      <a href="/projects/{prv['slug']}/"><span class="dir">← Previous</span>{e(prv['title'])}</a>
      <a class="next" href="/projects/{nxt['slug']}/"><span class="dir">Next →</span>{e(nxt['title'])}</a>
    </nav>
  </div>
</section>
{cta_band()}'''
    ld = [bc_ld([('Projects','/projects/'),(p['title'],f'/projects/{p["slug"]}/')]),
      {"@context":"https://schema.org","@type":"CreativeWork","name":p['title'],"description":p['overview'],
       "creator":{"@type":"Person","name":S['name']},"keywords":", ".join(p['technologies'])}]
    return page(f'/projects/{p["slug"]}/', f"{p['title']} | Portfolio | {S['name']}",
      p['overview'][:155], body, 'projects', ld, 'article')

# ---------------------------------------------------------------- PRICING
def pricing():
    cards = ''.join(price_card(t) for t in C['pricing'])
    body = f'''
<section class="page-hero">
  <img class="watermark" src="/assets/img/brand/logo-emblem-400.webp" width="400" height="398" alt="" aria-hidden="true">
  <div class="container">{breadcrumb([('Pricing','/pricing/')])}
    <span class="eyebrow">Pricing</span>
    <h1 class="h-xl" style="margin-top:12px">Choose your <span class="grad-text">package</span></h1>
    <p class="lead" style="margin-top:16px">Three clear starting points — from a single-page presence to a complete business solution.</p>
  </div>
</section>
<section class="section section--tight">
  <div class="container">
    <div class="price-grid">{cards}</div>
    <p class="price-note">All packages include responsive design and post-launch support. Project work begins after payment confirmation · revision limits depend on the selected package.</p>
    <p style="text-align:center;margin-top:26px" data-reveal><a class="link-gold" href="/booking/">Not sure which fits? Book a free consultation <span class="arr">{icon('arr',14)}</span></a></p>
  </div>
</section>
<section class="section section--tight bg-vignette" aria-labelledby="pfaq-h">
  <div class="container">
    <div class="section-head section-head--center" data-reveal><span class="eyebrow">Questions</span>
      <h2 class="h-md" id="pfaq-h">Before you <span class="grad-text">choose</span></h2></div>
    <div class="faq-list">{faq_item(C['faq'][1],20)}{faq_item(C['faq'][5],21)}</div>
    <p style="text-align:center;margin-top:26px"><a class="link-gold" href="/faq/">All frequently asked questions <span class="arr">{icon('arr',14)}</span></a></p>
  </div>
</section>
{cta_band()}'''
    ld = [bc_ld([('Pricing','/pricing/')]),
      {"@context":"https://schema.org","@type":"OfferCatalog","name":"Website packages",
       "itemListElement":[{"@type":"Offer","name":t['name'],"description":t['tagline'],
        "price":t['price'].replace('Starting From $',''),"priceCurrency":"USD"} for t in C['pricing']]}]
    return page('/pricing/', f"Pricing | {S['name']} — Website Packages",
      "Transparent website pricing from Mashzidul Tanun Borshon: Basic, Standard and Premium packages with delivery times and support.", body, 'pricing', ld)

# ---------------------------------------------------------------- BLOG
def blog():
    cards = ''.join(blog_card(b, i) for i,b in enumerate(C['blog']))
    body = f'''
<section class="page-hero">
  <img class="watermark" src="/assets/img/brand/logo-pictorial-300.webp" width="300" height="248" alt="" aria-hidden="true">
  <div class="container">{breadcrumb([('Blog','/blog/')])}
    <span class="eyebrow">Blog</span>
    <h1 class="h-xl" style="margin-top:12px">Articles &amp; <span class="grad-text">insights</span></h1>
    <p class="lead" style="margin-top:16px">Practical writing on web design, development, performance and growing a business online.</p>
  </div>
</section>
<section class="section section--tight">
  <div class="container">
    <div class="field" style="max-width:420px;margin-bottom:34px">
      <label for="blog-search">Search articles</label>
      <input class="blog-search" id="blog-search" type="search" placeholder="Search by title or topic…">
    </div>
    <div class="blog-grid">{cards}</div>
    <p class="blog-empty muted" style="display:none;margin-top:30px">No articles match your search.</p>
    <p class="form-note" style="margin-top:30px">Full articles are being written and will be published here soon.</p>
  </div>
</section>
{cta_band()}'''
    ld = [bc_ld([('Blog','/blog/')]),
      {"@context":"https://schema.org","@type":"Blog","name":f"{S['name']} — Blog","url":DOMAIN+"/blog/","author":person_ld()}]
    return page('/blog/', f"Blog | {S['name']} — Web Design & Development Articles",
      "Articles on web design, development, SEO, performance, security and UX by Mashzidul Tanun Borshon.", body, 'blog', ld)

def blog_post(b, idx):
    nxt = C['blog'][(idx+1) % len(C['blog'])]
    prv = C['blog'][(idx-1) % len(C['blog'])]
    rel = [x for x in C['blog'] if x['slug'] != b['slug']][:3]
    rel_cards = ''.join(blog_card(r, i) for i,r in enumerate(rel))
    body = f'''
<section class="page-hero">
  <div class="container">
    <div class="post-head">
      {breadcrumb([('Blog','/blog/'),(b['title'],f'/blog/{b["slug"]}/')])}
      <span class="tag">{e(b['category'])}</span>
      <h1>{e(b['title'])}</h1>
      <div class="meta"><span>By {e(S['name'])}</span><span>·</span><span class="blog-status">Article in progress</span></div>
    </div>
  </div>
</section>
<section class="section section--tight">
  <div class="container">
    <div class="post-body">
      <div class="notice" data-reveal>
        <p><b>This article is currently being written.</b> The full content for “{e(b['title'])}” will be published here.
        <span class="placeholder-tag" style="margin-top:12px">[CONTENT TO BE ADDED]</span></p>
      </div>
    </div>
    <nav class="pn-nav" aria-label="More articles">
      <a href="/blog/{prv['slug']}/"><span class="dir">← Previous</span>{e(prv['title'])}</a>
      <a class="next" href="/blog/{nxt['slug']}/"><span class="dir">Next →</span>{e(nxt['title'])}</a>
    </nav>
  </div>
</section>
<section class="section section--tight bg-vignette" aria-labelledby="rel-h">
  <div class="container">
    <div class="section-head" data-reveal><span class="eyebrow">Keep reading</span><h2 class="h-md" id="rel-h">Related articles</h2></div>
    <div class="blog-grid">{rel_cards}</div>
  </div>
</section>
{cta_band()}'''
    ld = [bc_ld([('Blog','/blog/'),(b['title'],f'/blog/{b["slug"]}/')]),
      {"@context":"https://schema.org","@type":"BlogPosting","headline":b['title'],
       "author":{"@type":"Person","name":S['name']},"url":f"{DOMAIN}/blog/{b['slug']}/","articleSection":b['category']}]
    return page(f'/blog/{b["slug"]}/', f"{b['title']} | Blog | {S['name']}",
      f"{b['title']} — an article by {S['name']}, {S['title'].lower()}.", body, 'blog', ld, 'article')

# ---------------------------------------------------------------- CONTACT
def contact():
    socials = [s for s in S['socials'] if s['label']]
    soc_html = ''.join(f'<b>{e(s["platform"])}</b>: {e(s["label"])}' for s in socials)
    body = f'''
<section class="page-hero">
  <img class="watermark" src="/assets/img/brand/logo-abstract-700.webp" width="700" height="760" alt="" aria-hidden="true">
  <div class="container">{breadcrumb([('Contact','/contact/')])}
    <span class="eyebrow">Contact</span>
    <h1 class="h-xl" style="margin-top:12px">Let's work <span class="grad-text">together</span></h1>
    <p class="lead" style="margin-top:16px">{e(C['contact']['intro'])}</p>
  </div>
</section>
<section class="section section--tight">
  <div class="container contact-grid">
    <div class="c-info" data-reveal="left">
      <div class="c-row"><span class="ico">{icon('user',20)}</span><div><span class="lbl">Name</span><div class="val">{e(S['name'])}</div><div class="val muted" style="font-size:13.5px">{e(C['contact']['profession'])}</div></div></div>
      <div class="c-row"><span class="ico">{icon('mail',20)}</span><div><span class="lbl">E-mail</span><a class="val" href="mailto:{S['email']}">{S['email']}</a></div></div>
      <div class="c-row"><span class="ico">{icon('phone',20)}</span><div><span class="lbl">Phone / WhatsApp</span><a class="val" href="tel:{S['phone'].replace(' ','')}">{S['phone']}</a></div></div>
      <div class="c-row"><span class="ico">{icon('pin',20)}</span><div><span class="lbl">Location</span><div class="val">{e(S['address'])}</div></div></div>
      <div class="social-note">Profiles — {soc_html}. <span class="placeholder-tag">profile URLs [CONTENT TO BE ADDED]</span></div>
    </div>
    <div data-reveal="right">
      <form data-form="contact" novalidate>
        <div class="form-grid">
          <div class="field"><label for="c-name">Name</label><input id="c-name" name="name" type="text" autocomplete="name" required><span class="err-msg">Please enter your name.</span></div>
          <div class="field"><label for="c-email">Email</label><input id="c-email" name="email" type="email" autocomplete="email" required><span class="err-msg">Please enter a valid email address.</span></div>
          <div class="field field--full"><label for="c-subject">Subject</label><input id="c-subject" name="subject" type="text" required><span class="err-msg">Please add a subject.</span></div>
          <div class="field field--full"><label for="c-msg">Message</label><textarea id="c-msg" name="message" required placeholder="Tell me about your project…"></textarea><span class="err-msg">Please write a short message.</span></div>
        </div>
        <p class="form-status" role="status"></p>
        <p style="margin-top:20px"><button class="btn btn--primary" type="submit">{icon('send',18)} Send Message</button></p>
        <p class="form-note">Prefer email? Write directly to <a href="mailto:{S['email']}" style="color:var(--gold-300)">{S['email']}</a>. Your details are handled per the <a href="/privacy/" style="color:var(--gold-300)">Privacy Policy</a>.</p>
      </form>
    </div>
  </div>
</section>
{cta_band()}'''
    ld = [bc_ld([('Contact','/contact/')]), person_ld()]
    return page('/contact/', f"Contact | {S['name']} — Let's Work Together",
      f"Contact {S['name']} ({S['title']}) by email, phone or the contact form to discuss your website project.", body, 'contact', ld)

# ---------------------------------------------------------------- FAQ
def faq():
    items = ''.join(faq_item(f, i) for i,f in enumerate(C['faq']))
    body = f'''
<section class="page-hero">
  <div class="container">{breadcrumb([('FAQ','/faq/')])}
    <span class="eyebrow">FAQ</span>
    <h1 class="h-xl" style="margin-top:12px">Frequently asked <span class="grad-text">questions</span></h1>
    <p class="lead" style="margin-top:16px">Straight answers about how I work, timelines and support.</p>
  </div>
</section>
<section class="section section--tight">
  <div class="container"><div class="faq-list" data-reveal>{items}</div></div>
</section>
{cta_band()}'''
    ld = [bc_ld([('FAQ','/faq/')]),
      {"@context":"https://schema.org","@type":"FAQPage","mainEntity":[
        {"@type":"Question","name":f['q'],"acceptedAnswer":{"@type":"Answer","text":f['a']}} for f in C['faq']]}]
    return page('/faq/', f"FAQ | {S['name']} — Web Design & Development Questions",
      "Answers to common questions about services, timelines, mobile-friendliness, redesigns, support and getting started.", body, '', ld)

# ---------------------------------------------------------------- LEGAL
def terms():
    lis = ''.join(f'<li>{e(t)}</li>' for t in C['terms'])
    body = f'''
<section class="page-hero"><div class="container">{breadcrumb([('Terms & Conditions','/terms/')])}
  <span class="eyebrow">Legal</span><h1 class="h-lg" style="margin-top:12px">Terms and <span class="grad-text">Conditions</span></h1>
  <p class="lead" style="margin-top:14px">By using this website, you agree to the following terms:</p></div></section>
<section class="section section--tight"><div class="container prose"><ul>{lis}</ul>
  <p style="margin-top:26px">Questions about these terms? <a href="/contact/" style="color:var(--gold-300)">Contact me</a> any time.</p></div></section>'''
    return page('/terms/', f"Terms & Conditions | {S['name']}",
      "Terms and conditions for using the website and services of Mashzidul Tanun Borshon.", body, '', bc_ld([('Terms & Conditions','/terms/')]))

def privacy():
    P = C['privacy']
    body = f'''
<section class="page-hero"><div class="container">{breadcrumb([('Privacy Policy','/privacy/')])}
  <span class="eyebrow">Legal</span><h1 class="h-lg" style="margin-top:12px">Privacy <span class="grad-text">Policy</span></h1>
  <p class="lead" style="margin-top:14px">{e(P['intro'])}</p></div></section>
<section class="section section--tight"><div class="container prose">
  <h2>Information collected through this website may include</h2>
  <ul>{''.join(f'<li>{e(x)}</li>' for x in P['collected'])}</ul>
  <h2>This information is used solely to</h2>
  <ul>{''.join(f'<li>{e(x)}</li>' for x in P['usedFor'])}</ul>
  <p style="margin-top:20px">{e(P['closing'])}</p>
  <p style="margin-top:14px">{e(P['consent'])}</p>
</div></section>'''
    return page('/privacy/', f"Privacy Policy | {S['name']}",
      "How personal information collected through this website is used and protected.", body, '', bc_ld([('Privacy Policy','/privacy/')]))

# ---------------------------------------------------------------- BOOKING
def booking():
    agenda = ''.join(f'<li>{icon('check',16)} {e(a)}</li>' for a in C['booking']['agenda'])
    checks = ''.join(f'<label style="display:flex;gap:10px;align-items:center;font-size:14.5px;color:var(--ink-2);text-transform:none;letter-spacing:0;font-family:var(--font-body)"><input type="checkbox" name="agenda" value="{e(a)}" style="width:auto;accent-color:#ECBD61"> {e(a)}</label>' for a in C['booking']['agenda'])
    body = f'''
<section class="page-hero">
  <img class="watermark" src="/assets/img/brand/logo-emblem-400.webp" width="400" height="398" alt="" aria-hidden="true">
  <div class="container">{breadcrumb([('Book an Appointment','/booking/')])}
    <span class="eyebrow">Book an appointment</span>
    <h1 class="h-xl" style="margin-top:12px">Schedule a <span class="grad-text">consultation</span></h1>
    <p class="lead" style="margin-top:16px">{e(C['booking']['intro'])}</p>
  </div>
</section>
<section class="section section--tight">
  <div class="container split">
    <div data-reveal="left">
      <span class="eyebrow">During our meeting, we'll cover</span>
      <ul class="feature-grid" style="grid-template-columns:1fr 1fr;margin-top:20px">{agenda}</ul>
      <div class="c-row" style="margin-top:26px"><span class="ico">{icon('clock',20)}</span><div><span class="lbl">Format</span><div class="val">Free consultation — online call or chat, at a time that suits you.</div></div></div>
    </div>
    <div data-reveal="right">
      <form data-form="booking" novalidate>
        <div class="form-grid">
          <div class="field"><label for="b-name">Name</label><input id="b-name" name="name" type="text" autocomplete="name" required><span class="err-msg">Please enter your name.</span></div>
          <div class="field"><label for="b-email">Email</label><input id="b-email" name="email" type="email" autocomplete="email" required><span class="err-msg">Please enter a valid email address.</span></div>
          <div class="field"><label for="b-date">Preferred date</label><input id="b-date" name="date" type="date"></div>
          <div class="field"><label for="b-budget">Budget range</label>
            <select id="b-budget" name="budget">
              <option value="">Select…</option><option>Under $100</option><option>$100 – $250</option><option>$250+</option><option>Not sure yet</option>
            </select></div>
          <div class="field field--full"><span id="agenda-lbl" style="font-family:var(--font-display);font-size:12.5px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--ink-muted)">Agenda (optional)</span>
            <div role="group" aria-labelledby="agenda-lbl" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;background:var(--surface-2);border:1px solid var(--line);border-radius:6px;padding:14px 16px;margin-top:8px">{checks}</div>
          </div>
          <div class="field field--full"><label for="b-msg">Anything else?</label><textarea id="b-msg" name="message" style="min-height:100px" placeholder="Optional notes…"></textarea></div>
        </div>
        <p class="form-status" role="status"></p>
        <p style="margin-top:20px"><button class="btn btn--primary" type="submit">{icon('cal',18)} {e(C['booking']['cta'])}</button></p>
        <p class="form-note">A calendar integration (Google Calendar / Calendly) can be connected here later without design changes.</p>
      </form>
    </div>
  </div>
</section>
{cta_band()}'''
    ld = [bc_ld([('Book an Appointment','/booking/')]),
      {"@context":"https://schema.org","@type":"Appointment","description":C['booking']['intro'],"provider":person_ld()}]
    return page('/booking/', f"Book an Appointment | {S['name']}",
      "Book a free consultation with Mashzidul Tanun Borshon to discuss project requirements, features, design, timeline and budget.", body, '', ld)

# ---------------------------------------------------------------- 404
def notfound():
    body = f'''
<section class="section" style="padding:calc(var(--header-h) + 90px) 0 110px;text-align:center">
  <div class="container" style="display:flex;flex-direction:column;align-items:center;gap:20px">
    <img src="/assets/img/brand/logo-abstract-700.webp" width="700" height="760" alt="" aria-hidden="true" style="width:180px;opacity:.5">
    <h1 class="h-xl">4<span class="grad-text">0</span>4</h1>
    <p class="lead">This page doesn't exist — but your project can. Let's get you back on track.</p>
    <div style="display:flex;gap:14px;flex-wrap:wrap;justify-content:center">
      <a class="btn btn--primary" href="/">Back to Home</a>
      <a class="btn btn--ghost" href="/projects/">View My Work</a>
    </div>
  </div>
</section>'''
    return page('/404/', f"Page Not Found | {S['name']}", "The page you were looking for could not be found.", body, '')

# ---------------------------------------------------------------- build
PAGES = []
def emit(path, htmlstr):
    rel = path.lstrip('/')
    fp = os.path.join(OUT, rel, 'index.html') if not rel.endswith('.html') else os.path.join(OUT, rel)
    os.makedirs(os.path.dirname(fp), exist_ok=True)
    open(fp, 'w', encoding='utf-8').write(htmlstr)
    PAGES.append(path if path.endswith('/') else path)

emit('/', home())
emit('/about/', about())
emit('/services/', services())
emit('/projects/', projects())
for i,p in enumerate(C['projects']): emit(f'/projects/{p["slug"]}/', project_detail(p, i))
emit('/pricing/', pricing())
emit('/blog/', blog())
for i,b in enumerate(C['blog']): emit(f'/blog/{b["slug"]}/', blog_post(b, i))
emit('/contact/', contact())
emit('/faq/', faq())
emit('/terms/', terms())
emit('/privacy/', privacy())
emit('/booking/', booking())
emit('/404.html', notfound())

# assets: css + js
os.makedirs(os.path.join(OUT, 'assets/css'), exist_ok=True)
os.makedirs(os.path.join(OUT, 'assets/js'), exist_ok=True)
shutil.copy(os.path.join(ROOT, 'assets/css/fonts.css'), os.path.join(OUT, 'assets/css/fonts.css'))
shutil.copy(os.path.join(ROOT, 'assets/css/styles.css'), os.path.join(OUT, 'assets/css/styles.css'))
shutil.copy(os.path.join(ROOT, 'assets/js/main.js'), os.path.join(OUT, 'assets/js/main.js'))

# sitemap + robots
urls = [p for p in PAGES if p != '/404.html']
with open(os.path.join(OUT, 'sitemap.xml'), 'w') as f:
    f.write('<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')
    for u in urls:
        loc = DOMAIN + (u if u.endswith('/') else u)
        f.write(f'  <url><loc>{loc}</loc><changefreq>monthly</changefreq></url>\n')
    f.write('</urlset>\n')
with open(os.path.join(OUT, 'robots.txt'), 'w') as f:
    f.write(f'User-agent: *\nAllow: /\nSitemap: {DOMAIN}/sitemap.xml\n')

print(f'Built {len(PAGES)} pages.')
