# COMPLETE TECHNICAL AUDIT & CPANEL CMS IMPLEMENTATION PLAN
## Mashzidul Tanun Borshon Portfolio — Static → Full-Stack CMS

**Date:** 2026-09-23
**Audit Scope:** Full codebase inspection, no modifications
**Current Deployment:** Vercel (static), GitHub: borshon-404/mashzidul-portfolio
**Target Deployment:** Standard cPanel (Apache + PHP + MySQL/MariaDB)
**Design Constraint:** Frontend is source of truth — DO NOT redesign

---

## 1. CURRENT PROJECT AUDIT

### Frontend Technology
- **Language:** HTML5, CSS3, Vanilla JavaScript (no framework)
- **CSS Architecture:** Custom design system, 629 lines styles.css
  - Variables: `--line`, `--line-2`, `--gold-200/300`, `--bg-2`, `--ink`, `--ease`
  - Components: header, footer, hero, stats, svc-rows, proj-card, price-card, blog-card, post-layout, prose, forms, filters, share-row, social-links
  - Fonts: Rubik 400/500, Sora 600/700 via `fonts.css` + woff2 self-hosted (no Google Fonts)
  - Animations: IntersectionObserver scroll-reveal, counter animation, CSS transitions (transform .25s)
- **JS:** `site_src/assets/js/main.js` 287 lines vanilla
  - No dependencies, no bundler, no npm deps (package.json dependencies: {} empty)
  - Features: header scroll state, burger mobile menu (aria-expanded), scroll-reveal, counters, FAQ accordion, project/blog filters, blog search (data-title/category/tags), share copy (clipboard API), forms (validation + mailto fallback), TOC active state, back-to-top, year
- **No frontend framework:** Not React, Vue, Next, etc.

### Programming Languages
- **Build system:** Python 3 (`site_src/build.py` 1216 lines)
- **Server:** Node.js zero-dependency static server (`server.js` 74 lines) — only for local dev / Vercel?
- **Frontend:** HTML/CSS/JS

### Frameworks & Libraries
- **None** — intentionally zero-dependency for performance
- Icons: Inline SVG stroke-based ICONS dict (24 icons) + filled SOCIAL_ICONS (6 brand icons) — no FontAwesome, no external icon library
- No jQuery, no Bootstrap JS (only CSS class naming similar but custom)

### Build System
- **File:** `site_src/build.py`
- **Input:** `site_src/content.json` (site settings, hero, about, services, projects, pricing, FAQ, contact, etc.) + `site_src/blog_posts_real.json` (4 full articles with id, slug, title, excerpt, content HTML, featuredImage base name, author, publishedDate ISO, category, tags, readingTime, metaTitle, metaDescription, canonicalUrl)
- **Process:**
  - Load JSON, normalize blog posts (reading time calc words/200, date parsing)
  - Sort posts by publishedDateISO DESC
  - Functions: `e()` html escape, `relativize()` rewrites /assets/ to ../ etc based on depth, `blog_image_data()` checks OUT/assets/img/blog for responsive variants, `rewrite_internal_links()` maps mashzidultanun.com URLs to local clean URLs
  - Components: header(), footer() with socials, breadcrumb(), section_head(), cta_band(), project_card(), price_card(), blog_card(), share_buttons(), table_of_contents(), etc.
  - Page shell `page()` generates full HTML with <head> SEO, canonical, OG, Twitter, JSON-LD, header, main, footer, to-top, main.js
  - `emit()` writes to OUT = repo root (web-root layout) — e.g., `/about/` → `about/index.html`
- **Output:** Repo root itself is the static site (16 other pages + 8 blog pages = 24 total). Assets copied: fonts.css, styles.css, main.js
- **Sitemap:** Only clean URLs, no legacy duplicates, sorted, monthly changefreq
- **Robots:** `User-agent: * Allow: / Sitemap: https://mashzidultanun.com/sitemap.xml`
- **No buildCommand on Vercel:** `vercel.json` has `"buildCommand": null, "outputDirectory": "."` — expects pre-built static files committed to repo (Python build runs locally, not on Vercel)

### Routing System
- **Clean URLs:** `/about/` → `about/index.html`, `/website-speed-optimization/` → `website-speed-optimization/index.html`
- **Legacy blog routes:** `/blog/{slug}/` also emitted but canonical → clean `/{slug}/` (preserves backward compat, SEO canonical prevents duplicate)
- **Server.js:** Handles clean-URL: if path is directory, serve index.html; if file not found, try path/index.html; fallback 404.html
- **Vercel:** trailingSlash true, headers for caching fonts (1yr immutable), css/js (1d), img (7d)

### Static Generation System
- **Build-time static generation:** Python script generates all HTML at build time, no SSR, no ISR
- **Data:** JSON files are source, not DB
- **Images:** WebP with responsive srcset (480w,720w,1114w, original) — files exist in `assets/img/blog/`, `assets/img/portrait/`, `assets/img/brand/`, `assets/img/projects/` (SVG placeholders)
- **Blog architecture:** Scalable — `load_blog_posts()` reads array, sorts, renders grid + detail via single `render_blog_post()` template; related_posts() scores by same category +2, shared tags +1, recency; latest_posts() first 3; category filter + search via data-attributes; TOC extraction via regex `<h[23] id="">`; share buttons X/LinkedIn/Facebook + copy

### Server-side Code
- **None in production:** Only `server.js` for local dev — zero-dependency http.createServer, MIME types, cache-control, path traversal protection (normalize + startsWith ROOT check)
- No PHP, no Express API, no serverless functions folder (no /api)

### API Endpoints
- **None** — `FORM_ENDPOINT = null` in main.js, fallback to `mailto:mail@mashzidultanun.com` with prefilled subject/body
- Forms: `data-form="contact"` and `data-form="booking"` — JS validation regex `/^[^\s@]+@[^\s@]+\.[^\s@]+$/`, required fields, status messages, but no backend POST

### Serverless Functions
- **None**

### Database Connections
- **None** — content.json + blog_posts_real.json are flat-file DB

### Authentication
- **None** — static site, no admin, no login

### Form Handling
- **Current:** Client-side validation only, then `window.location.href = mailto:` with subject/body encoded. No storage, no spam protection, no rate limiting. Booking form collects agenda checkboxes via `querySelectorAll('input[name=\"agenda\"]:checked')`
- **Production need:** Backend validation, DB storage, email notification, CSRF, spam protection

### External APIs
- **None** — no Formspree, no EmailJS, no Google APIs in code (only external links in blog content to pagespeed.web.dev, developers.google.com, wordpress.org, elementor.com — all with `target="_blank" rel="noopener noreferrer"`)

### Environment Variables
- **None** — `server.js` reads `process.env.PORT || 8080`, no .env file, no secrets in frontend

### Image Handling
- **Strategy:** WebP primary, PNG fallback for brand, SVG for project art
- **Responsive:** `blog_image_data()` checks filesystem for -480, -720, -1114 variants, builds srcset with sizes `(max-width: 640px) 92vw, (max-width: 1024px) 46vw, 360px` for cards, `(max-width: 960px) 92vw, 960px` for featured
- **Optimization:** Manual — source-assets contain original PNGs, built assets are WebP 480/720/1114 + original; no on-the-fly resizing, no sharp, no CDN
- **Alt text:** All images have alt, blog has featuredImageAlt from JSON, portrait has descriptive alt, decorative has `alt="" aria-hidden="true"`
- **Favicon:** 32,64, apple-touch 180, og 1200x630 PNG

### Blog Architecture (Detailed)
- **Model:** `blog_posts_real.json` — 4 posts, each with required fields per task: id, slug, title, excerpt, content (full HTML with h2 id, p, ul, ol, a, strong), featuredImage (base name), featuredImageAlt, author, publishedDate (YYYY-MM-DD), publishedDateISO (ISO8601), category, tags array, readingTime, metaTitle, metaDescription, canonicalUrl
- **Routes:** Clean `/{slug}/` primary, legacy `/blog/{slug}/` secondary, both use same `render_blog_post()` — canonical_path param ensures `<link rel="canonical" href="https://mashzidultanun.com/{slug}/">` even on legacy route
- **Templates:** `blog_archive()` — hero + search input + category filter buttons + `blog_grid()` + empty state + CTA; `render_blog_post()` — page-hero with blog_header (breadcrumb, category badge, h1, meta, lead) + post-featured image with cap + post-layout (TOC nav + post-body prose + share + post-cta + prev/next) + related articles bg-vignette + CTA
- **Related logic:** `related_posts()` scores, fallback to most recent if all scores 0, excludes current, limit 3
- **SEO:** BlogPosting JSON-LD with headline, description, image, author Person, datePublished, dateModified, mainEntityOfPage, articleSection, keywords, wordCount, timeRequired PT{M}M
- **Internal linking:** `rewrite_internal_links()` maps old mashzidultanun.com URLs to local clean URLs, also maps /blog/{slug}/ to /{slug}/
- **WebP:** 4 variants per post exist
- **Legacy preservation:** Both routes emitted, sitemap only clean, audit checks local link resolution

### SEO Architecture
- **Canonical:** Every page `page()` generates `<link rel="canonical" href="{DOMAIN}{path}">` with DOMAIN from content.json `https://mashzidultanun.com`
- **Meta:** title, description from per-page, OG site_name, type (website/article), title, description, url, image (og-1200x630 or blog featured), width/height, locale en_US, twitter card summary_large_image, title, description, image, theme-color #060605
- **JSON-LD:** Person (with sameAs 6 socials), WebSite, ProfessionalService with makesOffer, BreadcrumbList, ItemList for services/projects, Blog with blogPost array, BlogPosting with detailed, FAQPage, OfferCatalog, CreativeWork, Appointment, etc.
- **Sitemap:** `sitemap.xml` 22 URLs (16 other + 4 blog clean + blog index + home?), sorted, only clean, no legacy
- **Robots:** Allow all, sitemap reference
- **Headings:** audit.py checks single H1 per page
- **Alt text:** audit.py checks img without alt
- **Internal linking:** breadcrumb nav, related posts, prev/next, header/nav/footer quick links/services
- **OG image:** brand og-1200x630.png default, blog uses featured WebP
- **No structured data file:** JSON-LD inline in <head>

### Vercel-Specific
- `vercel.json`: buildCommand null (pre-built), outputDirectory ".", trailingSlash true, headers for caching
- No vercel functions, no rewrites, no redirects file
- Deployment assumes static files committed (Python build locally)

### GitHub/Vercel Assumptions
- Repo root is web root (not /public, /dist)
- No build step on Vercel — relies on committed HTML
- No env vars needed
- Node >=16 for server.js local dev, but Vercel serves static without Node

### Classification

**A. Static Frontend:** 100% — all HTML/CSS/JS/assets are static, no SSR
**B. Build-time:** Python build.py that generates HTML from JSON + copies assets, sitemap, robots
**C. Server-side:** Only dev server.js (zero-dep static), no production server logic
**D. Backend/API:** None — FORM_ENDPOINT null
**E. Database:** None — flat JSON files
**F. External third-party:** None in code (only external links in blog content)

---

## 2. CPANEL COMPATIBILITY

### Current Requirements to Run on cPanel

**What currently runs on cPanel without changes:**
- Static HTML/CSS/JS/WebP/PNG/SVG/WOFF2 — fully compatible with Apache on cPanel
- No PHP needed for current static site
- No Node needed (server.js only dev)
- No MySQL needed
- No Python needed at runtime (only build time)

**To run *as is* on cPanel:**
- Upload repo root contents to `public_html/` (or domain document root)
- No .htaccess needed if Apache serves index.html from directories (cPanel does by default), but for clean URLs `/about` → `/about/` → `/about/index.html`, need mod_rewrite or ensure DirectoryIndex
- Ensure MIME types for .webp, .woff2 (cPanel Apache usually has)
- SSL via cPanel AutoSSL (Let's Encrypt)

**What CMS will require:**
- PHP: Standard cPanel has PHP 8.1-8.3 selectable — perfect
- MySQL/MariaDB: cPanel has MySQL 8 / MariaDB 10.6+ — needed for CMS
- Apache: mod_rewrite required for clean URLs and admin routing
- No Node.js required if we use PHP backend (preferred for cPanel reliability)
- No npm required in production (only dev)
- Python: Not needed in production if we migrate to PHP — Python only used for current build, but CMS will be PHP
- Filesystem permissions: uploads folder 755, files 644, config outside public_html or protected
- Upload requirements: `upload_max_filesize` 8-32M, `post_max_size` similar, `memory_limit` 128-256M for WebP handling (GD or Imagick)
- Cron: Optional for sitemap regeneration, cleanup, but not required initially

### Recommended Backend Architecture for cPanel (Most Practical)

**Option A (RECOMMENDED): PHP 8.2+ MVC-lite + MySQL + Apache mod_rewrite, no framework dependency (or minimal)**

**Why:**
- Runs on *any* normal cPanel without Node, without Python, without custom server
- cPanel's PHP Selector allows 8.1/8.2/8.3, MySQL creation via wizard
- Apache .htaccess for clean URLs works everywhere
- No need to ask host to enable Node.js (many shared cPanels don't have Node, or have old Node, or require extra config)
- Performance: PHP server-rendered pages preserve current SEO (no JS hydration needed)
- Security: Well-understood, many hardening guides
- Deployment: Upload via FTP/File Manager, import SQL, set config — simple

**Architecture:**
```
public_html/
  index.php (front controller)
  .htaccess (rewrite all to index.php except assets)
  assets/ (css, js, img, fonts — same as now)
  uploads/ (media library — outside or protected)
  admin/ (admin dashboard PHP)
  includes/
    config.php (DB creds, not in public_html ideally ../config.php)
    db.php (PDO)
    functions.php (helpers: e(), slug, etc.)
    auth.php (session, login)
    upload.php (media handling)
  templates/
    header.php, footer.php, etc. (preserve current visual design exactly)
  api/ (optional for contact form POST)

Outside public_html:
  config.php (DB credentials, secret key)
```

**Alternative Option B (NOT recommended for standard cPanel): Node.js + Express + MySQL**
- Requires cPanel with Node.js Application feature, Passenger, port management, pm2
- Many shared hosts disable or limit Node
- More complex deployment, harder for client to maintain
- Not preferred per task: "Do NOT assume Node.js is available. Prefer architecture that can run reliably on normal cPanel"

**Alternative Option C: PHP + SQLite**
- Simpler than MySQL but cPanel MySQL is standard, SQLite file permissions tricky, less scalable for blog search

**Conclusion: Use PHP 8.2+ + MySQL/MariaDB + Apache mod_rewrite, server-rendered (not SPA), preserve current HTML/CSS/JS as templates, replace JSON data source with DB queries.**

---

## 3. CMS REQUIREMENTS

### Admin Panel Capabilities Needed

**SITE SETTINGS (site_settings table, single row or key-value)**
- site name, title, domain, metaDescription, email, phone, phoneHref, address, location
- logo (combination, lettermark, pictorial, emblem, wordmark, abstract, mascot) — media library references
- favicon 32/64, apple-touch 180, og 1200x630
- socials: platform, label, url, icon, order, visibility

**NAVIGATION (navigation_items)**
- label, url, order, visibility, parent_id (for future dropdown), active flag
- Currently 7 items: Home /, About /about/, Services /services/, Projects /projects/, Pricing /pricing/, Blog /blog/, Contact /contact/ + footer quick links + booking/faq

**HOMEPAGE (pages + page_sections)**
- Hero: greeting, name, role, intro, ctaPrimary, ctaSecondary, portrait hero image
- Marquee: skills list (from skillGroups)
- Why Choose Me: 5 tiles title+icon
- Stats: value, label, note, order
- Services: first 6 rows + link to all
- About split: paragraphs, info-list (4 items), CTA
- Skills: skillGroups group+skills array
- Portfolio: projects grid
- Pricing: price cards
- Blog: latest 3
- CTA band: intro

**ABOUT**
- Paragraphs, info table, skills, How I Work, portrait headshot, emblem seal, mascot, socials

**SERVICES (services)**
- slug unique, title, desc, icon key (palette, code, globe, cart, monitor, refresh, wrench, gauge, search, bug), order, visibility, price? (currently no price per service, but pricing table separate), features array

**PROJECTS (projects + project_images)**
- slug unique, title, category, duration, cost, role, technologies array (or many-to-many), overview, features array, liveUrl nullable, art (svg or image), featured boolean, visibility, order, created_at
- Gallery: future

**BLOG (blog_posts, blog_categories, blog_tags, blog_post_tags)**
- All fields from blog_posts_real.json must be editable: id/slug unique, title, excerpt, content (HTML with WYSIWYG), featuredImage base name or media_id, featuredImageAlt, author, publishedDate, publishedDateISO, category (FK), tags (M2M), readingTime auto calc but editable, metaTitle, metaDescription, canonicalUrl, OG image, status draft/published, visibility, wordCount auto
- Preserve: TOC generation from h2/h3 id, internal link rewriting, related logic, share buttons

**TESTIMONIALS (testimonials)**
- Currently not in content.json — but CMS should support for future (client name, role/company, testimonial, image, rating, visibility, order)

**MEDIA LIBRARY (media)**
- id, filename sanitized, original filename, path, url, mime type, extension, size, width, height, alt text, title, uploaded_by, created_at
- Variants: -480, -720, -1114 WebP generated on upload (GD/Imagick)
- Prevent: php, exe, etc.

**CONTACT MESSAGES (contact_messages)**
- name, email, phone, subject, message, form type (contact/booking), agenda (for booking), preferred date, budget, IP, user_agent, created_at, read boolean, status new/read/replied/archived

**SEO (seo_settings + per-page fields)**
- Per page: meta title, meta description, canonical override, OG image, OG type, Twitter card, robots (index/noindex), JSON-LD extra
- Global: site name, domain, OG default, Twitter, JSON-LD Person, etc.

---

## 4. DATABASE DESIGN

### Proposed Normalized Schema (MySQL/MariaDB InnoDB, utf8mb4_unicode_ci)

**users**
- id INT UNSIGNED AUTO_INCREMENT PK
- email VARCHAR(255) UNIQUE NOT NULL, indexed
- password_hash VARCHAR(255) NOT NULL (bcrypt $2y$)
- name VARCHAR(255)
- role ENUM('admin','editor') DEFAULT 'admin'
- is_active TINYINT(1) DEFAULT 1
- last_login_at DATETIME NULL
- created_at DATETIME DEFAULT CURRENT_TIMESTAMP
- updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
- Indexes: email UNIQUE, is_active

**site_settings**
- id INT UNSIGNED AUTO_INCREMENT PK (single row id=1)
- site_name VARCHAR(255)
- site_title VARCHAR(255)
- site_domain VARCHAR(255)
- meta_description TEXT
- email VARCHAR(255)
- phone VARCHAR(50)
- phone_href VARCHAR(50)
- address TEXT
- location VARCHAR(255)
- created_at, updated_at

**navigation_items**
- id INT UNSIGNED AUTO_INCREMENT PK
- label VARCHAR(100) NOT NULL
- url VARCHAR(255) NOT NULL
- order_index INT DEFAULT 0
- is_visible TINYINT(1) DEFAULT 1
- parent_id INT UNSIGNED NULL FK → navigation_items.id ON DELETE SET NULL
- created_at, updated_at
- Indexes: order_index, is_visible, parent_id

**pages**
- id INT UNSIGNED AUTO_INCREMENT PK
- slug VARCHAR(255) UNIQUE NOT NULL (e.g., 'home', 'about', 'contact')
- title VARCHAR(255)
- meta_title VARCHAR(255)
- meta_description TEXT
- canonical_url VARCHAR(255) NULL
- og_image_id INT UNSIGNED NULL FK → media.id ON DELETE SET NULL
- is_visible TINYINT(1) DEFAULT 1
- created_at, updated_at
- Indexes: slug UNIQUE

**page_sections**
- id INT UNSIGNED AUTO_INCREMENT PK
- page_id INT UNSIGNED FK → pages.id ON DELETE CASCADE
- section_key VARCHAR(100) (e.g., 'hero', 'why_choose', 'stats')
- content_json JSON (flexible for different sections)
- order_index INT DEFAULT 0
- is_visible TINYINT(1) DEFAULT 1
- created_at, updated_at
- Indexes: page_id, section_key, order_index

**services**
- id INT UNSIGNED AUTO_INCREMENT PK
- slug VARCHAR(255) UNIQUE NOT NULL
- title VARCHAR(255) NOT NULL
- description TEXT
- icon_key VARCHAR(50) (e.g., 'palette')
- order_index INT DEFAULT 0
- is_visible TINYINT(1) DEFAULT 1
- created_at, updated_at
- Indexes: slug UNIQUE, is_visible, order_index

**projects**
- id INT UNSIGNED AUTO_INCREMENT PK
- slug VARCHAR(255) UNIQUE NOT NULL
- title VARCHAR(255) NOT NULL
- category VARCHAR(100)
- duration VARCHAR(50)
- cost VARCHAR(50)
- role VARCHAR(255)
- overview TEXT
- live_url VARCHAR(255) NULL
- art_media_id INT UNSIGNED NULL FK → media.id ON DELETE SET NULL (or art string for SVG)
- is_featured TINYINT(1) DEFAULT 0
- is_visible TINYINT(1) DEFAULT 1
- order_index INT DEFAULT 0
- created_at, updated_at
- Indexes: slug UNIQUE, category, is_visible, order_index, is_featured

**project_technologies** (many-to-many for filtering)
- id INT UNSIGNED AUTO_INCREMENT PK
- project_id INT UNSIGNED FK → projects.id ON DELETE CASCADE
- technology VARCHAR(100)
- Indexes: project_id, technology, UNIQUE(project_id, technology)

**project_features**
- id INT UNSIGNED AUTO_INCREMENT PK
- project_id INT UNSIGNED FK → projects.id ON DELETE CASCADE
- feature TEXT
- order_index INT DEFAULT 0
- Indexes: project_id

**blog_categories**
- id INT UNSIGNED AUTO_INCREMENT PK
- slug VARCHAR(255) UNIQUE NOT NULL
- name VARCHAR(100) NOT NULL UNIQUE
- description TEXT NULL
- created_at, updated_at
- Indexes: slug UNIQUE, name UNIQUE

**blog_tags**
- id INT UNSIGNED AUTO_INCREMENT PK
- slug VARCHAR(255) UNIQUE NOT NULL
- name VARCHAR(100) NOT NULL UNIQUE
- created_at
- Indexes: slug UNIQUE, name UNIQUE

**blog_posts**
- id VARCHAR(100) PK (or INT, but keep original id string e.g., slug) — recommend INT AUTO_INCREMENT + slug UNIQUE for flexibility, but preserve original id as slug
- Use INT UNSIGNED AUTO_INCREMENT PK + slug VARCHAR(255) UNIQUE
- Let's define:
- id INT UNSIGNED AUTO_INCREMENT PK
- original_id VARCHAR(100) UNIQUE (for migration)
- slug VARCHAR(255) UNIQUE NOT NULL
- title VARCHAR(255) NOT NULL
- excerpt TEXT
- content LONGTEXT (HTML)
- featured_image_id INT UNSIGNED NULL FK → media.id ON DELETE SET NULL (replaces base name)
- featured_image_alt VARCHAR(255)
- author VARCHAR(255) DEFAULT 'Mashzidul Tanun Borshon'
- published_date DATE
- published_date_iso DATETIME
- category_id INT UNSIGNED NULL FK → blog_categories.id ON DELETE SET NULL
- reading_time INT
- meta_title VARCHAR(255)
- meta_description TEXT
- canonical_url VARCHAR(255)
- og_image_id INT UNSIGNED NULL FK → media.id ON DELETE SET NULL
- status ENUM('draft','published') DEFAULT 'published'
- is_visible TINYINT(1) DEFAULT 1
- word_count INT
- created_at, updated_at
- Indexes: slug UNIQUE, category_id, status, published_date_iso DESC, is_visible

**blog_post_tags** (M2M)
- post_id INT UNSIGNED FK → blog_posts.id ON DELETE CASCADE
- tag_id INT UNSIGNED FK → blog_tags.id ON DELETE CASCADE
- PRIMARY KEY (post_id, tag_id)
- Indexes: tag_id

**media**
- id INT UNSIGNED AUTO_INCREMENT PK
- filename VARCHAR(255) NOT NULL (sanitized, e.g., website-speed-optimization-480.webp)
- original_filename VARCHAR(255)
- file_path VARCHAR(500) (relative to uploads/)
- file_url VARCHAR(500)
- mime_type VARCHAR(100)
- extension VARCHAR(20)
- file_size INT UNSIGNED (bytes)
- width INT UNSIGNED NULL
- height INT UNSIGNED NULL
- alt_text VARCHAR(255) NULL
- title VARCHAR(255) NULL
- uploaded_by INT UNSIGNED NULL FK → users.id ON DELETE SET NULL
- created_at, updated_at
- Indexes: extension, mime_type, uploaded_by, created_at

**contact_messages**
- id INT UNSIGNED AUTO_INCREMENT PK
- form_type ENUM('contact','booking') DEFAULT 'contact'
- name VARCHAR(255) NOT NULL
- email VARCHAR(255) NOT NULL
- phone VARCHAR(50) NULL
- subject VARCHAR(255) NULL
- message TEXT NOT NULL
- agenda JSON NULL (for booking)
- preferred_date DATE NULL
- budget VARCHAR(100) NULL
- ip_address VARCHAR(45) NULL
- user_agent TEXT NULL
- is_read TINYINT(1) DEFAULT 0
- status ENUM('new','read','replied','archived') DEFAULT 'new'
- created_at DATETIME DEFAULT CURRENT_TIMESTAMP
- updated_at
- Indexes: form_type, is_read, status, created_at DESC, email

**testimonials** (future)
- id INT UNSIGNED AUTO_INCREMENT PK
- client_name VARCHAR(255)
- role_company VARCHAR(255)
- testimonial TEXT
- image_id INT UNSIGNED NULL FK → media.id ON DELETE SET NULL
- rating TINYINT NULL (1-5)
- is_visible TINYINT(1) DEFAULT 1
- order_index INT DEFAULT 0
- created_at, updated_at
- Indexes: is_visible, order_index

**seo_settings** (global)
- id INT UNSIGNED AUTO_INCREMENT PK
- page_type VARCHAR(100) UNIQUE (e.g., 'global', 'home', 'blog')
- og_default_image_id INT UNSIGNED NULL FK → media.id ON DELETE SET NULL
- twitter_card_type VARCHAR(50) DEFAULT 'summary_large_image'
- robots_default VARCHAR(100) DEFAULT 'index, follow'
- json_ld_extra JSON NULL
- created_at, updated_at

**Relationships Summary:**
- blog_posts.category_id → blog_categories.id SET NULL on delete (keep post if category deleted)
- blog_post_tags post_id CASCADE, tag_id CASCADE (delete mapping if post/tag deleted)
- projects → media SET NULL (keep project if image deleted)
- media.uploaded_by → users SET NULL
- page_sections.page_id CASCADE
- project_technologies.project_id CASCADE
- project_features.project_id CASCADE
- navigation_items.parent_id SET NULL (keep child if parent deleted)
- All FKs InnoDB, timestamps, soft visibility via is_visible not hard delete for content, hard delete for mappings.

**Deletion Behavior:**
- Users: SET NULL for uploaded_by, keep content
- Categories/Tags: SET NULL or CASCADE for mappings, preserve posts
- Media: SET NULL for references, keep referencing records but image becomes missing (admin should prevent deletion if in use or warn)
- Pages: CASCADE sections
- Projects: CASCADE technologies/features

**Indexes:** As listed, plus composite for blog search (title, category), for sitemap order (published_date_iso DESC).

---

## 5. ADMIN AUTHENTICATION

**Design:**

- **Login:** `/admin/login` — email + password form, POST to same, rate limiting
- **Password Hashing:** `password_hash($password, PASSWORD_BCRYPT)` (or ARGON2ID if PHP 8.2+ available), `password_verify()` — never plaintext
- **Sessions:** PHP native sessions with secure settings:
  - `session.cookie_httponly = 1`
  - `session.cookie_secure = 1` (when HTTPS)
  - `session.cookie_samesite = Lax` or Strict
  - `session.use_strict_mode = 1`
  - `session.gc_maxlifetime = 3600` (1h idle)
  - Regenerate ID on login: `session_regenerate_id(true)`
  - Store user id, role, IP, user_agent fingerprint, login time
- **Authorization:** Middleware `requireAdmin()` checks `$_SESSION['user_id']` and role, redirects to /admin/login if not, returns 403 if role insufficient
- **Protected Routes:** All `/admin/*` except `/admin/login` require auth via include at top
- **CSRF:** Generate token per session `bin2hex(random_bytes(32))`, store in session, include as hidden input `<input type="hidden" name="csrf" value="">`, validate on POST with `hash_equals()`, rotate after use for sensitive actions
- **Brute-force Protection:**
  - Login attempts table or in-memory: `login_attempts` (ip, email, attempts, last_attempt)
  - Limit 5 attempts per 15 min per IP+email, exponential backoff, show captcha after 3 fails (simple math or hCaptcha)
  - Delay response 1-2 sec on fail (sleep)
  - Log failed attempts
- **Secure Cookies:** If using remember-me, use separate token table with selector+validator, hashed validator, expiry 30d, httpOnly, secure, sameSite
- **Input Validation:** Server-side validation for all inputs: email filter_var, length checks, slug regex `/^[a-z0-9-]+$/`, HTML purifier for content (allowlist h2,h3,p,ul,ol,li,a,strong,em, etc., strip script, iframe, etc.)
- **Output Escaping:** `htmlspecialchars($str, ENT_QUOTES, 'UTF-8')` for all output except trusted HTML content (blog content stored as sanitized HTML, but still purify on save)
- **Password Policy:** Min 8 chars, require upper/lower/number/symbol, check against common passwords list, force change on first login
- **Logout:** Destroy session, delete session cookie, clear remember token

---

## 6. BLOG MIGRATION

**Current Files:**
- `site_src/blog_posts_real.json` 4 posts, 60k size, full HTML content
- `assets/img/blog/` WebP variants 480,720,1114 + original
- Routes: clean `/{slug}/` and legacy `/blog/{slug}/`
- SEO: canonicalUrl, metaTitle, metaDescription, featuredImageAlt, publishedDateISO
- Internal links: rewritten via `rewrite_internal_links()` mapping

**Migration Plan:**

1. **Parse JSON:** Read blog_posts_real.json, for each post:
   - Preserve: id, slug, title, excerpt, content (full HTML, DO NOT shorten), featuredImage base name, featuredImageAlt, author, publishedDate, publishedDateISO, category, tags array, readingTime, metaTitle, metaDescription, canonicalUrl
   - Generate: wordCount = count words in content stripped of tags, status = published

2. **Categories:** Extract unique categories (Performance, Web Design, Development, Business) → insert into blog_categories (slug = strtolower hyphenated, name = original)

3. **Tags:** Extract unique tags across all posts (e.g., "website speed optimization", "SEO", etc.) → insert into blog_tags (slug = slugify, name = original)

4. **Media:** For each featuredImage base name:
   - Check `assets/img/blog/{base}.webp` and variants exist
   - Copy to new `uploads/blog/` or `uploads/media/` with same names, or generate new sanitized names
   - Insert into media table: filename, original_filename, path, url, mime image/webp, size, width/height (via getimagesize), alt_text = featuredImageAlt
   - Store media id for featured_image_id and og_image_id

5. **Posts:** Insert into blog_posts:
   - original_id = old id
   - slug = old slug (unique, keep exactly to preserve URLs)
   - title, excerpt, content (purify but keep all h2 id, p, ul, etc. — DO NOT rewrite content)
   - featured_image_id = media id
   - featured_image_alt
   - author
   - published_date = publishedDate
   - published_date_iso = publishedDateISO
   - category_id = lookup from categories
   - reading_time = old readingTime or calc
   - meta_title, meta_description, canonical_url = old canonicalUrl (preserve https://mashzidultanun.com/{slug}/)
   - status published, is_visible 1

6. **Post-Tags M2M:** For each post tags array, insert into blog_post_tags

7. **URL Preservation:**
   - Keep slugs identical: website-speed-optimization, benefits-of-responsive-web-design, wordpress-website-development-is-a-smart-choice, benefits-of-a-professional-business-website
   - Canonical URLs remain https://mashzidultanun.com/{slug}/ — same as before
   - Ensure .htaccess or PHP router serves both clean `/{slug}/` and legacy `/blog/{slug}/` with 301 redirect or canonical to clean (current build emits both but canonical → clean; new system should 301 legacy to clean for SEO, or keep both with canonical)
   - Recommend: 301 redirect `/blog/{slug}/` → `/{slug}/` to consolidate, but keep canonical same
   - Internal links in content already rewritten to clean URLs via `rewrite_internal_links()` — preserve that mapping in DB content, or run same rewrite on display

8. **Images:** Preserve src `/assets/img/blog/{base}.webp` or update to `/uploads/...` — better to keep `/assets/img/blog/` path for existing posts to avoid broken images, and new uploads go to `/uploads/`. Or migrate all to `/uploads/blog/` and update content HTML img src if any inline images (currently content has no inline blog images, only featured image outside content)

9. **SEO Preservation:**
   - Keep metaTitle, metaDescription, canonicalUrl, featuredImageAlt
   - Keep publishedDateISO for JSON-LD datePublished/dateModified
   - Keep readingTime for display
   - Keep category for badge and filtering
   - Keep tags for data-tags attribute and related scoring

10. **Sitemap:** Regenerate sitemap from DB: include all published posts with clean URLs, same as current sitemap (22 URLs)

11. **Verification:** After migration, compare old HTML vs new PHP rendered HTML for each post — ensure TOC, share buttons, related, prev/next still work

**Do NOT:** Shorten content, rewrite article, change slugs, lose dates, lose categories/tags, lose alt text, lose canonical, lose internal links

---

## 7. MEDIA SYSTEM

**Requirements:** Support existing WebP strategy, plus JPG/PNG

**Design:**

- **Upload Endpoint:** `/admin/media/upload` POST multipart/form-data, auth required, CSRF token
- **Validation:**
  - MIME: Check `finfo_file()` + `mime_content_type()` + extension allowlist: image/jpeg, image/png, image/webp, image/svg+xml (SVG only for project art, not blog — sanitize SVG)
  - Extension allowlist: jpg, jpeg, png, webp, svg (lowercase)
  - Size: Limit 5MB per file (configurable), `upload_max_filesize` 8M, `post_max_size` 10M
  - Dimensions: Use `getimagesize()` to get width/height, reject if >5000px or <10px
  - Filename: Sanitize via `preg_replace('/[^a-z0-9-_.]/', '', strtolower)` + uniqid prefix, prevent `.php`, `.phtml`, etc. — double extension check
  - Path traversal: No `../`, no null bytes, use basename
  - Executable: Reject if contains `<?php`, `<script` in file content for images, check magic bytes

- **Storage:**
  - Path: `public_html/uploads/{year}/{month}/` or `uploads/media/` — create if not exists 755
  - Filename: `{uniqid}-{sanitized-original}.{ext}` e.g., `65f3a-website-speed-optimization-480.webp`
  - For WebP variants: Generate on upload via GD (imagecreatefromjpeg/png/webp) or Imagick if available:
    - Original: Keep original + convert to WebP if not WebP
    - Variants: 480w, 720w, 1114w, 1536w (for featured) — preserve aspect ratio, quality 80-85
    - Store variants with suffix: `filename-480.webp`, etc.
  - Alt text: Input field in media library, required for blog featured

- **Database:** Insert into media table with all metadata

- **Orphaned Media:** Cron or admin tool to find media not referenced in blog_posts.featured_image_id, projects.art_media_id, pages.og_image_id, etc. — show list, allow delete

- **Delete:** Auth + CSRF, check if file in use (query references), if in use warn, else unlink files + variants + DB row

- **Security:**
  - Store outside public_html? But need public URL — so keep in public_html/uploads with .htaccess denying php execution: `php_flag engine off`, `AddType text/plain php phtml`, `Options -ExecCGI`
  - No direct upload to assets/img/blog — keep that read-only for migrated old images
  - Validate SVG via XML parser, strip script, event handlers

---

## 8. API / DATA FLOW

**Recommended: PHP Server-Rendered Pages (Hybrid)**

**Why not API + JS frontend:**
- Current site is SEO-heavy, needs server-rendered HTML for crawlers, no JS dependency for content
- Preserving existing frontend as much as possible means keeping same HTML structure, just replacing static data with DB queries
- API + JS would require rewriting all templates to JS, breaking SEO, requiring hydration

**Architecture:**

**Database → Backend (PHP) → Frontend (HTML)**

1. **Request:** Visitor requests `https://mashzidultanun.com/about/` → Apache .htaccess rewrites to `index.php?path=/about/` or front controller parses `$_SERVER['REQUEST_URI']`

2. **Router (index.php):**
   - Parse path, e.g., `/about/` → page slug `about`
   - Check if path matches blog post slug → query `blog_posts WHERE slug = ? AND status='published' AND is_visible=1`
   - Else check pages/services/projects etc.
   - Example:
     - `/` → home.php template, queries site_settings, hero, stats, services, projects, pricing, latest blog posts
     - `/about/` → about.php, queries site_settings, about paragraphs, skillGroups (from page_sections or separate tables)
     - `/services/` → services.php, queries services WHERE is_visible ORDER BY order_index
     - `/projects/` → projects.php, queries projects
     - `/projects/{slug}/` → project_detail.php, query project by slug
     - `/blog/` → blog_archive.php, query blog_posts published ORDER BY published_date_iso DESC
     - `/{slug}/` → check if slug in blog_posts, if yes render blog_post.php, else 404
     - `/contact/` → contact.php
     - etc.

3. **Backend:**
   - `includes/db.php` — PDO connection with prepared statements (prevent SQL injection)
   - `includes/functions.php` — `e()` = htmlspecialchars, `getSiteSettings()`, `getNavigation()`, `getBlogPosts($limit)`, `getRelatedPosts($currentId)`, `formatDateLong()`, `extractToc()`, `rewriteInternalLinks()`, `blogImageData()` now from media table
   - Queries use prepared statements, no string concatenation

4. **Frontend:**
   - Templates in `templates/` preserve current HTML structure exactly (copy from build.py output, replace `C['hero']['intro']` with `<?= e($settings['intro']) ?>`)
   - Header/footer same as now, but dynamic from DB
   - CSS/JS unchanged — `assets/css/styles.css`, `assets/js/main.js` same files, no modification to visual design
   - Images from `/assets/` + `/uploads/`

5. **Admin → DB:**
   - Admin forms POST to `/admin/{resource}/save` → validate, sanitize, insert/update via PDO prepared
   - Media upload → `includes/upload.php` → move_uploaded_file + generate variants + DB insert

**Data Flow Example — Blog Archive:**
```
DB: blog_posts + blog_categories + media
→ Backend: $posts = $db->query("SELECT p.*, c.name as category_name, m.file_url FROM blog_posts p LEFT JOIN blog_categories c ON p.category_id=c.id LEFT JOIN media m ON p.featured_image_id=m.id WHERE p.status='published' ORDER BY p.published_date_iso DESC")
→ Frontend: templates/blog/archive.php loops $posts, outputs same HTML as current blog_card() function, with data-title, data-category, data-tags for JS filters
```

**Data Flow Example — Contact Form:**
```
Visitor: fills form in contact.php (same HTML as now)
→ JS: validates, fetch POST to /api/contact (or /contact/ POST)
→ Backend: api/contact.php validates, sanitizes, inserts into contact_messages, sends email via PHPMailer or mail(), returns JSON {success:true}
→ Frontend: shows status message
→ Admin: /admin/messages lists contact_messages
```

---

## 9. CONTACT FORM

**Current Audit:**
- File: `site_src/build.py` contact() + `site_src/assets/js/main.js` line 197 FORM_ENDPOINT null, 208 form[data-form] submit handler
- Validation: JS checks required fields + email regex, shows .has-error, .form-status is-err/is-ok
- No backend: If FORM_ENDPOINT null, fallback to `window.location.href = mailto:mail@mashzidultanun.com?subject=...&body=...` with prefilled data
- Booking form similar but collects agenda checkboxes
- No DB, no email sending from server, no spam protection, no rate limiting, exposes email in HTML (mailto)
- Functional but not production for CMS — mailto depends on client email app, no tracking

**Production Design:**

**Visitor → Form → Backend Validation → DB → Success → Admin Notification → Admin Dashboard**

1. **Frontend (contact.php):**
   - Same HTML as now (preserve design), but form `action="/api/contact"` method POST, add CSRF hidden input (generated per session, even for public form — or use honeypot + time check)
   - Keep JS validation for UX, but also server validation

2. **Backend Validation (api/contact.php):**
   - Check CSRF if using session for public (or use double-submit cookie)
   - Validate: name required 2-100 chars, email required valid format filter_var, phone optional but if present regex, subject required, message required 10-5000 chars
   - Honeypot: hidden field `website` should be empty (bot trap)
   - Time check: form rendered time in session, reject if submitted <3 sec (bot)
   - Rate limiting: IP-based, max 5 submissions per hour, store in `contact_rate_limit` table or file
   - Sanitize: strip tags except allowed, htmlspecialchars for DB, but keep message as plain text

3. **Database:**
   - Insert into `contact_messages` with form_type contact, ip_address $_SERVER['REMOTE_ADDR'], user_agent, is_read 0, status new, created_at NOW()

4. **Success Response:**
   - Return JSON `{success:true, message:"Thank you! Your message has been sent."}` with 200
   - Frontend JS shows is-ok status, resets form
   - If JS disabled, PHP renders same page with success message (progressive enhancement)

5. **Admin Notification:**
   - Send email to admin (mail@mashzidultanun.com) via PHP `mail()` or PHPMailer with SMTP (cPanel email)
   - Email: From noreply@mashzidultanun.com, To admin, Subject "New contact message from {name}", Body with details + link to admin/messages
   - Do NOT expose SMTP creds in frontend JS — keep in config.php outside public_html
   - Optional: Also send auto-reply to visitor

6. **Admin Dashboard:**
   - `/admin/messages` lists contact_messages ORDER BY created_at DESC, filter by form_type, status, is_read, search
   - Show unread count badge
   - Mark as read, reply (mailto), archive, delete (with CSRF)

7. **Security:**
   - No secrets in JS — FORM_ENDPOINT is /api/contact same domain, no API key needed
   - Use prepared statements for DB insert
   - Escape output in admin list
   - Rate limiting + honeypot + time check to reduce spam
   - Optional: reCAPTCHA v3 or hCaptcha if spam heavy (but keep simple first)

---

## 10. SECURITY AUDIT

**Potential Issues in Converting to CMS:**

- **SQL Injection:** Risk if using string concatenation for queries — Mitigation: Use PDO prepared statements with bound params everywhere, no `$_GET`/`$_POST` directly in SQL

- **XSS (Cross-Site Scripting):**
  - Stored XSS via blog content HTML — Mitigation: Purify HTML on save via HTMLPurifier or allowlist (h2,h3,p,ul,ol,li,a,strong,em,blockquote,code,pre,img with src alt, etc.), strip script, iframe, on* attributes, javascript: URLs
  - Reflected XSS via search query, etc. — Mitigation: Escape all output with `e()` (htmlspecialchars)
  - Blog content is HTML — must be sanitized but allow safe tags

- **CSRF:**
  - Admin forms (edit site, delete post, upload) — Mitigation: CSRF token per session, hidden input, validate with hash_equals, SameSite cookie Lax

- **Session Hijacking:**
  - Mitigation: Secure cookie settings (httponly, secure, samesite), regenerate ID on login, bind session to IP/user_agent fingerprint (with tolerance), short gc_maxlifetime, destroy on logout

- **Authentication:**
  - Weak passwords, plaintext storage — Mitigation: bcrypt, password policy, no plaintext, limit login attempts

- **Authorization:**
  - Direct access to /admin/* without login — Mitigation: requireAdmin() check at top of every admin file, role check

- **File Uploads:**
  - Malicious php file upload, double extension (image.php.jpg), path traversal — Mitigation: Allowlist MIME + extension, check magic bytes, sanitize filename, store outside executable path, .htaccess deny php, no user-controlled path, getimagesize check

- **Path Traversal:**
  - `../../etc/passwd` in file param — Mitigation: Use basename, realpath check that file is inside uploads dir, no `..` in filename

- **Malicious Filenames:**
  - Null bytes, special chars — Mitigation: Sanitize, uniqid prefix, remove null bytes

- **Arbitrary File Execution:**
  - Uploaded php executed via direct URL — Mitigation: .htaccess `php_flag engine off`, store uploads with no execute permission, serve via PHP script that checks auth if needed, but public images need direct access — so deny php execution in uploads folder

- **Exposed Environment Variables:**
  - Currently none, but CMS will have config.php with DB creds — Mitigation: Store outside public_html (`../config.php`), or in public_html but with .htaccess deny, not in JS, not in repo (add to .gitignore)

- **API Abuse:**
  - Contact form spam, mass POST — Mitigation: Rate limiting IP, honeypot, time check, captcha optional, validate Content-Type

- **Spam:**
  - Contact form bots — Mitigation: Honeypot, time check, rate limiting, Akismet or simple keyword filter, admin moderation

- **Rate Limiting:**
  - Login brute force, contact spam — Mitigation: login_attempts table, IP-based limit, exponential backoff, 429 response

- **Database Credentials:**
  - Hardcoded in repo — Mitigation: config.php outside public_html, env file not committed, .gitignore

- **Error Disclosure:**
  - PHP errors showing stack trace, DB credentials — Mitigation: `display_errors=0` in production, log to file, custom error pages, try/catch with generic message

---

## 11. SEO PRESERVATION

**Must Preserve:**

- **Clean URLs:** Current `/{slug}/` for blog, `/about/`, `/services/`, etc. — New PHP router must support same URLs via .htaccess `RewriteRule ^(.*)$ index.php?path=$1 [L,QSA]` or front controller
- **Canonical URLs:** `<link rel="canonical" href="https://mashzidultanun.com/{path}">` — Keep same DOMAIN, same path, no trailing slash mismatch (current trailingSlash true)
- **Sitemap:** `sitemap.xml` with only clean URLs, sorted, monthly — Regenerate from DB, same format, same 22 URLs + future posts
- **Robots.txt:** `User-agent: * Allow: / Sitemap: https://mashzidultanun.com/sitemap.xml` — Keep identical
- **Metadata:** Per-page title, description from DB (preserve current metaTitle, metaDescription)
- **Open Graph:** og:site_name, og:type, og:title, og:description, og:url, og:image (1200x630 default or blog featured), width/height, locale en_US — Keep same
- **Twitter Cards:** summary_large_image, title, description, image — Keep same
- **JSON-LD:** Person with sameAs, WebSite, ProfessionalService, BreadcrumbList, ItemList, Blog, BlogPosting with headline, description, image, author, datePublished, dateModified, mainEntityOfPage, articleSection, keywords, wordCount, timeRequired, FAQPage, OfferCatalog, CreativeWork, Appointment — Keep same structure, generate from DB
- **Article Structured Data:** BlogPosting — Preserve
- **Breadcrumb Structured Data:** BreadcrumbList — Preserve
- **Internal Links:** Breadcrumb nav, related posts, prev/next, header/nav/footer, rewrite_internal_links mapping — Preserve, ensure new router doesn't break
- **Image Alt Text:** All images have alt, blog featuredImageAlt preserved in media.alt_text — Preserve
- **Legacy Blog Routes:** `/blog/{slug}/` currently emits with canonical → clean — New system should 301 redirect legacy to clean to preserve SEO juice and avoid duplicate, or keep both with canonical same as now (recommend 301)

**Additional SEO Checks:**
- Single H1 per page (audit.py checks) — Keep
- Heading hierarchy h2/h3 in blog content — Preserve TOC extraction
- Meta theme-color #060605 — Keep
- Favicon 32/64, apple-touch 180 — Keep
- OG image 1200x630 — Keep

---

## 12. ADMIN UI

**Proposed Structure:**

```
/admin
  /login (public, no auth)
  /dashboard (auth, stats: total posts, projects, messages unread, media count)
  /settings (site_settings + socials)
  /navigation (navigation_items CRUD, drag order)
  /pages (pages list, edit sections)
    /pages/home (hero, why, stats, etc. — page_sections)
    /pages/about
  /services (services CRUD)
  /projects (projects CRUD + technologies + features + gallery)
  /blog
    /blog/posts (blog_posts list, filter draft/published, search)
    /blog/posts/new
    /blog/posts/{id}/edit
    /blog/categories (blog_categories CRUD)
    /blog/tags (blog_tags CRUD)
  /media (media library grid, upload, delete, edit alt)
  /testimonials (testimonials CRUD)
  /messages (contact_messages list, view, mark read, reply, archive)
  /seo (seo_settings global + per-page)
  /users (users CRUD, only admin role can manage)
  /logout
```

**UI Recommendations:**

- **Clean, simple, not developer-focused:** Use same black+gold premium but admin version — or use lightweight admin CSS (e.g., custom, not Bootstrap heavy) — keep minimal, elegant
- **Layout:** Sidebar nav with icons (same ICONS), top bar with user + logout, main content area with cards, tables, forms
- **Forms:** Label + input, required markers, validation messages, save/cancel buttons, same .field style as frontend but admin-specific
- **Tables:** For lists (blog posts, messages) — columns: title, category, date, status, actions (edit/delete), pagination, search
- **WYSIWYG for blog content:** Use TinyMCE or Quill or Toast UI Editor — allow h2/h3 with id, p, ul, ol, a, strong, etc., with HTML source view, but sanitize on save
- **Media Library:** Grid with thumbnails, upload drag-drop, alt text edit modal, delete with confirmation
- **Dashboard:** Stats cards (4 featured projects, 10 services, 13 technologies, 3 pricing packages — dynamic counts), recent messages, recent posts, quick links
- **No overcrowding:** Keep primary focus on editing, not analytics

**Tech for Admin UI:**
- PHP templates (same header/footer but admin)
- Vanilla JS for drag order, image preview, etc. — no heavy framework
- Or use minimal CSS framework like Pico.css or custom admin.css that matches black+gold but lighter

---

## 13. DEPLOYMENT

**Complete cPanel Deployment Plan:**

1. **Create Database:**
   - cPanel → MySQL Databases → Create Database `borshon_portfolio` (or `mashzidul_portfolio`)
   - Note: cPanel prefixes with username, e.g., `borshonba_portfolio`

2. **Create Database User:**
   - MySQL Databases → Create User `borshon_admin` with strong password (20+ chars)
   - Add User to Database with ALL PRIVILEGES

3. **Import Schema:**
   - phpMyAdmin → Select database → Import → Upload `schema.sql` (generated from proposed schema)
   - Or via MySQL CLI: `mysql -u user -p database < schema.sql`
   - Verify tables created

4. **Configure Environment Variables:**
   - Create `config.php` outside public_html (e.g., `/home/username/config.php`) or in `../config/` with:
     ```php
     <?php
     return [
       'db_host' => 'localhost',
       'db_name' => 'borshonba_portfolio',
       'db_user' => 'borshonba_admin',
       'db_pass' => 'strong_password',
       'site_domain' => 'https://mashzidultanun.com',
       'admin_email' => 'mail@mashzidultanun.com',
       'secret_key' => bin2hex(random_bytes(32)),
       'smtp_host' => 'mail.mashzidultanun.com',
       'smtp_user' => 'noreply@mashzidultanun.com',
       'smtp_pass' => '...',
     ];
     ```
   - Set permissions 600, owner username

5. **Upload Application:**
   - FTP/File Manager → Upload all PHP files, templates, includes, assets, admin, uploads folder (empty with .htaccess)
   - Ensure `public_html/` is document root — if domain is addon, set document root to `public_html/mashzidultanun.com` or similar
   - Current static assets (css, js, img, fonts) remain in `public_html/assets/`

6. **Configure Document Root:**
   - cPanel → Domains → Manage → Document Root → Set to `public_html` (or `public_html/mashzidultanun.com`)
   - Ensure `index.php` is default DirectoryIndex (Apache usually includes)

7. **Configure Apache Rewrite Rules:**
   - Create/edit `public_html/.htaccess`:
     ```
     RewriteEngine On
     RewriteBase /
     # Deny access to config
     <Files "config.php">
       Require all denied
     </Files>
     # Protect uploads from php execution
     <Directory "uploads">
       php_flag engine off
       AddType text/plain php phtml php3 php4 php5
       Options -ExecCGI -Indexes
     </Directory>
     # Clean URLs: if file or dir exists, serve it; else route to index.php
     RewriteCond %{REQUEST_FILENAME} !-f
     RewriteCond %{REQUEST_FILENAME} !-d
     RewriteRule ^(.*)$ index.php?path=$1 [L,QSA]
     # Security headers (optional)
     <IfModule mod_headers.c>
       Header set X-Content-Type-Options "nosniff"
       Header set X-Frame-Options "SAMEORIGIN"
       Header set Referrer-Policy "strict-origin-when-cross-origin"
     </IfModule>
     ```
   - Enable mod_rewrite (usually enabled on cPanel)

8. **Configure PHP Version:**
   - cPanel → Select PHP Version → Choose 8.2 or 8.3 (recommended)
   - Enable extensions: pdo, pdo_mysql, gd (or imagick), mbstring, fileinfo, openssl, json, curl (for email)
   - Set options: upload_max_filesize 32M, post_max_size 32M, memory_limit 256M, max_execution_time 60, display_errors Off, log_errors On

9. **Configure Permissions:**
   - Folders 755, files 644
   - `uploads/` 755, writable by PHP (owner username, not 777)
   - `config.php` outside public_html 600

10. **Configure Uploads:**
    - Create `public_html/uploads/`, `uploads/blog/`, `uploads/projects/`, `uploads/media/` with .htaccess denying php
    - Test upload via admin

11. **Configure SSL:**
    - cPanel → SSL/TLS Status → AutoSSL or Let's Encrypt → Enable for domain + www
    - Force HTTPS via .htaccess or PHP redirect if not already

12. **Configure Production Settings:**
    - `display_errors = Off`, `error_reporting = E_ALL`, `log_errors = On`, error_log outside public_html
    - Set `site_domain` to https://mashzidultanun.com
    - Ensure canonical URLs use https

13. **Run Migration:**
    - Create `tools/migrate.php` that reads `site_src/blog_posts_real.json` + `content.json` + scans `assets/img/blog/` and inserts into DB per blog migration plan
    - Run via browser (protected) or CLI `php tools/migrate.php`
    - Verify counts: 4 blog posts, 4 categories, ~20 tags, 4 media for blog featured

14. **Create Admin Account:**
    - `tools/create_admin.php` CLI: prompts email, password, hashes, inserts into users
    - Or via phpMyAdmin manual insert with password_hash
    - Delete tool after use

15. **Test Frontend:**
    - Visit https://mashzidultanun.com/ → home loads, hero, stats, etc.
    - /about/, /services/, /projects/, /pricing/, /blog/, /contact/, /faq/, /booking/, /terms/, /privacy/
    - Blog clean URLs: /website-speed-optimization/ etc.
    - Legacy /blog/{slug}/ → should 301 to clean or render with canonical
    - Check assets load, fonts, images, CSS, JS, no console errors

16. **Test Admin:**
    - /admin/login → login with admin creds → dashboard
    - /admin/settings → edit site name, save, check frontend reflects
    - /admin/blog/posts → list, edit one post, save, check frontend
    - /admin/media → upload WebP, check variants generated, alt text
    - /admin/services → edit service, save

17. **Test Contact Form:**
    - Submit contact form with valid data → should insert into contact_messages + send email + show success
    - Check /admin/messages → new message appears, unread
    - Test validation: empty, invalid email, honeypot, rate limiting

18. **Test Blog:**
    - Create new draft post via admin, preview, publish, check frontend /blog/ + /{slug}/ + sitemap
    - Edit existing post, check related posts, TOC, share buttons still work
    - Delete post (soft hide), check 404

19. **Test Uploads:**
    - Upload JPG, PNG, WebP via media library, check sanitization, variants, alt text
    - Try upload php file → should reject
    - Try upload with ../ in filename → should sanitize

20. **Test SEO:**
    - View source of home, blog post: check canonical, OG, Twitter, JSON-LD
    - Check sitemap.xml includes new posts, only clean URLs
    - Check robots.txt
    - Use Google Rich Results Test for BlogPosting
    - Check single H1 per page, alt text, internal links

21. **Test Security:**
    - Try SQL injection in search ?q=' OR 1=1 — should be escaped
    - Try XSS in contact form <script>alert(1)</script> — should be escaped/sanitized
    - Try access /admin without login → redirect to login
    - Try CSRF without token → reject
    - Try upload php → reject
    - Check config.php not accessible via https://mashzidultanun.com/config.php → 403

22. **Production QA:**
    - Full audit.py equivalent for PHP rendered pages (check links, alt, H1)
    - Check responsive on mobile/tablet/desktop (no overflow)
    - Check animations, filters, search, share, FAQ, forms
    - Check performance (PageSpeed Insights)
    - Check SSL, HTTPS redirect, www vs non-www canonical
    - Backup DB + files

---

## 14. MIGRATION STRATEGY

**From CURRENT STATIC SITE → CMS DATABASE → BACKEND → DYNAMIC FRONTEND → CPANEL PRODUCTION**

**Phase 0: Preserve Current (Now)**
- Current static site remains live on Vercel/GitHub Pages
- No modifications to frontend design
- All work in separate branch `feature/cms-cpanel` or separate repo `mashzidul-portfolio-cms`

**Phase 1: Audit (This Document)**
- Complete audit, no code changes — DONE

**Phase 2: Database & Schema (Milestone 1)**
- Create schema.sql from proposed design
- Set up local MySQL (XAMPP or Docker)
- No frontend changes

**Phase 3: Migration Scripts (Milestone 2)**
- Build `tools/migrate.php` to import blog_posts_real.json, content.json, media
- Test migration locally, verify data integrity, preserve URLs
- No frontend changes yet

**Phase 4: Backend Core (Milestone 3)**
- Develop `includes/db.php`, `config.php`, `functions.php`, `auth.php`
- Develop front controller `index.php` + router + templates that mirror current HTML but with PHP variables
- Keep CSS/JS/assets identical
- Test locally with PHP built-in server `php -S localhost:8000 -t public_html`
- Compare old static HTML vs new PHP HTML (diff) — should be nearly identical except dynamic data

**Phase 5: Admin Dashboard (Milestone 4)**
- Build /admin/login, dashboard, settings, navigation, services, projects, blog CRUD, media, messages, SEO, users
- Use same black+gold design but admin version
- Test CRUD operations

**Phase 6: Contact Form & Media (Milestone 5)**
- Implement api/contact.php with validation, DB, email, rate limiting
- Implement media upload with variants, security
- Test forms, uploads

**Phase 7: SEO & Security Hardening (Milestone 6)**
- Implement sitemap.php dynamic, robots.txt dynamic or static, JSON-LD generation, canonical logic, 301 legacy
- Security audit: prepared statements, XSS, CSRF, auth, upload, etc.
- Run audit.py equivalent

**Phase 8: Staging Deployment (Milestone 7)**
- Deploy to cPanel staging subdomain `staging.mashzidultanun.com` or `cms.mashzidultanun.com`
- Create DB, import schema, run migration, create admin, test full flow
- Client review on staging

**Phase 9: Production Deployment (Milestone 8)**
- Backup current static site
- Deploy to production `mashzidultanun.com` public_html (after approval)
- Switch DNS if needed, enable SSL, test, QA

**Branch Strategy:**
- `main` = current static site (Vercel)
- `feature/cms-cpanel` = CMS development
- `staging` = staging deployment
- Merge to `main` only after production approval, or keep separate repos: `mashzidul-portfolio` (static) and `mashzidul-portfolio-cms` (new)

**Current Site Usable During Development:**
- Yes, keep Vercel static live while developing CMS locally + staging
- No downtime until final cutover

---

## 15. DO NOT MODIFY YET

**This audit has NOT modified:**
- No files edited, deleted, redesigned
- No routes changed
- No blog content changed
- No dependencies installed
- No database created
- No backend code created

**Next step:** Await approval of this architecture before implementation.

---

## FINAL OUTPUT REQUIRED (Summary)

### 1. Current Architecture
- **Type:** 100% static frontend, Python build-time static generation, zero-dependency vanilla JS, self-hosted fonts, WebP images, no backend, no DB, no API
- **Build:** Python script reads JSON → generates 24 HTML pages (16 other + 8 blog) to repo root, copies assets, generates sitemap.xml + robots.txt
- **Server:** Node zero-dep static server for dev only, Vercel serves static with trailingSlash true and caching headers
- **Routing:** Clean URLs via directory index.html, legacy blog routes with canonical to clean

### 2. Current Backend Status
- **None** — FORM_ENDPOINT null, mailto fallback, no API, no serverless, no PHP

### 3. Current Database Status
- **None** — flat JSON files content.json + blog_posts_real.json

### 4. Current API Status
- **None** — no endpoints, no external APIs in code

### 5. Current Form Handling
- **Client-side only:** JS validation regex, required check, status messages, mailto fallback with prefilled subject/body, no storage, no email sending, no spam protection, no rate limiting

### 6. Current Blog Architecture
- **Scalable flat-file:** 4 posts in blog_posts_real.json with full HTML content, id/slug/title/excerpt/content/featuredImage/author/date/category/tags/readingTime/meta/canonical, responsive WebP variants, clean + legacy routes, single render_blog_post() template, related scoring (category+tags+recency), TOC extraction, share buttons, search + category filters via data-attributes, internal link rewriting, sitemap only clean

### 7. Current SEO Architecture
- **Comprehensive:** Canonical per page, meta title/description, OG site_name/type/title/description/url/image/width/height/locale, Twitter card summary_large_image, theme-color, favicon 32/64/apple-touch 180/og 1200x630, JSON-LD Person/WebSite/ProfessionalService/BreadcrumbList/ItemList/Blog/BlogPosting/FAQPage/OfferCatalog/CreativeWork/Appointment, sitemap 22 clean URLs, robots Allow + sitemap, single H1, alt text, breadcrumb, related/prev/next internal linking, OG image per blog, legacy canonical to clean

### 8. Recommended cPanel Architecture
- **PHP 8.2+ + MySQL/MariaDB + Apache mod_rewrite, server-rendered, no Node, no Python runtime, no framework or minimal, front controller index.php, .htaccess clean URLs, uploads with php_flag off, config outside public_html**

### 9. Recommended Backend Technology
- **PHP 8.2+ (PDO, GD/Imagick, mbstring, fileinfo, openssl), MySQL 8/MariaDB 10.6+, Apache 2.4+, vanilla PHP MVC-lite (no Laravel heavy), optional PHPMailer for email, HTMLPurifier for blog content, no npm deps in production**

### 10. Database Schema Proposal
- **15 tables:** users, site_settings, navigation_items, pages, page_sections, services, projects, project_technologies, project_features, blog_categories, blog_tags, blog_posts, blog_post_tags, media, contact_messages, testimonials, seo_settings (see Section 4 for full schema with PK/FK/indexes/unique/relationships/deletion)

### 11. Admin Dashboard Architecture
- **Routes:** /admin/login, /dashboard, /settings, /navigation, /pages, /services, /projects, /blog/posts|/categories|/tags, /media, /testimonials, /messages, /seo, /users, /logout
- **UI:** Clean simple black+gold admin, sidebar nav, top bar, cards/tables/forms, WYSIWYG for blog (TinyMCE/Quill), media grid drag-drop, dashboard stats, auth required, CSRF, vanilla JS

### 12. API Architecture
- **Server-rendered primary:** DB → PHP (PDO prepared) → HTML templates (preserve current design)
- **API only for forms:** /api/contact POST JSON → validation → DB → email → JSON response
- **No SPA:** Keep SEO, no JS hydration, same CSS/JS assets

### 13. Security Architecture
- **SQLi:** PDO prepared
- **XSS:** htmlspecialchars for output, HTMLPurifier for blog content allowlist
- **CSRF:** Token per session, hidden input, hash_equals, SameSite Lax
- **Session:** httponly, secure, samesite, strict_mode, regenerate ID, fingerprint, short lifetime
- **Auth:** bcrypt, rate limiting login_attempts, brute-force protection, password policy
- **Uploads:** MIME + extension allowlist, magic bytes, getimagesize, sanitize filename, uniqid, path traversal check, .htaccess php_flag off, no executable, SVG sanitize
- **Config:** Outside public_html, 600 perms, .gitignore, display_errors Off, log_errors On

### 14. Content Migration Plan
- **Source:** content.json (site, hero, why, about, skillGroups, services, stats, projects, pricing, FAQ, contact, terms, privacy, booking) + blog_posts_real.json
- **Steps:** Parse JSON → insert site_settings, navigation_items, pages/page_sections, services, projects + technologies + features, media for existing images, preserve slugs/URLs/titles/content/dates/categories/tags/alt/canonical/internal links, generate sitemap

### 15. Blog Migration Plan
- **Preserve:** All 4 posts full HTML content (DO NOT shorten), slugs identical, dates, categories, tags, featuredImage base → media id with variants, featuredImageAlt, author, readingTime, metaTitle, metaDescription, canonicalUrl, internal links rewritten, TOC, related, wordCount
- **Process:** Categories → blog_categories, Tags → blog_tags, Media → media, Posts → blog_posts, M2M → blog_post_tags, 301 legacy /blog/{slug}/ → /{slug}/ or keep canonical, verify HTML diff

### 16. Media Migration Plan
- **Existing:** assets/img/blog WebP variants 480/720/1114/original, brand, portrait, projects SVG
- **New:** uploads/{year}/{month}/ with sanitized uniqid names, generate variants 480/720/1114/1536 WebP via GD/Imagick, store metadata in media table, alt text required, .htaccess deny php, orphan check, keep old assets path for migrated posts to avoid broken images

### 17. Deployment Plan
- **22 steps:** Create DB, user, import schema, config env, upload app, document root, .htaccess rewrite, PHP version 8.2+, perms 755/644, uploads, SSL, production settings, run migration, create admin, test frontend, admin, contact, blog, uploads, SEO, security, QA (see Section 13)

### 18. Development Milestones
- **M0 Audit (Done)**
- **M1 Schema (1-2 days)**
- **M2 Migration Scripts (2-3 days)**
- **M3 Backend Core + Router + Templates (5-7 days)**
- **M4 Admin Dashboard CRUD (7-10 days)**
- **M5 Contact + Media (3-4 days)**
- **M6 SEO + Security (2-3 days)**
- **M7 Staging Deploy + Client Review (2 days)**
- **M8 Production Deploy + QA (2 days)**
- **Total Estimated: 24-31 days solo, 15-20 days with focus**

### 19. Risks/Problems Discovered
- **Current Vercel buildCommand null:** Relies on committed HTML, not CI build — need to ensure PHP build replaces Python build, no Vercel dependency in cPanel
- **No backend currently:** All forms mailto — must build from scratch, need email SMTP config
- **Python build only:** Team must switch to PHP, Python not available on standard cPanel — need to rewrite build logic in PHP
- **Image variants manual:** Currently manual WebP variants, need automated generation in PHP
- **No auth:** Must build secure auth from scratch
- **Flat JSON to relational:** Need careful migration to preserve URLs, SEO, related logic
- **No rate limiting/spam protection:** Contact form will be abused without honeypot/rate limiting/captcha
- **Config exposure risk:** DB creds must not be in repo
- **.htaccess complexity:** Clean URLs for both pages and blog slugs need careful regex to avoid conflict with assets
- **Legacy blog routes:** Must decide 301 vs canonical — 301 better for SEO consolidation
- **No tests:** audit.py only checks static HTML, need new audit for PHP rendered

### 20. Files That Will Need Modification
- **Will be REPLACED (not modified, but new PHP versions):**
  - `index.php` (new front controller, replaces static index.html generation)
  - `site_src/build.py` → replaced by PHP templates + DB (keep for reference, but not used in production)
  - `server.js` → not needed on cPanel, but keep for local dev
  - All HTML files in repo root (about/index.html, etc.) → will be dynamically generated by PHP, not static — static files will be removed from repo root after CMS, only PHP templates remain
- **Will be EXTENDED:**
  - `assets/css/styles.css` — keep as is, no redesign, but may add admin.css separate
  - `assets/js/main.js` — keep as is, but FORM_ENDPOINT will point to /api/contact
  - `vercel.json` — not needed on cPanel, but keep for Vercel if still used for staging
  - `.htaccess` — new file for Apache rewrites (currently doesn't exist)

### 21. Files That Should Remain Untouched (Design Source of Truth)
- **Visual design:** `site_src/assets/css/styles.css` (source) and `assets/css/styles.css` (built) — DO NOT redesign, keep black+gold premium, typography, spacing, animations, responsive
- **Frontend JS:** `site_src/assets/js/main.js` logic for filters, search, reveal, FAQ, share — keep, only FORM_ENDPOINT change
- **Brand assets:** `assets/img/brand/*`, `assets/img/portrait/*`, `assets/fonts/*` — keep as is, no recolor/redesign
- **Blog content:** `site_src/blog_posts_real.json` content HTML — DO NOT shorten/rewrite, preserve for migration
- **Project art:** `assets/img/projects/*.svg` — keep
- **Source assets:** `source-assets/*` — reference, keep
- **Docs:** `docs/IMPLEMENTATION_PLAN.md` — keep for history

### 22. Estimated Implementation Complexity by Milestone
- **M1 Schema:** Low — 1-2 days, straightforward DDL
- **M2 Migration:** Medium — 2-3 days, need careful parsing, media handling, URL preservation
- **M3 Backend Core:** High — 5-7 days, router, front controller, templates mirroring current HTML, DB abstraction, SEO preservation
- **M4 Admin Dashboard:** Very High — 7-10 days, auth, CRUD for 10+ resources, WYSIWYG, media library, validation, CSRF, UI
- **M5 Contact/Media:** Medium — 3-4 days, upload security, variants, email, rate limiting
- **M6 SEO/Security:** Medium-High — 2-3 days, sitemap dynamic, JSON-LD, .htaccess, hardening, audit
- **M7 Staging:** Low-Medium — 2 days, cPanel setup, import, test
- **M8 Production:** Low-Medium — 2 days, backup, deploy, QA, SSL

**Overall Complexity:** High — Full-stack CMS from scratch, but feasible with PHP+MySQL on cPanel, no heavy framework, preserve frontend exactly.

---

## APPENDIX: Current File List (Relevant)

- `site_src/build.py` (1216 lines, Python static generator)
- `site_src/content.json` (site settings, 10 services, 4 projects, 3 pricing, 6 FAQ, etc.)
- `site_src/blog_posts_real.json` (4 full articles, 60k)
- `site_src/assets/css/styles.css` (629 lines, source)
- `site_src/assets/js/main.js` (287 lines, vanilla)
- `assets/` (built css, js, img, fonts)
- `server.js` (74 lines, zero-dep static server)
- `vercel.json` (static config, trailingSlash true, caching headers)
- `package.json` (no deps, scripts build/start/dev/audit/check)
- `tools/audit.py` (55 lines, HTML QC)
- `sitemap.xml` (22 clean URLs)
- `robots.txt` (Allow + sitemap)
- 24 HTML pages (16 other + 8 blog) — repo root is web root

---

**END OF AUDIT — Awaiting approval before implementation.**
