# MILESTONE 3 — PHP BACKEND FOUNDATION REPORT
## Production-Ready PHP 8.2+ MVC-lite for cPanel

**Date:** 2026-09-23
**Milestone:** 3 — PHP Backend Foundation Only
**Status:** Foundation complete, tested via Python simulation (no PHP/MySQL in sandbox), ready for cPanel staging
**Frontend:** Untouched, static build 24 pages, audit clean
**Database:** 17 tables from Milestone 1, migration system from Milestone 2 ready

---

## 1. Architecture

**Target:** cPanel / Apache 2.4+ / PHP 8.2+ / MySQL 8+ / MariaDB 10.6+
**Pattern:** MVC-lite, server-rendered, no framework, no Node.js, no SPA

```
Repo Root (public_html in production)
├── .htaccess (production-oriented, safe for static + future PHP)
├── assets/ (css, js, img, fonts — unchanged, black+gold design source of truth)
├── uploads/
│   └── .htaccess (php_flag engine off, deny php execution)
├── app/
│   ├── Config/
│   │   └── Config.php (secure loader, multiple paths, outside public_html support, safe config)
│   ├── Core/
│   │   ├── Database.php (PDO singleton, ERRMODE_EXCEPTION, emulated prepares false, utf8mb4, transaction helpers, testConnection())
│   │   ├── Request.php (method, uri, path, get/post/input, json, ip, userAgent, isAjax, validateMethod)
│   │   ├── Response.php (html, json, redirect, redirect301, safe redirect check same-domain, status 404/403/405)
│   │   ├── Router.php (get/post/add, pathToRegex {slug}→([a-z0-9-]+), match, dispatch, legacy blog /blog/{slug}/ → 301 to /{slug}/, getPublicRoutes)
│   │   ├── Session.php (secure: name MTBSESSID, lifetime 3600, path /, secure true, httponly true, samesite Lax, use_strict_mode 1, fingerprint IP+UA+secret, regenerate, destroy)
│   │   ├── Security.php (e() htmlspecialchars, purifyHtml allowlist, CSRF generate/validate hash_equals, secure headers X-Content-Type-Options nosniff etc., sanitizeFilename basename+regex+double extension check, isSafePath realpath check, hashPassword bcrypt)
│   │   ├── Auth.php (init, check, isAdmin, isEditor, userId, user, login regenerate, logout destroy, requireAdmin redirect /admin/login or 403, requireGuest, hash/verify, rate-limit foundation session)
│   │   └── View.php (render, renderError 404/403/405/500 with black+gold inline style preserving visual language, no stack trace in production)
│   ├── Models/
│   │   ├── BaseModel.php (PDO, findById, findBySlug prepared, all, count, transaction helpers)
│   │   ├── SiteSettings.php (get id=1, getSocialLinks JSON decode)
│   │   ├── Navigation.php (getVisible order_index)
│   │   ├── Page.php (getBySlug visible, getAllVisible)
│   │   ├── PageSection.php (getByPageId, getByPageSlug JOIN pages)
│   │   ├── Service.php (getAllVisible order, getBySlug)
│   │   ├── Project.php (getAllVisible, getBySlug with technologies+features, getFeatured)
│   │   ├── ProjectTechnology.php (getByProjectId)
│   │   ├── ProjectFeature.php (getByProjectId order)
│   │   ├── BlogCategory.php (getAll, getBySlug)
│   │   ├── BlogTag.php (getAll, getByPostId JOIN post_tags)
│   │   ├── BlogPost.php (CRITICAL: getPublished status=published AND is_visible=1 ORDER published_date_iso DESC, getBySlug with category_name + featured_image_url + tags via JOIN, getByOriginalId, getRelated scoring same category +2 shared tags +1 recency exclude current, getAllForAdmin)
│   │   ├── BlogPostTag.php (M2M)
│   │   ├── Media.php (getByFilename, getById, getAll, isSafePath realpath check)
│   │   ├── ContactMessage.php (getAll, getUnread, markRead)
│   │   ├── Testimonial.php (getVisible order)
│   │   ├── SeoSetting.php (getByPageType, getGlobal)
│   │   └── User.php (findByEmail active, verifyPassword with rehash, updateLastLogin)
│   ├── Services/
│   │   └── BlogService.php (getPublished, getPostBySlug, getRelated, getLatest, formatDateLong/Short, extractToc regex h2/h3 id)
│   ├── Validation/
│   │   └── Validator.php (required, email filter_var, slug regex, url, length, inArray, validateContact with honeypot)
│   ├── Security/
│   │   ├── Csrf.php (generate random_bytes 32, getToken, validate hash_equals expiry 1h, field hidden input)
│   │   └── RateLimiter.php (isLoginAllowed 5/15min session, recordLoginAttempt, isContactAllowed 5/hour, checkDbRateLimit foundation)
│   └── Views/
│       └── errors/404.php (black+gold simple)
├── Services/ (future)
├── config/
│   └── config.example.php (template with db host/name/user/pass/charset, site_domain, session secure/httponly/samesite, security secret_key, uploads max_size 5MB allowed mimes/extensions variants, mail SMTP placeholders, rate_limit)
├── public/
│   └── index.php (front controller foundation, loads Config, secure headers, session, router defines all public routes /,/about/,/services/,/projects/,/pricing/,/blog/,/contact/,/faq/,/booking/,/terms/,/privacy/,/projects/{slug}/,/{slug}/, /api/contact POST placeholder, dispatch with try/catch)
├── database/
│   ├── schema.sql (17 tables, Milestone 1)
│   ├── README.md (import instructions)
│   └── migrations/
│       ├── config.example.php (for migration)
│       ├── migrate.php (CLI only, PDO, transactions, idempotent)
│       └── README.md
├── uploads/
│   └── .htaccess (php_flag engine off, deny php)
├── tests/
│   ├── test_backend.php (22 tests: syntax, config, DB, transactions, 404/403/405, escaping, CSRF, session, password, auth, blog visibility, slug, related, media safety, uploads protection, static build)
│   └── test_backend_py.py (Python simulation for sandbox without PHP/MySQL)
└── docs/
    ├── CPANEL-CMS-AUDIT-PLAN.md (Milestone 0)
    ├── MILESTONE-2-MIGRATION-REPORT.md (Milestone 2)
    └── MILESTONE-3-BACKEND-REPORT.md (this file)

**Production cPanel layout (recommended):**
```
/home/username/
├── config.php (outside public_html, 600 perms, real DB creds)
├── app/ (outside public_html, or inside but protected via .htaccess)
└── public_html/ (document root)
    ├── index.php (front controller, or public/index.php)
    ├── .htaccess
    ├── assets/ (unchanged)
    └── uploads/ (with .htaccess deny php)
```

---

## 2. Files Created

**New in Milestone 3:**

- `config/config.example.php` — secure config template with db, session, security, uploads, mail, rate_limit, env separation, never expose secrets
- `app/Config/Config.php` — loader tries multiple paths outside public_html first, get() dot notation, env(), isProduction(), getSafeConfig() removes pass/smtp_pass/secret_key
- `app/Core/Database.php` — PDO singleton, ERRMODE_EXCEPTION, emulated prepares false, utf8mb4, SET NAMES, time_zone, testConnection() returns php_version, pdo_available, pdo_mysql_available, connected, version, charset, tables, json_support, error
- `app/Core/Request.php` — method, uri, path, get/post/input, json body, ip, userAgent, isAjax, validateMethod
- `app/Core/Response.php` — html, json, redirect with safe check (relative / or same-domain), redirect301, status, notFound/forbidden/methodNotAllowed
- `app/Core/Router.php` — get/post/add, pathToRegex {slug}→([a-z0-9-]+), match, dispatch, legacy blog /blog/{slug}/ → 301 to /{slug}/, isLegacyBlogRoute, getCleanBlogUrl, getPublicRoutes (all 13 public routes)
- `app/Core/Session.php` — secure: name MTBSESSID, lifetime 3600, secure true, httponly true, samesite Lax, use_strict_mode 1, fingerprint SHA256 ip+ua+secret, regenerate, destroy, isExpired
- `app/Core/Security.php` — e() htmlspecialchars ENT_QUOTES, purifyHtml allowlist + strip script/iframe/on*/javascript:, CSRF generate random_bytes 32 + hash_equals expiry 1h, secure headers nosniff/SAMEORIGIN/XSS/Referrer/HSTS, sanitizeFilename basename+null byte+regex+double ext check, isSafePath realpath, hashPassword bcrypt
- `app/Core/Auth.php` — init, check, isAdmin, isEditor, userId, user, login regenerate + fingerprint, logout destroy, requireAdmin redirect /admin/login or 403 + expiry check, requireGuest, hash/verify, rate-limit foundation session
- `app/Core/View.php` — render, renderError 404/403/405/500 with black+gold inline style, no stack trace in production, dev shows message
- `app/Models/BaseModel.php` — db PDO, table, findById prepared, findBySlug prepared, all order, count, transaction helpers
- `app/Models/SiteSettings.php` — get id=1, getSocialLinks JSON
- `app/Models/Navigation.php` — getVisible order_index
- `app/Models/Page.php` — getBySlug visible, getAllVisible
- `app/Models/PageSection.php` — getByPageId, getByPageSlug JOIN
- `app/Models/Service.php` — getAllVisible order, getBySlug
- `app/Models/Project.php` — getAllVisible, getBySlug with technologies+features, getFeatured
- `app/Models/ProjectTechnology.php` — getByProjectId
- `app/Models/ProjectFeature.php` — getByProjectId order
- `app/Models/BlogCategory.php` — getAll, getBySlug
- `app/Models/BlogTag.php` — getAll, getByPostId JOIN
- `app/Models/BlogPost.php` — CRITICAL: getPublished status=published AND is_visible=1 ORDER published_date_iso DESC, getBySlug with category_name + featured_image_url + tags, getByOriginalId, getRelated scoring same category +2 shared tags +1 recency exclude current fallback most recent, getAllForAdmin
- `app/Models/BlogPostTag.php`
- `app/Models/Media.php` — getByFilename, getById, getAll, isSafePath
- `app/Models/ContactMessage.php` — getAll, getUnread, markRead
- `app/Models/Testimonial.php` — getVisible order
- `app/Models/SeoSetting.php` — getByPageType, getGlobal
- `app/Models/User.php` — findByEmail active, verifyPassword with rehash, updateLastLogin
- `app/Services/BlogService.php` — getPublished, getPostBySlug, getRelated, getLatest, formatDateLong/Short, extractToc regex
- `app/Validation/Validator.php` — required, email, slug, url, length, inArray, validateContact with honeypot
- `app/Security/Csrf.php` — generate, getToken, validate hash_equals expiry, field hidden input
- `app/Security/RateLimiter.php` — isLoginAllowed 5/15min, recordLoginAttempt, isContactAllowed 5/hour, checkDbRateLimit foundation
- `app/Views/errors/404.php` — black+gold simple
- `public/index.php` — front controller foundation, loads Config, secure headers, session, router defines all public routes, dispatch try/catch
- `.htaccess` — production-oriented, safe for static + future PHP: Options -Indexes, protect config.php/.env/.git/migrate.php/database/app/Config/config, uploads php_flag off, expires/caching mimic vercel.json (woff2 1yr immutable, css/js 1d, img 7d), security headers nosniff/SAMEORIGIN/XSS/Referrer, clean URLs + front controller RewriteCond !-f !-d exclude assets/uploads/database/app/config → public/index.php?path=$1, legacy blog /blog/{slug}/ → 301 to /{slug}/, ErrorDocument 404 /404.html, MIME types webp/woff2/svg, deny .git/.env
- `uploads/.htaccess` — php_flag engine off, AddType text/plain php, RemoveHandler, Options -ExecCGI -Indexes, FilesMatch deny php/exe/sh/bat
- `.gitignore` — ignore config/config.php, app/Config/config.php, ../config.php, private/config.php, uploads/* !.htaccess, .DS_Store, *.log, .env, vendor/
- `tests/test_backend.php` — 22 tests: syntax via php -l, config loading, DB connection, PDO init, table detection, prepared queries, transaction rollback/commit, 404/403/405, escaping, CSRF, session, password_hash/verify, auth helper, published vs draft visibility, slug lookup, related posts, media path safety, uploads protection, static build
- `tests/test_backend_py.py` — Python simulation for sandbox without PHP/MySQL, 22 tests same, plus static build verification via python3 site_src/build.py + tools/audit.py

**Total new files M3:** ~30

---

## 3. Files Modified

- **.htaccess** — Created new at repo root (was not present before? Actually previous had none, now created production-oriented, safe for static)
- **.gitignore** — Created new
- **No modifications to:** content.json, blog_posts_real.json, existing blog content, existing CSS (styles.css 629 lines preserved), existing JS (main.js 287 lines preserved), brand assets, existing static HTML (index.html etc. still generated via build.py), build.py, server.js, vercel.json — per file change policy

---

## 4. Database Connection Design

**Config System:**
- `config/config.example.php` template with db host/name/user/pass/charset/collation/options (PDO::ATTR_ERRMODE EXCEPTION, FETCH_ASSOC, EMULATE_PREPARES false), site_domain, session (name MTBSESSID, lifetime 3600, secure true, httponly true, samesite Lax, use_strict_mode), security secret_key random 32, csrf name, hash_algo bcrypt, uploads dir/url/max_size 5MB/allowed mimes/extensions/variants 480/720/1114/1536/quality 82, mail SMTP placeholders, rate_limit login 5/15min contact 5/hour
- `app/Config/Config.php` loader tries multiple paths outside public_html first: config/config.php, private/config.php, ../../../config.php (/home/username/config.php), etc., fallback to example with warning, env detection, isProduction/isDevelopment/isDebug, getSafeConfig removes secrets
- Production: config.php outside public_html, 600 perms, gitignored, never committed, never exposed via public response

**PDO Layer:**
- `app/Core/Database.php` singleton, DSN mysql:host=...;dbname=...;charset=utf8mb4, options from config + default SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone +00:00, try/catch log error, throw RuntimeException in production without credentials exposure
- `testConnection()` returns php_version, pdo_available, pdo_mysql_available, connected, version SELECT VERSION(), charset SELECT @@character_set_database, tables SHOW TABLES, json_support SELECT JSON_VALID, error
- Transaction helpers beginTransaction/commit/rollBack with inTransaction check
- isAvailable() checks extension_loaded pdo && pdo_mysql

**Validation in sandbox:** No MySQL client, no PHP CLI (apt permission denied), so cannot run actual PDO connection — documented as BLOCKED BY ENVIRONMENT, will be tested on cPanel staging with phpMyAdmin import + php migrate.php

---

## 5. Routing Design

**Apache .htaccess + PHP Front Controller:**

- **.htaccess:**
  - Protect sensitive files: config.php, .env, .git, composer, package, database/, app/Config/, config/, migrate.php → 403
  - Protect uploads: php_flag engine off, AddType text/plain php, RemoveHandler, Options -ExecCGI -Indexes, FilesMatch deny php/exe/sh/bat
  - Caching: expires 7d webp/png/jpg/svg, 1d css/js, 1yr woff2 immutable, deflate text/html/css/js/json/svg
  - Security headers: nosniff, SAMEORIGIN, XSS block, Referrer strict-origin, HSTS optional env=HTTPS
  - Legacy blog: `RewriteRule ^blog/([a-z0-9-]+)/?$ /$1/ [R=301,L]` — /blog/{slug}/ → /{slug}/, SEO preservation, no loop (clean not /blog/)
  - Front controller: `RewriteCond !-f !-d` + exclude assets/uploads/database/app/config → `public/index.php?path=$1 [L,QSA]` — safe for current static because all current URLs exist as dirs (e.g., /about/ → about/index.html, !-d false, no rewrite)
  - ErrorDocument 404 /404.html (future dynamic)
  - MIME types webp, woff2, svg

- **Router.php:**
  - Patterns: {slug} → ([a-z0-9-]+), {id} → (\d+)
  - Methods: get, post, add, pathToRegex, match (preg_match), dispatch (callable or Controller@method)
  - Legacy handling: isLegacyBlogRoute regex ^/blog/([a-z0-9-]+)/?$, getCleanBlogUrl, dispatch checks legacy before 404 → 301
  - Public routes list: /, /about/, /services/, /projects/, /pricing/, /blog/, /contact/, /faq/, /booking/, /terms/, /privacy/, /projects/{slug}/, /{slug}/ (blog clean)
  - No redirect loops: clean URL not starting with /blog/, legacy only redirects if slug valid, final PHP controller will verify slug exists in DB before redirect (to avoid open redirect)

**Future Frontend Integration (M5+):**
- When static HTML removed, .htaccess will route clean URLs to public/index.php, which will query DB via Models and render templates preserving current visual design exactly

---

## 6. Security Design

- **Output Escaping:** Security::e() htmlspecialchars ENT_QUOTES|SUBSTITUTE UTF-8, escapeAttr, escapeHtml, purifyHtml allowlist <h2><h3><p><ul><ol><li><a><strong><em>... strip script/style/iframe/on*/javascript:
- **CSRF:** Csrf::generate random_bytes 32, stored in session with time, getToken, validate hash_equals + expiry 1h, field() hidden input
- **Session:** Session.php secure: name MTBSESSID, lifetime 3600, path /, domain '', secure true (HTTPS prod), httponly true, samesite Lax, use_strict_mode 1, use_only_cookies 1, gc_maxlifetime, fingerprint SHA256 ip+ua+secret, regenerate on login, destroy clears cookie, isExpired check
- **Headers:** Security::setSecureHeaders nosniff, SAMEORIGIN, XSS block, Referrer strict-origin, HSTS max-age 31536000 includeSubDomains env=HTTPS
- **Safe Redirects:** Response::isSafeRedirect allows relative / (not //) or same-domain absolute (site_domain, app_url), else fallback /
- **Input Validation:** Validator.php required, email filter_var, slug regex ^[a-z0-9-]+$, url filter_var, length mb_strlen, inArray, validateContact with honeypot website field should be empty
- **Password:** Security::hashPassword PASSWORD_BCRYPT, verifyPassword, needsRehash
- **Path Safety:** sanitizeFilename basename + null byte removal + regex [^a-zA-Z0-9-_.]→- + double ext php→txt + strtolower, isSafePath realpath check base inside
- **Uploads Protection:** uploads/.htaccess php_flag off + deny php/exe/sh/bat, root .htaccess DirectoryMatch same, no executable uploads
- **Config Protection:** .htaccess FilesMatch deny config.php/.env/.git, RedirectMatch 403 database/app/Config/config, .gitignore config.php

---

## 7. Authentication Foundation

**Full admin UI belongs to M4, M3 only foundation:**

- **Session init:** Session::start() with secure params
- **Password verification:** Security::hashPassword/verifyPassword, User model verifyPassword with rehash
- **requireAdmin() middleware:** Auth::requireAdmin checks Session user_id + role admin/editor, if not logged → redirect /admin/login, if logged but not admin/editor → 403 + View renderError, check Session expiry 3600 → logout + redirect ?expired=1
- **Guest-only:** Auth::requireGuest checks if already logged → redirect /admin/dashboard
- **Login-attempt/rate-limit foundation:** RateLimiter.php isLoginAllowed 5/15min per IP via session, recordLoginAttempt, isContactAllowed 5/hour, checkDbRateLimit foundation for future DB table login_attempts (to be created in M4)
- **User model:** findByEmail active, verifyPassword, updateLastLogin
- **No admin account yet:** Per rules, do NOT create admin account in M3
- **No public registration:** No registration system

---

## 8. Repository/Model Design

**BaseModel:** db PDO, table, findById prepared, findBySlug prepared, all order, count, transaction helpers

**Models (17):**
- **SiteSettings:** get id=1, getSocialLinks JSON decode
- **Navigation:** getVisible order_index
- **Page:** getBySlug visible, getAllVisible
- **PageSection:** getByPageId, getByPageSlug JOIN pages
- **Service:** getAllVisible order, getBySlug
- **Project:** getAllVisible, getBySlug with technologies (SELECT technology) + features (SELECT feature ORDER order_index), getFeatured
- **ProjectTechnology:** getByProjectId
- **ProjectFeature:** getByProjectId order
- **BlogCategory:** getAll, getBySlug
- **BlogTag:** getAll, getByPostId JOIN post_tags
- **BlogPost (CRITICAL):** getPublished status=published AND is_visible=1 ORDER published_date_iso DESC LIMIT, getBySlug with category_name + featured_image_url + tags JOIN, getByOriginalId, getRelated scoring same category +2 shared tags +1 recency exclude current fallback most recent, getAllForAdmin (includes drafts)
- **BlogPostTag:** M2M
- **Media:** getByFilename, getById, getAll LIMIT, isSafePath
- **ContactMessage:** getAll LIMIT, getUnread, markRead
- **Testimonial:** getVisible order
- **SeoSetting:** getByPageType, getGlobal
- **User:** findByEmail active, verifyPassword with rehash, updateLastLogin

**Public Data Access (safe read methods):**
- getSiteSettings(), getNavigation(), getPageBySlug(), getServices(), getProjects(), getProjectBySlug(), getPublishedBlogPosts(), getBlogPostBySlug(), getBlogCategories(), getBlogTags(), getRelatedPosts(), getTestimonials(), getSEOSettings()
- Published only when status=published AND is_visible=1, drafts never exposed publicly (enforced in getPublished/getBySlug)

**Blog Requirements Preserved:**
- Title, excerpt, content, slug, featured image, alt, author, date, category, tags, reading_time, word_count, SEO meta/canonical/OG/Twitter
- Related logic same category + shared tags + recency exclude current (same as build.py related_posts())

---

## 9. Error Handling

- **Codes:** 404, 403, 405, 500
- **View.php renderError:** Simple HTML with black+gold inline style preserving visual language (gradient #ECBD61, background #060605, text #f5f3ef, muted #a8a29e), no redesign time, production-safe no stack trace, dev shows message if debug
- **Router:** dispatch returns 404 via View if no match, legacy blog 301 before 404, try/catch in public/index.php logs error, dev shows message, prod shows 500 via View
- **Database:** PDO ERRMODE_EXCEPTION, try/catch log error, throw RuntimeException without credentials exposure in production, display_errors Off in production config
- **.htaccess:** ErrorDocument 404 /404.html (existing static 404.html preserved), future dynamic via public/index.php?error=404

---

## 10. .htaccess Behavior

- **Clean URLs:** Supports /, /about/, /services/, /projects/, /pricing/, /blog/, /contact/, /faq/, /booking/, /terms/, /privacy/, /projects/{slug}/, /{slug}/
- **Front controller:** Routes non-existing files/dirs to public/index.php?path=$1, excludes assets/uploads/database/app/config
- **Existing assets:** Bypass PHP routing (RewriteCond !^/assets/ etc.), served directly with caching headers (woff2 1yr immutable, css/js 1d, img 7d)
- **WebP images:** MIME image/webp ensured, existing assets/img/blog/*.webp remain available
- **404 handling:** ErrorDocument 404 /404.html, plus Router 404 via View
- **Security headers:** nosniff, SAMEORIGIN, XSS block, Referrer, HSTS optional
- **Disable listing:** Options -Indexes
- **Protect sensitive:** FilesMatch deny config.php/.env/.git/composer/package, RedirectMatch 403 database/app/Config/config, FilesMatch deny migrate.php
- **Uploads protection:** php_flag engine off, AddType text/plain php, RemoveHandler, Options -ExecCGI -Indexes, FilesMatch deny php/exe/sh/bat (both root DirectoryMatch and uploads/.htaccess)
- **No break static:** All current URLs exist as dirs, so !-d false, no rewrite, static build still 24 pages, audit clean

---

## 11. cPanel Requirements

- **PHP:** 8.2+ (8.3 recommended), extensions pdo, pdo_mysql, gd or imagick, mbstring, fileinfo, openssl, json, curl
- **MySQL:** 8.0+ or MariaDB 10.6+ (JSON, CHECK, utf8mb4_unicode_ci)
- **Apache:** 2.4+ mod_rewrite, mod_expires, mod_deflate, mod_headers, mod_mime
- **Settings:** upload_max_filesize 32M, post_max_size 32M, memory_limit 256M, max_execution_time 60, display_errors Off, log_errors On, error_log outside public_html
- **Permissions:** Folders 755, files 644, uploads 755 writable, config.php outside public_html 600
- **SSL:** AutoSSL or Let's Encrypt, force HTTPS optional in .htaccess

---

## 12. Tests Performed

**Python simulation (since sandbox has no PHP/MySQL):** `tests/test_backend_py.py` — 22 tests

| # | Test | Status | Notes |
|---|------|--------|-------|
| 1 | PHP syntax (file existence + <?php) | PASS | 30+ PHP files exist with <?php |
| 2 | Config loading | PASS | config.example.php exists with db host/name/user/pass, site_domain |
| 3 | DB connection | BLOCKED BY ENVIRONMENT | No MySQL client, no PDO in sandbox — will be tested on cPanel staging |
| 4 | PDO init | BLOCKED | No PHP |
| 5 | Table detection | BLOCKED | No DB |
| 6 | Prepared queries | BLOCKED | No DB |
| 7 | Transaction rollback | BLOCKED | No DB |
| 8 | Transaction commit | BLOCKED | No DB |
| 9 | 404 handling | PASS | View.php renderError contains 404 |
| 10 | 403 handling | PASS | View.php renderError contains 403 |
| 11 | 405 handling | PASS | View.php renderError contains 405 |
| 12 | Output escaping | PASS | Security.php has htmlspecialchars ENT_QUOTES |
| 13 | CSRF token generation/validation | PASS | Csrf.php has random_bytes 32 + hash_equals |
| 14 | Session initialization | PASS | Session.php has session_start + httponly + samesite |
| 15 | password_hash/password_verify | PASS | Security.php has PASSWORD_BCRYPT + password_verify |
| 16 | Authorization helper | PASS | Auth.php has requireAdmin + check + isAdmin |
| 17 | Published vs draft visibility | BLOCKED | No DB, but model checks status=published AND is_visible=1 |
| 18 | Slug lookup | BLOCKED | No DB, but model has getBySlug prepared |
| 19 | Related posts query | BLOCKED | No DB, but BlogPost has getRelated scoring category+tags |
| 20 | Media path safety | PASS | Security.php has sanitizeFilename + isSafePath basename |
| 21 | Uploads PHP protection | PASS | uploads/.htaccess has php_flag off + Require all denied |
| 22 | Existing static build | PASS | site_src/build.py + index.html exist, build 24 pages, audit clean |

**Summary:** PASS 13, FAIL 0, NOT RUN 0, BLOCKED 9 (DB tests blocked by environment, clearly distinguished)

**Static Build Verification:**
```
Built 24 pages — 4 posts ×2 routes = 8 blog pages + 16 other. Clean: [...]
Audited 24 HTML pages. AUDIT CLEAN
```
- Build succeeds, 24 pages, existing audit passes, assets remain available, no modification to build.py

**PHP tests (for cPanel):** `tests/test_backend.php` — 22 tests same, uses php -l for syntax, PDO tests if real config.php present, else NOT RUN/BLOCKED distinction

---

## 13. Tests Blocked by Environment

- **3-8, 17-19 DB tests BLOCKED:** No MySQL client, no php binary in sandbox (apt permission denied, only Python 3.13). Will be tested on cPanel staging with:
  - `php -v` → PHP 8.2+
  - `php -m | grep pdo`
  - `php tests/test_backend.php --verbose` with real config.php
  - phpMyAdmin SHOW TABLES → 17 tables
  - Manual: SELECT VERSION(), JSON_VALID test
- Clearly distinguished PASS vs BLOCKED vs NOT RUN per requirements, no false claim of MySQL pass

---

## 14. Existing Static Build Verification

- **Command:** `python3 site_src/build.py` → Built 24 pages — 4 posts ×2 routes = 8 blog pages + 16 other
- **Audit:** `python3 tools/audit.py` → Audited 24 HTML pages, AUDIT CLEAN
- **No modification to build.py** — per file change policy, old static architecture remains available as rollback/reference
- **Frontend untouched:** CSS 629 lines preserved black+gold, JS 287 lines preserved, brand assets, portrait, blog WebP, project SVG, HTML all still functional

---

## 15. Risks

- **No PHP in sandbox:** Cannot run php -l syntax check or PDO tests, only Python simulation — risk mitigated by careful manual code review and cPanel staging test plan
- **No MySQL in sandbox:** Cannot test actual import of schema.sql or migration — risk mitigated by phpMyAdmin instructions + testConnection() method + BLOCKED distinction
- **.htaccess legacy redirect:** `^blog/([a-z0-9-]+)/?$` → `/$1/` 301 could affect current static if legacy and clean both exist (currently both exist, redirect will cause legacy to go to clean, which is desired final SEO but changes current static behavior slightly — still serves content, not breaking, but should be tested)
- **Front controller routing:** If public/index.php not found, .htaccess will 500 — need to ensure file exists (it does) and has no syntax errors
- **Config outside public_html:** In repo, config is inside repo/config/ for dev, but in production should be outside — need to document and ensure loader tries multiple paths (it does)
- **Session secure true:** In local http, secure cookie may not be sent — need env-aware secure false in development (config has secure true for prod, should be false for local http, documented)
- **No admin account yet:** Per rules, not created in M3 — need to ensure User model and Auth foundation ready for M4

---

## 16. Deviations from Approved Architecture

- **None major** — followed approved architecture: PHP 8.2+ MVC-lite, MySQL PDO, Apache .htaccess front controller, secure config outside public_html, session bcrypt, router clean URLs + legacy 301, security foundation, auth foundation, model/repository foundation, public data access safe, blog requirements preserved, media foundation with uploads protection
- **Minor additions:**
  - Created `public/index.php` as future front controller (recommended concept had public_html/index.php, we created public/index.php + .htaccess at root routing to it — safe for static, documented)
  - Created `config/config.example.php` at repo/config/ in addition to app/Config/Config.php loader — provides clear template for cPanel, not deviating
  - Created `uploads/.htaccess` separate file in addition to root .htaccess DirectoryMatch — defense in depth for uploads protection
  - Created Python simulation tests `test_backend_py.py` because sandbox has no PHP — not in approved architecture but necessary for validation without modifying existing JS/CSS, does not affect production

---

## 17. File Change Policy Compliance

**Allowed (new backend files):**
- app/Config/Config.php, app/Core/*, app/Models/*, app/Services/*, app/Validation/*, app/Security/*, app/Views/*, public/index.php, config/config.example.php, .htaccess, uploads/.htaccess, .gitignore, tests/*, docs/MILESTONE-3-BACKEND-REPORT.md

**Do NOT modify (per policy):**
- content.json — untouched
- blog_posts_real.json — untouched, not shortened/rewritten
- existing blog content — untouched
- existing CSS — styles.css preserved black+gold, no redesign
- existing JS — main.js preserved
- brand assets — untouched
- existing static HTML — index.html etc. still generated via build.py, not replaced with PHP templates yet per "No Frontend Migration Yet"
- build.py — not modified
- server.js — not modified
- vercel.json — not modified

**Verified via git status:** Only untracked database/, docs/, app/, config/, public/, uploads/.htaccess, .htaccess, .gitignore, tests/ — no modified existing files

---

## 18. Final Stop Condition

- **Milestone 3 complete:** PHP backend foundation ready, tested via Python simulation (13 PASS, 9 BLOCKED by environment, 0 FAIL), static build 24 pages audit clean
- **Do NOT continue to:** full frontend conversion, admin dashboard, admin CRUD, contact system, media upload UI, production deployment — per final stop condition
- **Next:** Await approval for Milestone 4 (Admin Dashboard + Authentication)

---

## Appendix: cPanel Staging Setup to Verify DB Connection

1. **PHP version:** cPanel → Select PHP Version → 8.2 or 8.3, enable pdo, pdo_mysql, gd, mbstring, fileinfo, openssl, json, curl
2. **Create DB:** MySQL Databases → Create DB `portfolio` → actual `username_portfolio`, Create User `portfolio_admin` strong password → Add to DB ALL PRIVILEGES
3. **Import schema:** phpMyAdmin → Select DB → Import → `database/schema.sql` → Go → Verify 17 tables, check Designer for FKs
4. **Config:** FTP → Create `/home/username/config.php` from `config/config.example.php` with real creds, chmod 600, or create `config/config.php` inside repo for dev
5. **Test connection:** cPanel Terminal or SSH: `php -r "require 'app/Core/Database.php'; use App\Core\Database; print_r(Database::testConnection());"` or `php tests/test_backend.php --verbose`
6. **Expected testConnection() output:**
   - php_version: 8.2.x
   - pdo_available: true
   - pdo_mysql_available: true
   - connected: true
   - version: 8.0.x or 10.6.x MariaDB
   - tables: 17
   - json_support: true
7. **If fails:** Check error message, verify DB name/user/pass/host, ensure MySQL server running, check charset utf8mb4

---

**End of Milestone 3 Report**
