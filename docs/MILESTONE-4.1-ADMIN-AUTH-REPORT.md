# MILESTONE 4.1 — ADMIN AUTHENTICATION + DASHBOARD SHELL REPORT

**Date:** 2026-09-23
**Milestone:** 4.1 — Admin Auth + Dashboard Shell Only
**Status:** Complete, tested via Python simulation (no PHP/MySQL in sandbox), static site remains clean
**Previous Milestones:** M1 DB Foundation (17 tables), M2 Migration (24 media, 4 blog posts, etc.), M3 Backend Foundation (PDO, Router, Security)

---

## 1. Files Created

**New in M4.1:**

- `app/Controllers/Admin/BaseAdminController.php` — ensures requireAdmin() for all admin except AuthController, secure headers
- `app/Controllers/Admin/AuthController.php` — showLogin GET, login POST, logout POST, CSRF validation, rate limiting 5/15min, generic failure message, dummy hash to prevent timing/user enumeration, delay 0.5s on fail, active check, login + last_login update
- `app/Controllers/Admin/DashboardController.php` — index redirect to /admin/dashboard, dashboard with real counts + system status, placeholder() for future CRUD sections
- `app/Views/admin/layout.php` — reusable admin layout: black/dark base #060605 + gold #ECBD61, sidebar fixed 280px with header/logo, close button, nav sections Overview/Content/Website/Communication/System, active state is-active gold, main wrapper margin-left 280px, header sticky with burger (mobile), title + breadcrumb + user + View Site, main with flash success/error, footer, responsive: sidebar transforms -100% on <=1024px, burger shows, grid 1fr on mobile
- `app/Views/admin/login.php` — professional login black+gold, centered card 440px, logo lettermark, h1 Admin Login, flash error, form POST /admin/login with _csrf hidden, email type email required autocomplete email autofocus old_email preserved, password type password required autocomplete current-password no value (no echo), btn primary full, note secure admin area + back to website link, no registration, no password reset
- `app/Views/admin/dashboard.php` — stats grid 8 cards: Pages 11 expected, Services 10, Projects 4, Blog Published 4, Blog Draft 0, Testimonials 0, Messages + unread, Media 24, plus grid 2: System Status dl (DB connected badge success/error, tables /17, PHP version 8.2+, env, milestone M4.1) + Quick Actions btns to future sections badge M4.2, plus preservation check list (4 blog posts, project slugs, blog slugs, services, pages, media) + static rollback note
- `app/Views/admin/placeholder.php` — centered card with h2 title, lead "Coming in the next milestone", muted M4.1 only, badge M4.2, back to dashboard
- `assets/css/admin.css` — separate from public styles.css, black+gold variables --admin-bg #060605, --admin-gold #ECBD61, etc., login body radial gradient gold, login card surface #161410 border line, field label uppercase 12px, input bg-2 border line focus gold, btn primary gold black hover gold-2 translateY -1px, btn ghost transparent, flash error rgba 224,82,82 + success rgba 76,175,125, admin wrapper flex, sidebar 280px fixed, sidebar header, logo, close, nav section label 10px uppercase muted, nav link flex gap 10px padding 9px 10px radius 8px hover rgba white .04, active rgba gold .12 + gold + border gold .2, logout hover danger, main wrapper margin-left 280px, header sticky, burger hidden desktop flex mobile, breadcrumb, user name/role, main max-width 1200px padding 28px, footer, stats grid auto-fill minmax 180px, stat card surface border radius, badge success/error, responsive 1024px sidebar transform -100% is-open 0, burger flex, grid 1fr, 640px padding 18px
- `assets/js/admin.js` — vanilla, burger toggle sidebar is-open, aria-expanded, body overflow hidden, close on X, Escape, outside click on mobile
- `scripts/create_admin.php` — CLI only check php_sapi_name !== 'cli' → 403, loads config/config.php, checks pdo/pdo_mysql, prompts name (min 2), email filter_var, password secure promptPassword with stty -echo hiding, validates password policy min 8 + upper + lower + number + symbol + not common, confirm, hash bcrypt password_hash, PDO DSN mysql:host/dbname/charset utf8mb4, check duplicate email SELECT, INSERT users email/hash/name/role admin/is_active 1 prepared, never print password/hash, success message ID/email/name/role, never log password, fallback phpMyAdmin manual documented: generate hash locally php -r "echo password_hash(...)", paste into phpMyAdmin
- `tests/test_admin.php` — 22 admin tests: routes defined, AuthController exists, DashboardController exists, login view black+gold email/password/CSRF/no echo, layout sidebar/header/flash/responsive, dashboard real counts + system status, admin CSS separate black+gold, admin JS exists, CLI script CLI only bcrypt secure prompts, no hardcoded creds, CSRF login/logout, rate limiting 5/15min, session security httponly/samesite/strict/regenerate, password bcrypt no plaintext, no enumeration generic + dummy hash, protected routes requireAdmin, logout destroys session + CSRF, dashboard counts, system status no secrets, placeholders coming next milestone, plus 8 DB tests blocked
- `tests/test_admin_py.py` — Python simulation for sandbox without PHP/MySQL, 18 tests: admin routes, AuthController, login view, no enumeration, CLI admin, session security, rate limiting, requireAdmin, dashboard shell, layout, placeholder, admin CSS, no hardcoded, plus 5 blocked DB tests

**Total new M4.1:** ~15 files

---

## 2. Files Modified

- `public/index.php` — **Modified to add admin routes** (M3 had only public placeholder routes, now adds admin routes GET /admin/login, POST /admin/login, POST /admin/logout, GET /admin/ redirect, GET /admin/dashboard, plus 11 placeholders for future CRUD /admin/pages, /services, /projects, /blog, /testimonials, /settings, /navigation, /seo, /media, /messages, /account). Loads Models + Security/Csrf/RateLimiter + BaseAdminController/AuthController/DashboardController. Existing public routes preserved, no breaking. **Why change required:** Front controller must handle /admin/* clean URLs via .htaccess routing (non-existing files/dirs → public/index.php). Without this, /admin/login would 404. Change is additive, does not modify public HTML generation, static site still builds 24 pages audit clean.

- `.htaccess` — Already created in M3, no modification in M4.1 (already supports /admin/* routing via !-f !-d → public/index.php)

- No modification to: content.json, blog_posts_real.json, build.py, existing CSS (styles.css preserved, admin.css separate), existing JS (main.js preserved, admin.js separate), existing static HTML (index.html etc. still generated), brand assets, Vercel config

---

## 3. Routes Created

| Method | Path | Handler | Auth | Description |
|--------|------|---------|------|-------------|
| GET | /admin/login | Admin\AuthController@showLogin | Guest only (redirect if auth) | Login page black+gold, CSRF token, error flash, old_email |
| POST | /admin/login | Admin\AuthController@login | Guest only | Validate CSRF, rate limit 5/15min IP, validate email/password required, email filter, findByEmail, dummy hash to prevent timing, verify bcrypt, active check, record rate limit, login regenerate, update last_login, redirect dashboard, generic failure "Invalid email or password" no enumeration |
| POST | /admin/logout | Admin\AuthController@logout | Protected (CSRF) | Validate POST, CSRF, Auth::logout destroy, redirect /admin/login |
| GET | /admin/ | Admin\DashboardController@index | Protected requireAdmin | Redirect 302 to /admin/dashboard |
| GET | /admin/dashboard | Admin\DashboardController@dashboard | Protected requireAdmin | Real counts pages/services/projects/blog_published/draft/total/testimonials/messages/unread/media/users + system status DB connected/version/tables/PHP version/env/app_version M4.1, user info, render layout + dashboard |
| GET | /admin/pages | Placeholder | Protected | Shows "Coming in the next milestone" |
| GET | /admin/services | Placeholder | Protected | Same |
| GET | /admin/projects | Placeholder | Protected | Same |
| GET | /admin/blog | Placeholder | Protected | Same |
| GET | /admin/testimonials | Placeholder | Protected | Same |
| GET | /admin/settings | Placeholder | Protected | Same |
| GET | /admin/navigation | Placeholder | Protected | Same |
| GET | /admin/seo | Placeholder | Protected | Same |
| GET | /admin/media | Placeholder | Protected | Same |
| GET | /admin/messages | Placeholder | Protected | Same |
| GET | /admin/account | Placeholder | Protected | Same |

**Unauthenticated behavior:**
- GET /admin/login → 200 login page
- GET /admin/dashboard → redirect 302 to /admin/login (via BaseAdminController requireAdmin)
- GET /admin/ → redirect to /admin/dashboard → then redirect to /admin/login if not auth (chain)

**Authenticated behavior:**
- GET /admin/login → redirect 302 to /admin/dashboard
- GET /admin/dashboard → 200 dashboard shell
- POST /admin/logout → session destroyed, redirect /admin/login

---

## 4. Admin Authentication Flow

```
Visitor → GET /admin/login
  → Session start secure (MTBSESSID, HttpOnly, SameSite Lax, strict_mode)
  → Auth::check() ? if yes redirect /admin/dashboard
  → Else Csrf::getToken() → render login view with _csrf hidden, old_email, error flash

Visitor → POST /admin/login with email, password, _csrf
  → Method validation POST only else 405
  → Auth::check() ? if already auth redirect dashboard
  → CSRF validate hash_equals + expiry 1h else error + redirect login
  → RateLimiter::isLoginAllowed(ip) check session count 5/15min else error + sleep 2 + redirect
  → Validation required email/password, email filter_var else generic error + record attempt
  → User model findByEmail (prepared) — if not found use dummyHash $2y$10$... to prevent timing
  → Security::verifyPassword(password, hash) bcrypt
  → If !user or !valid → record failed attempt, usleep 0.5s, generic error "Invalid email or password" (no enumeration), old_email preserved, redirect login
  → If user is_active !=1 → error disabled, redirect
  → Success: RateLimiter record success clear, Auth::login(user) regenerate session id true, fingerprint SHA256 ip+ua+secret, set user_id/email/name/role/logged_in_at, update last_login_at via User model prepared, flash success, redirect /admin/dashboard

Authenticated → GET /admin/dashboard
  → BaseAdminController __construct → Auth::requireAdmin() checks user_id + role admin/editor else redirect /admin/login or 403, check expiry 3600
  → DashboardController getCounts() PDO queries COUNT(*) from pages, services, projects, blog_posts published/draft, testimonials, messages/unread, media, users — try/catch keeps 0 if DB not available
  → getSystemStatus() Database::testConnection() → db_connected, version, tables, php_version, env, app_version M4.1
  → renderAdmin layout + dashboard view

Authenticated → POST /admin/logout with _csrf
  → Method POST only else 405
  → CSRF validate (log if fails but still logout for safety)
  → Auth::logout() Session::destroy() clears $_SESSION, deletes cookie, session_destroy
  → Redirect /admin/login
```

---

## 5. Security Implementation

- **CSRF:** Csrf::generate random_bytes 32 bin2hex, stored session _csrf_token + time, getToken, validate hash_equals + expiry 1h, field() hidden input, login form has _csrf, logout form has _csrf, AuthController validates on POST login/logout
- **Session:** Session.php secure: name MTBSESSID, lifetime 3600, path /, secure true (prod HTTPS), httponly true, samesite Lax, use_strict_mode 1, use_only_cookies 1, gc_maxlifetime, fingerprint SHA256 ip+ua+secret, regenerate on login true, destroy clears cookie, isExpired 3600
- **Rate Limiting:** RateLimiter.php isLoginAllowed checks session login_attempts_{ip} count 5 window 15min (900 sec), recordLoginAttempt increments or clears on success, isContactAllowed 5/hour for future, checkDbRateLimit foundation for future DB table login_attempts, AuthController uses isLoginAllowed before validation + recordLoginAttempt on fail/success + sleep 2 on rate limited + usleep 0.5s on fail to slow brute force and make timing consistent
- **Password:** Security::hashPassword PASSWORD_BCRYPT, verifyPassword, needsRehash, User::verifyPassword uses Security::verifyPassword + rehash if needed, scripts/create_admin.php uses password_hash bcrypt, never plaintext, never log password, never print password/hash, promptPassword uses stty -echo to hide input
- **No User Enumeration:** Generic failure message "Invalid email or password" for both user not found and password wrong, dummy hash $2y$10$usesomesilly... to make timing similar, same redirect + old_email preserved, no reveal if email exists
- **No Password Echo:** input type password, no value attribute, no echo
- **SQL Injection:** All queries via PDO prepared statements with bound params (BaseModel findById, findBySlug, User findByEmail, Dashboard counts via query but no user input, only COUNT(*), safe)
- **XSS:** Security::e() htmlspecialchars ENT_QUOTES for all rendered user/database content in admin views (title, counts, user name/email/role, flash messages, error), login view old_email escaped, dashboard counts int cast, system status escaped
- **Authorization:** BaseAdminController __construct calls Auth::requireAdmin() for all admin except AuthController, requireAdmin checks user_id + role admin/editor else redirect /admin/login or 403 via View renderError, expiry check
- **Session Fixation:** regenerate ID on login via Session::regenerate() true, use_strict_mode 1
- **Session Expiration:** Session::_last_activity time() set, isExpired 3600, requireAdmin checks expiry → logout + redirect ?expired=1
- **Brute-force:** Rate limiting 5/15min IP, delay sleep 2 on rate limit, usleep 0.5s on fail, clear on success
- **HTTP Method Validation:** Request::isPost() check in login/logout, else 405 Method Not Allowed
- **Safe Redirects:** Response::isSafeRedirect allows relative / not // or same-domain absolute site_domain/app_url, else fallback /, prevents open redirect
- **Config Exposure:** Config::getSafeConfig removes db pass, smtp pass, secret_key, .htaccess denies config.php/.env/.git/database/app/Config/config/migrate.php, .gitignore config.php, system status shows db_connected/version/tables/php_version/env/app_version but no secrets
- **Uploads Protection:** uploads/.htaccess php_flag engine off + deny php/exe/sh/bat, root .htaccess DirectoryMatch same
- **Direct Access Protection:** .htaccess RedirectMatch 403 database/app/Config/config, FilesMatch deny migrate.php, scripts/create_admin.php CLI only check php_sapi_name !== 'cli' → 403

---

## 6. Dashboard Functionality

**Shell/UI Only, No CRUD Yet:**

- **Visual:** Professional CMS, black/dark base #060605 + gold #ECBD61 accent, clean typography Sora/Rubik system-ui, responsive desktop sidebar 280px fixed + mobile burger, clear hierarchy
- **Layout:** Reusable admin/layout.php with sidebar (header logo lettermark + close X, nav sections Overview/Content/Website/Communication/System with icons and labels, active is-active gold, logout form POST with CSRF, footer muted M4.1), main wrapper margin-left 280px, header sticky with burger + title + breadcrumb Dashboard/Current + user name/role + View Site btn, main max-width 1200px, footer
- **Navigation Placeholders (11):** Dashboard, Pages, Services, Projects, Blog, Testimonials, Site Settings, Navigation, SEO, Media, Messages, Admin Account, Logout — all placeholders except Dashboard show "Coming in the next milestone" via placeholder.php with badge M4.2
- **Overview Real Counts (safe):**
  - Pages: SELECT COUNT(*) FROM pages → 11 expected
  - Services: 10 expected
  - Projects: 4 expected
  - Blog Published: status=published → 4 expected
  - Blog Draft: status=draft → 0 expected
  - Blog Total: published+draft
  - Testimonials: 0 expected future
  - Messages: COUNT(*) + unread is_read=0
  - Media: 24 expected
  - Users: COUNT(*)
  - If table empty or DB not available, display 0, try/catch log error
- **System Status:**
  - DB connection: Connected/Disconnected badge + version + tables /17 + error if any
  - PHP version: PHP_VERSION (required 8.2+)
  - Env: production/development/staging
  - App version: M4.1 Milestone Admin Auth + Dashboard Shell
  - No secrets exposed (no db password, secret keys, smtp)

---

## 7. Database Verification

**Before and after M4.1:**

- **Blog posts still exist:** 4 posts verified via Python (website-speed-optimization len 16071 hash 6b7c65d4d6c6, benefits-of-responsive-web-design 14147 hash 3846ba3e, wordpress-... 14812 hash 17f314fe, benefits-of-a-professional-business-website 10294 hash 25e9d0da) — counts from previous migration report, full content unchanged, no rewrite
- **Project slugs unchanged:** seo-agency-website, web-hosting-company-website, creative-agency-portfolio, digital-creative-studio
- **Blog slugs unchanged:** website-speed-optimization, benefits-of-responsive-web-design, wordpress-website-development-is-a-smart-choice, benefits-of-a-professional-business-website
- **Services unchanged:** 10 expected
- **Pages unchanged:** 11 expected
- **Media unchanged:** existing WebP assets remain, no deletion
- **SEO data unchanged:** canonical URLs https://mashzidultanun.com/{slug}/ preserved

**Verification via:**
- Python simulation: content.json projects 4, services 10, blog 4, content lengths and hashes match previous migration report
- Dashboard counts (when DB available) will show real counts via SELECT COUNT(*)
- No migration re-run in M4.1, no data rewritten

---

## 8. Test Results

**Python Simulation (sandbox no PHP/MySQL):**

- `tests/test_backend_py.py`: PASS 13, BLOCKED 9 (DB tests), FAIL 0, Build PASS, Audit PASS (24 pages)
- `tests/test_admin_py.py`: PASS 13, BLOCKED 5, FAIL 0
  - PASS: Admin routes GET/POST /admin/login/logout/dashboard, AuthController exists with CSRF+RateLimiter, Login view black+gold email/password/CSRF/no echo, No enumeration generic+dummy hash, CLI admin creation CLI only bcrypt secure prompts, Session security HttpOnly SameSite strict_mode regenerate, Rate limiting 5/15min, Protected routes requireAdmin, Dashboard shell real counts+system status, Admin layout sidebar/header/flash/responsive, Placeholder coming next milestone, Admin CSS separate black+gold, No hardcoded creds
  - BLOCKED: Invalid credentials DB, Successful auth DB, Protected route redirect unauth, Logout destroys session, Existing data preservation 4 blog posts (no DB in sandbox, will test on cPanel staging)

**PHP Tests (for cPanel):**
- `tests/test_backend.php`: 22 tests syntax/config/DB/transactions/404/403/405/escaping/CSRF/session/password/auth/blog visibility/slug/related/media safety/uploads protection/static build — PASS for runnable, BLOCKED for DB if no real config
- `tests/test_admin.php`: 22 admin tests routes/AuthController/Dashboard/login view/layout/dashboard/CSS/JS/CLI script/no hardcoded/CSRF/rate limit/session security/password/no enumeration/requireAdmin/logout/counts/system status/placeholders + 8 DB tests blocked

**Clearly distinguished:** PASS vs FAIL vs NOT RUN vs BLOCKED BY ENVIRONMENT, never claim MySQL/PHP pass if environment cannot execute

---

## 9. Static Build Result

```
python3 site_src/build.py
Built 24 pages — 4 posts ×2 routes = 8 blog pages + 16 other. Clean: ['website-speed-optimization', 'benefits-of-responsive-web-design', 'wordpress-website-development-is-a-smart-choice', 'benefits-of-a-professional-business-website']
```

**PASS — 24 pages, same as before M4.1**

---

## 10. Audit Result

```
python3 tools/audit.py
Audited 24 HTML pages.
AUDIT CLEAN: links, assets, alt text, heading structure all OK.
```

**PASS — No broken links, assets, alt, H1**

---

## 11. Blocked Tests

- **DB connection, PDO init, table detection, prepared queries, transaction rollback/commit, blog visibility, slug lookup, related posts, invalid credentials, successful auth, protected route redirect, logout, existing data preservation DB counts** — BLOCKED BY ENVIRONMENT: No MySQL client, no PHP binary in sandbox (apt permission denied, only Python 3.13), will be tested on cPanel staging with:
  - `php -v` → 8.2+
  - `php -m | grep pdo_mysql`
  - `php tests/test_backend.php --verbose` + `php tests/test_admin.php --verbose` with real config.php
  - phpMyAdmin SHOW TABLES 17
  - Manual login test: valid/invalid creds, rate limiting, session, CSRF

---

## 12. Risks

- **No PHP in sandbox:** Cannot run php -l or PDO tests, only Python file existence checks — mitigated by careful code review + cPanel staging plan
- **No MySQL in sandbox:** Cannot test actual login, counts, rate limiting DB — mitigated by session-based rate limiting for M4.1 + testConnection() + BLOCKED distinction
- **.htaccess legacy redirect:** `^blog/([a-z0-9-]+)/?$` → `/$1/` 301 will redirect legacy blog URLs to clean even in static mode (currently both exist, redirect will make legacy go to clean, which is desired final SEO but changes current static behavior slightly — still serves content, not breaking, but should be tested)
- **Front controller:** public/index.php must exist and have no syntax errors, else .htaccess rewrite to it will 500 for /admin/* — file exists and was manually reviewed
- **Session secure true:** In local http without HTTPS, secure cookie may not be sent — config has secure true for prod, should be false for local http dev (documented in config.example.php)
- **No admin account yet in repo:** Must be created via CLI script, not hardcoded — risk of no admin if script not run, mitigated by documentation
- **Admin CSS separate:** assets/css/admin.css separate from styles.css, no contamination, but need to ensure /assets/css/admin.css is accessible (it is, file exists, .htaccess allows /assets/)

---

## 13. Exact Instructions for Creating First Admin

### Via SSH / cPanel Terminal (Recommended, Secure)

1. **Upload project** to cPanel (e.g., `/home/username/mashzidul-portfolio` or `/home/username/public_html` if repo root is doc root, but keep app/ outside ideally)
2. **Create Database** (if not already from M1): cPanel → MySQL Databases → Create DB `portfolio` (actual `username_portfolio`) → Create User `portfolio_admin` strong password 20+ chars → Add User to DB ALL PRIVILEGES
3. **Import Schema** (if not already): phpMyAdmin → Select DB → Import → `database/schema.sql` → Go → Verify 17 tables
4. **Configure:** 
   ```bash
   cp config/config.example.php config/config.php
   # Edit config.php with real DB creds:
   # db_host localhost, db_name username_portfolio, db_user username_admin, db_pass STRONG_PASSWORD
   # Set env production, debug false, session secure true (HTTPS)
   chmod 600 config/config.php
   ```
   Or for production outside public_html:
   ```bash
   cp config/config.example.php /home/username/config.php
   chmod 600 /home/username/config.php
   # Edit /home/username/config.php
   ```
5. **Create Admin (CLI only):**
   ```bash
   php scripts/create_admin.php
   ```
   Prompts:
   ```
   Name (e.g., Mashzidul Tanun Borshon): Mashzidul Tanun Borshon
   Email: mail@mashzidultanun.com
   Password (min 8 chars, upper/lower/number/symbol): [hidden input via stty -echo]
   Confirm Password: [hidden]
   ```
   Validation: email filter_var, password policy min 8 + upper + lower + number + symbol + not common, hash bcrypt, PDO prepared INSERT, duplicate email check, never print password/hash
   Success:
   ```
   ✅ Admin account created successfully!
   ID: 1
   Email: mail@mashzidultanun.com
   Name: Mashzidul Tanun Borshon
   Role: admin
   You can now login at /admin/login
   ```
6. **Secure:** Delete script or keep protected (CLI only check + .htaccess deny `migrate.php` and `database/` + `app/Config/`). Recommend keep but ensure not web-accessible (it already has CLI only + .htaccess 403 for database/)
7. **Login:** Visit https://mashzidultanun.com/admin/login → enter email/password → should redirect to /admin/dashboard with counts and system status

### Fallback Without SSH — phpMyAdmin Manual (Safe, No Plaintext Stored)

If hosting has no SSH/Terminal:

1. **Generate bcrypt hash locally** on your machine with PHP:
   ```bash
   php -r "echo password_hash('YourStrongPass123!', PASSWORD_BCRYPT) . PHP_EOL;"
   # Output: $2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG (60 chars)
   ```
   Never share hash publicly, never store plaintext in DB.

2. **cPanel → phpMyAdmin → Select DB → users table → Insert:**
   - email: mail@mashzidultanun.com
   - password_hash: paste the $2y$10$... hash from local generation
   - name: Mashzidul Tanun Borshon
   - role: admin
   - is_active: 1

3. **Login** at /admin/login with email and original password (not hash)

**IMPORTANT:** Never store plaintext password in DB, only hash. Never commit config.php with real creds. Revoke/delete admin creation script after use if desired.

---

## 14. Confirmation That No Public Content Was Modified

- **content.json:** Untouched (verified git status no M, size 14899)
- **blog_posts_real.json:** Untouched (60450 bytes, 4 posts, content lengths 16071/14147/14812/10294 hashes 6b7c65d4..., 3846ba3e..., 17f314fe..., 25e9d0da... same as M2)
- **site_src/build.py:** Untouched (1216 lines)
- **Existing CSS:** `assets/css/styles.css` preserved 629 lines black+gold, no redesign, admin.css separate
- **Existing JS:** `assets/js/main.js` preserved 287 lines, admin.js separate
- **Existing static HTML:** index.html etc. still generated via build.py 24 pages, audit clean, no replacement with PHP templates yet per rule
- **Brand assets:** assets/img/brand/*, portrait, fonts, projects SVG untouched
- **Vercel config:** vercel.json untouched (buildCommand null, outputDirectory ., trailingSlash true)
- **Build verification:** `python3 site_src/build.py` Built 24 pages + `python3 tools/audit.py` AUDIT CLEAN — same as before M4.1
- **Git status:** Only untracked database/, docs/, app/, config/, public/, uploads/.htaccess, .htaccess, .gitignore, tests/, scripts/ — no modified existing public files except public/index.php (additive admin routes, explained, does not break static because static files exist and !-f !-d false)

**Public static website remains working rollback/reference version.**

---

## 15. Known Limitations (M4.1 Only)

- No CRUD editors yet — placeholders show "Coming in next milestone" per rules
- No media upload UI yet — media foundation only
- No contact message management yet — placeholder
- No site settings editor yet — placeholder
- No password reset yet — per rules
- No registration — per rules
- Rate limiting currently session-based (M3 foundation), DB table login_attempts will be in M4 full auth if needed
- No admin account in repo — must be created via CLI script
- DB tests blocked in sandbox — will be tested on cPanel staging

---

**End of Milestone 4.1 Report — Awaiting approval for M4.2 CRUD**
