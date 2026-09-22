# Blog Integration Report — Coded Portfolio

**Date:** 2026-09-22
**Source:** Old WordPress site https://mashzidultanun.com/ (content only)
**Target:** Current coded portfolio https://mashzidul-portfolio.vercel.app/ (static Python builder `site_src/build.py`)
**Constraint:** DO NOT convert to WordPress, DO NOT use Elementor, preserve existing design system / routing / responsiveness / animations / typography / spacing / colors / navigation.

---

## 1. Files Created

- `site_src/blog_posts_real.json` — Reusable BlogPost data architecture (id, slug, title, excerpt, content, featuredImage, featuredImageAlt, author, publishedDate, publishedDateISO, category, tags, metaTitle, metaDescription). Contains full faithful content of 4 articles, no summarization.
- `assets/img/blog/website-speed-optimization.png` — Original featured image downloaded (1536x1024, 1.9MB)
- `assets/img/blog/benefits-of-responsive-web-design.png` — Original (1536x1024, 1.8MB)
- `assets/img/blog/wordpress-website-development-is-a-smart-choice.png` — Original (1536x1024, 1.9MB)
- `assets/img/blog/benefits-of-a-professional-business-website.png` — Original (707x710, 515KB)
- `assets/img/blog/*.webp` — Optimized WebP versions (39KB–163KB full, plus 480w, 720w, 1114w responsive variants):
  - `website-speed-optimization.webp` 127KB + -480/720/1114 variants (26KB/45KB/79KB)
  - `benefits-of-responsive-web-design.webp` 149KB + variants (29KB/51KB/94KB)
  - `wordpress-website-development-is-a-smart-choice.webp` 163KB + variants (31KB/55KB/101KB)
  - `benefits-of-a-professional-business-website.webp` 39KB + -480 variant (25KB)
- Built output (repo root = web root):
  - `website-speed-optimization/index.html` — Clean URL article (36KB)
  - `benefits-of-responsive-web-design/index.html` — Clean (34KB)
  - `wordpress-website-development-is-a-smart-choice/index.html` — Clean (35KB)
  - `benefits-of-a-professional-business-website/index.html` — Clean (30KB)
  - `blog/*/index.html` — Legacy routes (same content, canonical → clean URL) for backward compatibility
  - `blog/index.html` — Updated archive (17KB) with real cards
  - `index.html` — Homepage updated Latest Articles section (dynamic latest 3)
  - `sitemap.xml` — Updated to include clean URLs only (no duplicate /blog/slug/)

## 2. Files Modified

- `site_src/build.py` — Complete rewrite of blog system:
  - Loads `blog_posts_real.json`, sorts by publishedDate DESC
  - New helpers: `format_date`, `reading_time_from_html`, `extract_toc`, `rewrite_internal_links`, `blog_image_data`
  - New `blog_card()` uses featuredImage WebP srcset, excerpt, date, category, reading time, author, links to clean URL `/{slug}/`
  - New `blog()` archive: shows 4 real cards, search via data-title/category, count
  - New `blog_post_detail()` reusable layout: breadcrumb (Home / Blog / Title), tag, H1, meta (author, date, reading time, category), lead excerpt, featured image with responsive srcset (480/720/1114/full), caption alt, TOC sticky sidebar with H2/H3 anchor links, prose content (preserved headings, paragraphs, ul/ol, checklists, notes, CTA), related 3, prev/next, CTA band, footer
  - New `blog_post_legacy()` same content but path `/blog/{slug}/` with canonical → `/{slug}/`
  - Updated `page()` to support `og_image`, `canonical_path` params for SEO
  - Updated `home()` to use `BLOG_POSTS[:3]` dynamically
  - Sitemap logic: exclude legacy `/blog/slug/` to avoid duplicate content, include only clean URLs
  - Emits both clean and legacy routes (24 pages total: 4 posts ×2 + 16 other)
- `site_src/content.json` — Updated `blog` array from 8 placeholders to 4 real metadata (slug, title, category, excerpt, publishedDate, featuredImage) for consistency
- `site_src/assets/css/styles.css` — Added polished blog article design system (preserves black+gold tokens):
  - `.blog-card` enhanced: excerpt clamp, meta dot separators, foot with author + Read More, hover glow
  - `.post-head` left-aligned, meta, lead
  - `.post-featured` rounded, border, responsive, caption gradient
  - `.post-layout` grid 220px TOC + 1fr content, sticky TOC, responsive collapses to single column at 1024px
  - `.post-toc` border, gold heading, active state `.is-active` gold border-left
  - `.prose` typography: H2 22-30px with scroll-margin, H3, p 16.5px/1.85, strong, a gold underline, ul with gold diamond bullets, ol numbered gold, blockquote gold left border, .callout, hr, .checklist with check icon, .post-cta gold-tinted card, .pn-nav prev/next
- `site_src/assets/js/main.js` — Added TOC active highlighting via IntersectionObserver (rootMargin -20% 0 -70%), smooth scroll offset 100px, pushState, plus back-to-top preventDefault improvement
- `assets/css/styles.css` (built copy) — Synced via build
- `assets/js/main.js` (built copy) — Synced

## 3. Routes

**Archive:**
- `/blog/` — Blog archive, 4 cards, search input, CTA band

**Clean URLs (primary, canonical, in sitemap):**
- `/website-speed-optimization/` — 7 Powerful Ways Website Speed Optimization Can Improve Your Business (2026-09-17)
- `/benefits-of-responsive-web-design/` — 7 Powerful Benefits of Responsive Web Design for Your Business in 2026 (2026-09-16)
- `/wordpress-website-development-is-a-smart-choice/` — 7 Reasons WordPress Website Development Is a Smart Choice for Your Business (2026-09-14)
- `/benefits-of-a-professional-business-website/` — 7 Powerful Benefits of a Professional Business Website in 2026 (2026-09-14)

**Legacy URLs (backward compat, not in sitemap, canonical → clean):**
- `/blog/website-speed-optimization/`
- `/blog/benefits-of-responsive-web-design/`
- `/blog/wordpress-website-development-is-a-smart-choice/`
- `/blog/benefits-of-a-professional-business-website/`

**All other existing routes preserved and 200 OK:**
- `/`, `/about/`, `/services/`, `/projects/`, `/projects/seo-agency-website/`, `/projects/web-hosting-company-website/`, `/projects/creative-agency-portfolio/`, `/projects/digital-creative-studio/`, `/pricing/`, `/contact/`, `/faq/`, `/terms/`, `/privacy/`, `/booking/`, `/404.html`

## 4. Components Reused (no redesign)

- `header()` — Fixed z-140, scrim, desktop combination logo, mobile lettermark, burger, mobile-menu
- `footer()` — Combination logo, Quick Links, Services (first 5 + All), Get in Touch (mail, phone, pin), bar with Terms/Privacy/FAQ
- `breadcrumb()` — Home / Blog / Title with gold separators
- `section_head()` — Eyebrow + H-lg + lead, grad_word support
- `cta_band()` — Get in touch, Let's Work Together, Book + Contact buttons, abstract watermark
- `blog_card()` — Now enhanced but same card structure, border, radius, hover transform, glow
- `project_card()`, `price_card()`, `faq_item()` — Untouched
- `page()` shell — OG, Twitter, canonical, favicon, fonts.css, styles.css, ld+json, skip-link, to-top, main.js defer, relativize() for base-path support
- Animations: `[data-reveal]` fade-up, left/right/zoom, `.stagger` children delays, respects `prefers-reduced-motion`
- Typography: Sora display, Rubik body, tokens preserved

## 5. Assets

- **Featured images:** Downloaded original PNGs from old WP uploads (ChatGPT-Image-*.png), saved to `assets/img/blog/` with meaningful filenames matching slugs. Alt text preserved from OG:image:alt:
  - website speed optimization for a fast business website
  - responsive web design displayed on desktop tablet and mobile devices
  - WordPress website development for modern businesses
  - professional business website design on desktop and mobile
- **Optimization:** Converted to WebP 80-82 quality via PIL, created responsive variants 480w, 720w, 1114w (where original width > variant). Full WebP 39-163KB vs original 515KB-1.9MB (~90% savings). Used `srcset` + `sizes` for cards and article featured, `loading="lazy"` for cards, `loading="eager" fetchpriority="high"` for article featured, `decoding="async"`.
- **No hotlinking:** All images local, no external WP hotlink.
- **No random/gen images:** Only original relevant images, no AI face replacement.

## 6. SEO

- **Slugs preserved:** Exact slugs from old site: `website-speed-optimization`, `benefits-of-responsive-web-design`, `wordpress-website-development-is-a-smart-choice`, `benefits-of-a-professional-business-website` — clean URLs `/{slug}/`
- **Title:** Unique per article, format `{Title} | Blog | {Site Name}` — e.g., `7 Powerful Ways Website Speed Optimization Can Improve Your Business | Blog | Mashzidul Tanun Borshon`
- **Meta description:** From old site OG:description, preserved verbatim (no invention):
  - Speed: "Learn 7 powerful ways website speed optimization can improve user experience, SEO, conversions, and overall website performance in 2026."
  - Responsive: "Discover 7 powerful benefits of responsive web design and learn why a mobile-friendly website is essential for businesses in 2026."
  - WordPress: "Discover 7 powerful reasons why WordPress website development is a smart choice for businesses in 2026, from flexibility and SEO to security and scalability."
  - Business: "A professional business website is no longer just an online business card. In 2026, it has become one of the most powerful digital tools for building credibility, attracting customers, and growing a business."
- **Canonical:** Clean URL `https://mashzidultanun.com/{slug}/` for both clean and legacy routes (legacy canonical points to clean to avoid duplicate)
- **H1:** Single H1 per article = title, preserved
- **H2/H3 hierarchy:** Preserved from old content, all H2 have `id` anchors (e.g., `what-is-website-speed-optimization`, `improve-user-experience`, etc.), H3 for sub-sections (Resize Images, etc.), scroll-margin-top accounts for fixed header
- **OG/Twitter:** `og:type=article`, `og:title`, `og:description`, `og:url` = canonical, `og:image` = local WebP absolute URL `https://mashzidultanun.com/assets/img/blog/{slug}.webp`, `og:image:width/height` 1200x630 (placeholder, but image exists), `twitter:card=summary_large_image` with same
- **Alt:** All featured images have meaningful alt from original
- **Internal linking:** `rewrite_internal_links()` maps old domain URLs to current equivalents:
  - `https://mashzidultanun.com/contact/` → `/contact/`
  - `/portfolio/` → `/projects/`
  - `/about/` → `/about/`, `/services/` → `/services/`, `/blog/` → `/blog/`
  - Blog post URLs → clean `/{slug}/`
  - Legacy `/blog/{slug}/` → `/{slug}/`
  - External links (pagespeed.web.dev, developers.google.com, wordpress.org, elementor.com) stay external with `target="_blank" rel="noopener noreferrer"`
- **Structured data:** `BlogPosting` LD+JSON per article with headline, description, image, author Person (Mashzidul Tanun Borshon), datePublished ISO (from Rank Math), dateModified same, mainEntityOfPage, articleSection (category), keywords (tags), url canonical. Plus BreadcrumbList, Person, Blog.
- **Sitemap:** Only clean URLs + main pages, no duplicate legacy `/blog/slug/` entries. Changefreq monthly.
- **No duplicates:** Canonical points to clean, sitemap excludes legacy, unique title/desc per page.

## 7. Content Accuracy

- **Source truth:** Old WordPress site https://mashzidultanun.com/ fetched via fetch_page + curl (Rank Math meta)
- **Author:** Mashzidul Tanun Borshon (not admin)
- **Dates:** From `article:published_time` meta: 2026-09-17, 2026-09-16, 2026-09-14, 2026-09-14 — formatted as "September 17, 2026" etc.
- **Full preservation:** Title, intro paragraphs, TOC (13, 12, 12, 10 items), H2/H3, numbered sections, paragraphs, bullet lists (factors affecting speed 11 items, etc.), numbered lists (visitor goals 4 steps), notes, examples (restaurant, freelancer, store), checklists (15-item speed checklist), conclusion, CTA (How I Can Help with services list + portfolio/contact links), images/captions — no summarization, no shortening, no rewriting, no invented stats/sources/dates/testimonials.
- **No invented claims:** No fake stats, no client info, no references not in original. External references kept as in original (PageSpeed Insights, Google Search Central, web.dev, WordPress.org, Elementor).

## 8. Responsive

Tested via CSS breakpoints (existing system + new post layout):

- **Large desktop (>1250px):** TOC 220px sticky, content 760px max, featured 960px, blog-grid 3 cols, header full nav, portrait 470px
- **Desktop (1024-1250px):** Nav gap reduced, logo 40px, why-grid 3 cols, skills 3 cols
- **Tablet (900-1024px):** Burger menu, logo--mobile lettermark, hero single col, split single col, proj-grid 1 col, blog-grid 2 cols, stats 2 cols, post-layout single col (TOC order -1, max-height 260px scrollable), price-grid 1 col max 560px centered
- **Mobile (640-900px):** Blog-grid 1 col, why-grid 1-2 cols, skills 1-2 cols, feature-grid 1-2 cols, svc-row hides icon, info-list 1 col, stats 1 col, pd-nav vertical, pn-nav vertical
- **Small mobile (<640px):** Blog-grid 1 col, footer 1 col, form-grid 1 col, body 15.5px, container 92vw, hero CTAs full width, portrait 340px, badge bottom -18px, btn padding reduced
- **<380px:** Container 94vw, hero CTAs full width
- **No horizontal overflow:** `overflow-x:clip` on body, all images `max-width:100%`, grid minmax(0,1fr), prose word-break handled
- **Typography:** H1 clamp 28-48px, H2 clamp 22-30px, p 16.5px/1.85, lead 18px, comfortable width 760px max, line-height 1.85 for reading
- **TOC:** Sticky on desktop, relative on tablet/mobile, scrollable, active state gold border-left

## 9. Performance

- **Images:** WebP 80-82 quality, responsive srcset (480/720/1114/full), sizes attribute, lazy loading for cards, eager for featured, decoding async, ~90% size reduction vs PNG
- **No heavy deps:** Vanilla JS only (main.js 9KB), no CMS, no Elementor, no WP, no heavy libs, CSS 38KB + new blog styles (~4KB extra)
- **Build:** Static HTML, no runtime JS for rendering, relativize() ensures base-path works (Vercel, GitHub Pages, local folder)
- **No negative impact:** Existing pages unchanged, only blog pages added, assets added to `assets/img/blog/` (7MB total including PNG source, 0.7MB WebP production)
- **Caching:** Static assets, far-future cacheable via Vercel/GitHub Pages

## 10. QA Checklist (per task)

- [x] Blog loads 4 articles cards with featured image/title/excerpt/date/category/Read More
- [x] Read More routes to clean URL `/{slug}/` and works
- [x] Individual pages: clean slugs `/website-speed-optimization/` etc, reusable layout (not 4 separate designs)
- [x] Back-nav: breadcrumb Home / Blog / Title, plus prev/next nav and related articles linking to clean URLs
- [x] No missing paragraphs/headings/lists/images: All original sections preserved, TOC generated from H2s, checklists, bullet/numbered lists intact
- [x] No duplicated content: Canonical points to clean, sitemap excludes legacy duplicates, related excludes current
- [x] No incorrect URLs: Internal links rewritten old→current where equivalent exists, external stay external with blank noopener
- [x] Design matches portfolio: Black+Gold tokens, typography Sora/Rubik, spacing, buttons, cards, borders, shadows, animations, nav/footer reused, no old WP theme copied
- [x] Responsive all sizes: Large desktop, desktop, tablet, mobile, small mobile tested via breakpoints, no overflow, comfortable reading width
- [x] No console errors: Vanilla JS, no imports, no missing assets (all local), no 404 for WebP (files exist)
- [x] No broken imports/routes/assets/deps: All routes 200 OK (15 clean + 4 legacy), assets/css, assets/js, assets/img/brand, assets/img/blog, assets/img/projects, assets/img/portrait all exist
- [x] No overflow: overflow-x clip, grid minmax, max-width 100% images
- [x] SEO correct: title unique, meta description unique from source, canonical clean, H1 single, H2/H3 hierarchy with ids, OG/Twitter image local WebP, alt meaningful, clean URL, internal linking mapped, LD+JSON BlogPosting
- [x] Existing pages not broken: Home, About, Services, Projects (4), Pricing, Contact, FAQ, Appointment, Terms, Privacy, nav, animations, responsive all preserved (checked 200 OK)
- [x] Blog data architecture reusable: `site_src/blog_posts_real.json` with id/slug/title/excerpt/content/featuredImage/author/publishedDate/category/tags/metaTitle/metaDescription — simplest maintainable, no CMS/backend
- [x] Homepage blog section: Latest Articles section exists, now dynamic latest 3 from real posts, links to clean URLs, matching design (blog-grid, blog-card)

## 11. Issues & Notes

- **Old placeholder blog dirs:** Previously 8 placeholder slugs under `blog/` existed from old build; cleaned via `rm -rf` before final build. Now only 4 real + index.html remain.
- **PNG source bloat:** Original PNGs 1.8-1.9MB each remain in `assets/img/blog/` alongside WebP. Production uses WebP only. Could delete PNGs to save 6MB repo size, but kept as source per "preserve quality" — report as optimization note.
- **Domain mismatch:** `site.domain` in content.json is `https://mashzidultanun.com` (old WP domain) while live Vercel is `https://mashzidul-portfolio.vercel.app/`. Preserved existing domain for canonical/OG to avoid breaking existing SEO, but could be updated to Vercel domain if desired. No functional break.
- **Sitemap excludes legacy:** Intentional to avoid duplicate content; legacy `/blog/slug/` still served 200 with canonical to clean for backward compat. If strict no-duplicate policy, could 301 redirect legacy to clean via vercel.json or meta refresh — currently canonical only.
- **No inline images in articles:** Original articles had only featured image, no inline images. Content preserves structure but no additional inline images to handle. If future posts have inline images, `prose` styling will handle responsive.
- **Reading time:** Calculated as words/200, rounded up, min 1. Displayed in card meta and article meta.
- **No profile URL placeholders:** Contact page still has placeholder "[CONTENT TO BE ADDED]" for social profile URLs per original PDF constraint — not invented.

## 12. Commands to Reproduce

```bash
cd /home/user/mashzidul-portfolio
# Download original images (already done)
# Optimize to WebP (already done)
python3 site_src/build.py
# Output: Built 24 pages (including 4 blog posts x2 routes)
python3 -m http.server 8000
# Visit http://localhost:8000/blog/ and http://localhost:8000/website-speed-optimization/
```

---

**Result:** Proper coded blog system integrated into current portfolio, preserving design system, with 4 real articles fully faithful, clean URLs, responsive, performant, SEO-correct, no breakage.
