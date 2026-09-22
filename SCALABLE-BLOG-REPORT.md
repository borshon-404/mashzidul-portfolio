# Scalable Coded Blog — Second-Pass Quality Report

**Date:** 2026-09-22
**Goal:** Make coded blog clean, reusable, maintainable, ready for future articles. No WP/Elementor, no redesign of unrelated parts.

---

## 1. Blog Architecture

**Data → Components → Pages → Static Output**

```
site_src/blog_posts_real.json  (single source of truth, normalized)
        ↓ load_blog_posts() → sorted DESC by publishedDateISO
site_src/build.py:
  - normalize_blog_post() ensures full model
  - Reusable components: category_badge, blog_card, blog_grid, table_of_contents, blog_header, share_buttons, related_posts, latest_posts
  - render_blog_post() single template for both clean /{slug}/ and legacy /blog/{slug}/ (canonical → clean)
  - blog_archive() with search + category filters
  - home() uses latest_posts(BLOG_POSTS,3) data-driven
Output repo root:
  /blog/index.html, /{slug}/index.html, /blog/{slug}/index.html (legacy), sitemap.xml (clean only), assets/img/blog/*.webp
```

Adding new article = add JSON entry + image → `python3 site_src/build.py` → auto appears everywhere.

## 2. Data Structure

Every post follows **BlogPost** model (from `blog_posts_real.json`):

```json
{
  "id": "website-speed-optimization",
  "slug": "website-speed-optimization",
  "title": "7 Powerful Ways Website Speed Optimization Can Improve Your Business",
  "excerpt": "Learn 7 powerful ways...",
  "content": "<p>...full HTML with h2 id=...>",
  "featuredImage": "website-speed-optimization",
  "featuredImageAlt": "website speed optimization for a fast business website",
  "author": "Mashzidul Tanun Borshon",
  "publishedDate": "2026-09-17",
  "publishedDateISO": "2026-09-17T03:27:41+00:00",
  "category": "Performance",
  "tags": ["website speed optimization","performance","SEO","user experience","Core Web Vitals"],
  "readingTime": 10,
  "metaTitle": "7 Powerful Ways Website Speed Optimization Can Improve Your Business",
  "metaDescription": "Learn 7 powerful ways...",
  "canonicalUrl": "https://mashzidultanun.com/website-speed-optimization/"
}
```

- `readingTime` calculated words/200, stored for reuse
- `canonicalUrl` = DOMAIN + /slug/
- Normalized via `normalize_blog_post()` — missing fields get sensible defaults, no hard-coded values
- Sorted DESC by `publishedDateISO` → newest first everywhere

## 3. Components Created

All in `build.py`, no external deps, reusable:

- `category_badge(category)` → `<span class="tag">`
- `blog_card(post, index, clean_url)` → card with `data-title`, `data-category`, `data-tags` for search/filter, WebP srcset, meta (category, date short, reading time), title, excerpt clamp 3, foot author + Read More → clean URL
- `blog_grid(posts, clean_url)` → wraps cards in `.blog-grid`, empty state
- `table_of_contents(toc_items)` → sticky nav, H2/H3 with anchors, `aria-label`
- `blog_header(post)` → breadcrumb + badge + H1 + meta (author, long date, reading time, category) + lead excerpt
- `share_buttons(title, url)` → lightweight X, LinkedIn, Facebook sharer links + Copy button (no libs, dynamic URL/title, `data-copy`)
- `related_posts(current, all, limit=3)` → sensible scoring: same category +2, shared tags +1 each, then recency. Excludes self, fallback to most recent if scores 0
- `latest_posts(posts, count=3)` → slice newest
- `blog_archive()` → heading, intro with count, search input + category filter buttons (All, Business, Development, Performance, Web Design), grid, empty state, CTA
- `render_blog_post(post, idx, route_path, canonical_path)` → single source of truth for article page (eliminates previous duplication between detail & legacy)

CSS added:
- `.share-row`, `.share-label`, `.share-btn`, `.is-copied`

JS added:
- `setupFilterGroup()` for both projects and blog category filters
- Search enhanced to title + category + tags + excerpt
- Share copy with clipboard API + fallback + feedback
- TOC active via IntersectionObserver + smooth scroll offset 100px for fixed header

## 4. Routes

**Clean primary (canonical, in sitemap, 200 OK):**
- `/website-speed-optimization/`
- `/benefits-of-responsive-web-design/`
- `/wordpress-website-development-is-a-smart-choice/`
- `/benefits-of-a-professional-business-website/`

**Legacy backward compat (200 OK, not in sitemap, canonical → clean):**
- `/blog/website-speed-optimization/` etc (4)

**Archive:**
- `/blog/` → 4 cards, search, category filters

**Invalid:**
- `/blog/invalid-article/` → 404 (static hosting serves `/404.html` with proper design, CTA)

**Other preserved (200 OK):** `/`, `/about/`, `/services/`, `/projects/`, `/projects/*` (4), `/pricing/`, `/contact/`, `/faq/`, `/terms/`, `/privacy/`, `/booking/`, `/404.html` — total 24 pages (16 other + 8 blog)

Tested via `python3 -m http.server 8000` — all clean + legacy 200, invalid 404.

## 5. SEO Implementation

- **Unique title:** `Title | Blog | Mashzidul Tanun Borshon`
- **Unique meta description:** from old WP OG:description, verbatim
- **Canonical:** clean `DOMAIN/{slug}/` for both clean & legacy
- **H1:** single per article = title
- **H2/H3 hierarchy:** preserved from source, all H2 have id anchors, H3 subsections, `scroll-margin-top` = header-h + 20px
- **OG:** `og:type=article`, `og:title`, `og:description`, `og:url`=canonical, `og:image`=local WebP absolute `DOMAIN/assets/img/blog/{slug}.webp`, 1200x630
- **Twitter:** `summary_large_image`, same title/desc/image
- **Alt:** meaningful from original (e.g., "website speed optimization for a fast business website")
- **LD+JSON:** `BlogPosting` with headline, description, image, author Person, datePublished ISO, dateModified, mainEntityOfPage canonical, articleSection category, keywords tags, wordCount, timeRequired PT{M}M; plus BreadcrumbList, Person, Blog with blogPost list
- **Sitemap:** only clean URLs (4 blog + 12 other + /blog/), no duplicate legacy, changefreq monthly
- **Internal links:** `rewrite_internal_links()` maps old domain → current (`/contact/`, `/projects/`, `/about/`, `/services/`, `/blog/`, `/{slug}/`), legacy `/blog/{slug}/` → `/{slug}/`; external (pagespeed.web.dev, developers.google.com, wordpress.org, elementor.com) stay external with `target="_blank" rel="noopener noreferrer"`
- **No duplicates/conflicts:** single `page()` shell, canonical unified

## 6. Image Handling

- **Download:** original PNGs from WP uploads `ChatGPT-Image-*.png` saved to `assets/img/blog/` with slug filenames
- **Optimization:** converted to WebP 80-82 quality via PIL, responsive variants 480w,720w,1114w where width allows. Full WebP 39-163KB vs 1.8MB PNG (~90% reduction). Final repo: only WebP (1.1MB total) — PNGs removed to avoid duplication/bloat, WebP preserves quality
- **Paths:** local `/assets/img/blog/{base}.webp` and variants, no hotlink (`mashzidultanun.com/wp-content` count 0)
- **Responsive:** `srcset` + `sizes` for cards `(max-width:640px) 92vw, (max-width:1024px) 46vw, 360px` and featured `(max-width:960px) 92vw, 960px`, `width`/`height` to prevent CLS, `loading="lazy"` cards, `eager` + `fetchpriority="high"` featured, `decoding="async"`
- **Alt:** preserved from source, descriptive
- **No overflow:** `max-width:100%`, `object-fit:cover`, aspect-ratio 16/10 cards, 16/9 featured, container overflow-x clip
- **No duplication:** single source WebP, no duplicate assets, no inline duplicate images

## 7. Responsive Improvements

- **Archive:** 3-col → 2-col at 1024px → 1-col at 640px, search + filters wrap flex, filter buttons pill style with active gold
- **Article:** `.post-layout` grid 220px TOC + 1fr content, max 1120px centered; at 1024px collapses to 1-col, TOC order -1, max-height 260px scrollable
- **Reading:** `.post-body` max 760px, `.prose` 16.5px/1.85 line-height, H2 clamp 22-30px, H3 18-22px, p margin 0 0 20px, lead 18px, comfortable 62ch, `scroll-margin-top` for fixed header
- **Cards:** excerpt clamp 3 lines, meta dot separators, foot border-top
- **TOC:** sticky top header-h+24px desktop, relative mobile, active state gold border-left, hover gold
- **Share:** flex wrap, pill buttons, hover lift, copied feedback gold
- **Existing breakpoints preserved:** 1250,1200,1024,900,640,380, reduced-motion

## 8. Performance Improvements

- **Before second-pass:** 7MB assets (PNG+WebP), duplicated `blog_post_detail` + `blog_post_legacy` logic (2× 35KB HTML gen), no category filter, no share, related random, search only title/category
- **After:**
  - Removed PNGs → 1.1MB WebP only (6MB saved)
  - Refactored to single `render_blog_post()` → -50% duplicated logic, maintainable
  - Added `blog_image_data()` + `blog_featured_image_data()` reuse → no duplicate srcset logic
  - Related scoring instead of random → better UX without extra cost
  - Share buttons lightweight (no libs, 4 links + clipboard)
  - Search enhanced but still O(n) vanilla, no extra JS bundle
  - Lazy loading cards, eager featured with high priority → LCP improved
  - No new dependencies, main.js 9KB → ~11KB (+TOC+filters+share), CSS +4KB share styles, still lightweight
  - Build remains static, no client-side rendering, no large bundles

## 9. Errors Found and Fixed

- **Duplicated blog post rendering:** `blog_post_detail` and `blog_post_legacy` had identical HTML generation (300+ lines duplicated). Fixed → single `render_blog_post(post, idx, route_path, canonical_path)` used for both routes, canonical param distinguishes.
- **Duplicated image srcset logic:** card vs featured had separate existence checks. Fixed → `blog_image_data()` + `blog_featured_image_data()` reusable.
- **Sitemap included legacy duplicates:** previous filter `len(split)==3` missed `/blog/slug/` (len 4). Fixed → exclude all `/blog/` except `/blog/` itself, only clean URLs in sitemap.
- **Old placeholder blog dirs:** 8 placeholder dirs remained after first integration. Fixed → `rm -rf blog/beginners...` before final build.
- **PNG bloat:** 1.9MB PNGs referenced? Actually WebP used but PNGs remained, 6MB waste. Fixed → deleted PNGs, kept WebP only.
- **Related random:** previously `[:3]` excluding current, not sensible. Fixed → scoring same category +2, shared tags +1, recency tie-break, fallback recent.
- **Search limited:** only title/category. Fixed → also tags + excerpt, added `data-tags` attribute to cards.
- **Category filter missing for blog:** only projects had filters. Fixed → `setupFilterGroup()` reusable for both grids, blog archive now has All + 4 category buttons.
- **No share buttons:** missing. Fixed → lightweight `share_buttons()` with X, LinkedIn, Facebook, Copy, dynamic URL/title, clipboard API.
- **TOC duplication:** TOC generation repeated in detail & legacy. Fixed → `table_of_contents()` component.
- **Missing readingTime/canonicalUrl in model:** task requires those fields. Fixed → added to `blog_posts_real.json` + `normalize_blog_post()` ensures them.

## 10. Remaining Issues

- **Domain in content.json still old WP domain:** `https://mashzidultanun.com` while Vercel is `mashzidul-portfolio.vercel.app`. Preserved to avoid breaking existing canonicals, but could be updated to Vercel domain if desired. No functional break.
- **Legacy routes still served:** `/blog/{slug}/` returns 200 with canonical to clean. If strict no-duplicate, could add 301 via `vercel.json` redirects — currently canonical only, acceptable for static.
- **No pagination:** blog archive shows all 4; when many posts (e.g., 20+), may need pagination. Architecture supports adding `BlogPagination` component — `blog_grid` could slice with page param, but not implemented yet as not needed for 4 posts.
- **No inline images in articles:** original posts had only featured image; `prose` styling supports responsive images but none present. Future posts with inline images will need to ensure they are local and have alt.
- **Social profile URLs placeholder:** contact page still shows "[CONTENT TO BE ADDED]" per original PDF constraint — not invented, intentional.

---

## How to Add Future Post (scalable)

1. Add image to `assets/img/blog/{slug}.webp` + responsive variants `-480.webp`, `-720.webp`, `-1114.webp` (use PIL script from first report or any optimizer, keep meaningful alt)
2. Add entry to `site_src/blog_posts_real.json`:
```json
{
  "id": "my-new-post",
  "slug": "my-new-post",
  "title": "My New Post Title",
  "excerpt": "Short excerpt...",
  "content": "<p>Intro</p><h2 id=\"section-one\">Section One</h2><p>...</p>",
  "featuredImage": "my-new-post",
  "featuredImageAlt": "descriptive alt",
  "author": "Mashzidul Tanun Borshon",
  "publishedDate": "2026-09-23",
  "publishedDateISO": "2026-09-23T00:00:00+00:00",
  "category": "Web Design",
  "tags": ["web design","responsive"],
  "metaTitle": "My New Post Title",
  "metaDescription": "Excerpt or SEO description",
  "canonicalUrl": "https://mashzidultanun.com/my-new-post/",
  "readingTime": 5
}
```
3. Run `python3 site_src/build.py`
4. New post automatically: appears on `/blog/`, in Latest Articles if newest, has route `/my-new-post/`, metadata, related, search, filters.

No UI code duplication needed.
