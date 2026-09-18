# Mashzidul Tanun Borshon — Personal Portfolio Website

Premium black + gold personal portfolio for **Mashzidul Tanun Borshon — Web Designer & Full Stack Web Developer**.
Static multi-page site: 24 pages, dependency-free vanilla CSS/JS, Python build step, self-hosted fonts.

Live identity: `https://mashzidultanun.com` · email `mail@mashzidultanun.com` · phone/WhatsApp `+8801330132141`

---

## Quick start

Requirements: **Node ≥ 16** (serving only) and **Python 3** (build step, stdlib only).

```bash
npm install        # zero runtime dependencies — creates the lockfile state
npm run build      # regenerates ./site from ./site_src (content.json + templates)
npm start          # serves ./site at http://localhost:8080 (clean URLs + 404 fallback)
```

Other scripts:

| Script | Purpose |
|---|---|
| `npm run dev` | build + start |
| `npm run audit` | QC audit of the built site (links, assets, alt text, H1, tag balance) |
| `npm run check` | build + audit + JS syntax checks (full pre-deploy verification) |

The site is fully static — `site/` can also be dropped onto any static host
(cPanel, Netlify, Vercel, GitHub Pages, Cloudflare Pages) with no build at all.

## Project structure

The **repository root IS the web root** (built pages live next to the source folders),
so GitHub Pages "Deploy from a branch → / (root)" works with zero extra config.
All internal URLs are **page-relative**, so the same build runs at a domain root
(`https://mashzidultanun.com/`), a project sub-path (`https://user.github.io/mashzidul-portfolio/`)
or any local folder — no rebuild needed when the base path changes.

```
mashzidul-portfolio/
├── index.html             # BUILT WEBSITE (web root) — Home
├── about/ services/ projects/ (+4 case studies) pricing/
├── blog/ (+8 article pages) contact/ faq/ terms/ privacy/ booking/
├── 404.html  sitemap.xml  robots.txt
├── assets/
│   ├── css/               # fonts.css (self-hosted @font-face) + styles.css (design system)
│   ├── js/                # main.js — nav, reveal, counters, accordion, filters, forms
│   ├── fonts/             # Sora 600/700 + Rubik 400/500 (woff2, latin, self-hosted)
│   └── img/
│       ├── brand/         # official logo derivatives (webp), favicon, apple-touch, OG image
│       ├── portrait/      # official photo derivatives (webp + png masters)
│       └── projects/      # branded project artwork (svg)
├── site_src/              # SOURCE
│   ├── build.py           # static site generator (components + pages)
│   ├── content.json       # SINGLE SOURCE OF TRUTH for every string on the site
│   └── assets/css|js      # source css/js copied into site/assets by the build
├── source-assets/         # uploaded original masters (logos, photos, content PDF)
├── docs/                  # implementation plan + portrait composition proof
├── tools/audit.py         # QC audit script
├── server.js              # zero-dependency static server (clean URLs, 404 fallback)
└── package.json
```

## Editing content

All copy (personal info, skills, services, projects, pricing, blog titles, FAQs,
terms, privacy, booking agenda) lives in **`site_src/content.json`**.
Edit it, then run `npm run build`. Never hand-edit pages in `site/`.

Brand assets: use the files in `source-assets/` as masters. The build expects
optimized derivatives in `site/assets/img/brand|portrait` (regenerate with PIL/WebP
at the same names if masters change). **Do not redesign, recolor or recreate the logos.**

## Features

- 24 SEO-ready pages: unique titles/descriptions, canonicals, Open Graph + Twitter cards,
  JSON-LD (Person, WebSite, ProfessionalService + Offers, FAQPage, BreadcrumbList,
  ItemList, BlogPosting, CreativeWork), `sitemap.xml`, `robots.txt`
- Responsive from 320 px to 1920 px; dedicated mobile nav (animated hamburger → full-screen menu)
- Accessible: skip link, semantic landmarks, single H1 per page, ARIA accordion/menus/filters,
  visible focus states, `prefers-reduced-motion` support
- Motion: scroll reveals, staggered grids, animated counters, skills marquee, hover lifts —
  subtle, no gimmicks
- Forms: client validation + functional mailto handoff; `FORM_ENDPOINT` constant in
  `site/assets/js/main.js` is the single plug-in point for a real backend (Formspree, cPanel, API)
- Booking page prepared for a calendar integration (Google Calendar / Calendly) without design changes

## Deploy — GitHub Pages

1. Repository → **Settings → Pages**.
2. *Build and deployment → Source:* **Deploy from a branch**.
3. *Branch:* `main` · *Folder:* **/ (root)** → Save.
4. Wait 1–2 min → live at `https://<username>.github.io/<repo>/`.

No build step runs on GitHub — the committed root already contains the built site.
Custom domain later? Point the domain at the same root; relative URLs keep working.

## Content integrity rules (binding)

The PDF in `source-assets/` is the source of truth. No invented clients, projects,
testimonials, statistics, awards, certifications or social URLs. Missing data is marked
`[CONTENT TO BE ADDED]` in-page (social profile URLs, blog article bodies, project live links).

## Personal assets

Logos, portrait photographs and mascot © Mashzidul Tanun Borshon. All rights reserved.
Included here for building/deploying this website only.
