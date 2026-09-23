# Database Foundation — Milestone 1

**Status:** Production-ready schema, no data migration yet
**Approved Architecture:** PHP 8.2+ MVC-lite + MySQL/MariaDB + Apache/cPanel
**File:** `database/schema.sql`

---

## 1. Database Name Expectations

cPanel prefixes database names with cPanel username, e.g.:

- cPanel username: `borshonba`
- Database name you create: `portfolio` → actual MySQL name: `borshonba_portfolio`
- Database user: `borshonba_admin` → actual: `borshonba_admin`
- Host: `localhost` (standard cPanel)

**Recommended names:**
- Database: `mashzidul_portfolio` or `portfolio` (will become `username_portfolio`)
- User: `portfolio_admin` or `cms_admin`

**In `config.php` (to be created in Milestone 3, outside public_html):**
```php
return [
  'db_host' => 'localhost',
  'db_name' => 'borshonba_portfolio', // actual cPanel name
  'db_user' => 'borshonba_admin',
  'db_pass' => 'STRONG_PASSWORD_HERE',
];
```

---

## 2. Import Instructions

### Via cPanel phpMyAdmin (Recommended for shared hosting)
1. cPanel → MySQL Databases → Create Database `portfolio` → Create User `portfolio_admin` with strong password → Add User to Database → Check ALL PRIVILEGES
2. cPanel → phpMyAdmin → Select your database (`borshonba_portfolio`)
3. Import tab → Choose File → `database/schema.sql` → Go
4. Verify 17 tables created, no errors

### Via MySQL CLI (VPS or local)
```bash
mysql -u root -p -e "CREATE DATABASE portfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p portfolio < database/schema.sql
```

### Via PDO (Future migration script)
Migration script in Milestone 2 will use PDO to import if needed, but schema.sql is primary.

### Requirements
- **MySQL:** 8.0+ or MariaDB 10.6+ (JSON support, utf8mb4, CHECK constraints)
- **PHP:** 8.2+ with extensions: pdo, pdo_mysql, gd or imagick, mbstring, fileinfo, openssl, json
- **Charset:** utf8mb4 / utf8mb4_unicode_ci everywhere (consistent)

---

## 3. Table Relationships

```
users
  └─< media.uploaded_by (SET NULL)

media
  ├─< site_settings.logo_*_id (SET NULL) — 11 FKs for logos/favicon/og
  ├─< pages.og_image_id, twitter_image_id (SET NULL)
  ├─< projects.art_media_id (SET NULL)
  ├─< blog_posts.featured_image_id, og_image_id, twitter_image_id (SET NULL)
  ├─< testimonials.image_id (SET NULL)
  └─< seo_settings.og_default_image_id (SET NULL)

site_settings (single row id=1)
  - social_links JSON (6 official socials)

navigation_items
  └─ self parent_id (SET NULL) for future dropdowns

pages
  └─< page_sections.page_id (CASCADE)

services (independent)

projects
  ├─< project_technologies.project_id (CASCADE) UNIQUE(project_id, technology)
  └─< project_features.project_id (CASCADE)

blog_categories
  └─< blog_posts.category_id (SET NULL) — keep post if category deleted

blog_tags
  └─< blog_post_tags.tag_id (CASCADE)

blog_posts
  ├─< blog_post_tags.post_id (CASCADE)
  └─ category_id → blog_categories, featured/og/twitter → media

blog_post_tags (M2M)
  PK (post_id, tag_id), CASCADE both

contact_messages (independent, no FK, stores IP, honeypot, agenda JSON)

testimonials
  └─ image_id → media (SET NULL), rating CHECK 1-5

seo_settings
  └─ og_default_image_id → media (SET NULL), page_type UNIQUE
```

**Deletion Behavior:**
- **SET NULL:** Optional references (media, category, uploaded_by, parent_id) — preserves main content if related deleted
- **CASCADE:** Dependent mappings/sections (page_sections, project_technologies, project_features, blog_post_tags) — delete children when parent deleted
- **No destructive CASCADE** for important content (e.g., deleting category does NOT delete posts, only sets NULL)

---

## 4. Tables Created (17)

1. **users** — admin auth, bcrypt, email UNIQUE, role admin/editor, is_active, last_login
2. **media** — filename sanitized unique, original_filename, file_path, file_url, mime, extension, file_size, width, height, alt_text, title, uploaded_by FK users SET NULL
3. **site_settings** — site_name, site_title, domain, description, portfolio_url, email, phone, phone_href, whatsapp, address, location, 11 logo/favicon FKs to media SET NULL, social_links JSON, timestamps
4. **navigation_items** — label, url, order_index, is_visible, parent_id self FK SET NULL
5. **pages** — slug UNIQUE (home, about, services, etc.), title, meta_title, meta_description, canonical_url, og_title/desc/image_id, twitter_title/desc/image_id, robots default index,follow, is_visible
6. **page_sections** — page_id FK CASCADE, section_key, content_json JSON, order_index, is_visible
7. **services** — slug UNIQUE, title, description, icon_key (palette, code, etc.), order_index, is_visible
8. **projects** — slug UNIQUE (must remain unchanged), title, category, duration, cost, role, overview TEXT, live_url, github_url, client_name, project_date, art_media_id FK SET NULL, is_featured, is_visible, order_index
9. **project_technologies** — project_id FK CASCADE, technology VARCHAR(100), UNIQUE(project_id, technology)
10. **project_features** — project_id FK CASCADE, feature TEXT, order_index
11. **blog_categories** — slug UNIQUE, name UNIQUE, description
12. **blog_tags** — slug UNIQUE, name UNIQUE
13. **blog_posts** — CRITICAL: original_id UNIQUE (from JSON), slug UNIQUE (must remain unchanged for SEO), title, excerpt, content LONGTEXT (full HTML, DO NOT shorten), featured_image_id FK SET NULL, featured_image_alt, author, published_date DATE, published_date_iso DATETIME, category_id FK SET NULL, reading_time, word_count, meta_title, meta_description, canonical_url, og_title/desc/image_id, twitter_title/desc/image_id, robots, status draft/published, is_visible, timestamps
14. **blog_post_tags** — M2M, PK (post_id, tag_id), CASCADE both
15. **contact_messages** — form_type contact/booking, name, email, phone, subject, message TEXT, agenda JSON, preferred_date, budget, honeypot_field (bot trap), ip_address, user_agent, is_read, status new/read/replied/archived, timestamps
16. **testimonials** — client_name, role_company, testimonial TEXT, image_id FK SET NULL, rating CHECK 1-5, is_visible, order_index
17. **seo_settings** — page_type UNIQUE (global, home, blog, etc.), og_default_image_id FK SET NULL, twitter_card_type, robots_default, json_ld_extra JSON

---

## 5. Indexes & Constraints

- **Primary Keys:** INT UNSIGNED AUTO_INCREMENT for all except blog_post_tags (composite PK)
- **Unique:** users.email, pages.slug, services.slug, projects.slug, blog_categories.slug/name, blog_tags.slug/name, blog_posts.slug/original_id, project_technologies (project_id, technology), seo_settings.page_type
- **Foreign Keys:** All FKs with explicit names fk_*, ON DELETE SET NULL for optional, CASCADE for dependent, ON UPDATE CASCADE
- **Indexes:** order_index, is_visible, status, category, published_date_iso DESC, created_at DESC, email, etc. for performance
- **Checks:** testimonials.rating 1-5 via CHECK constraint (MySQL 8+ / MariaDB 10.6+)
- **Charset:** All tables InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci, SET NAMES utf8mb4

---

## 6. Migration Considerations (For Milestone 2, not now)

- **Do NOT migrate yet** per Milestone 1 rules — schema only
- **Future migration will:**
  - Parse `site_src/content.json` → site_settings (single row), navigation_items, pages, services, projects, project_technologies, project_features
  - Parse `site_src/blog_posts_real.json` → blog_categories (4 unique), blog_tags (~20 unique), media (scan assets/img/blog/*.webp, getimagesize), blog_posts (preserve original_id, slug unchanged, full content HTML, reading_time, word_count, meta, canonical), blog_post_tags M2M
  - Preserve slugs exactly: website-speed-optimization, benefits-of-responsive-web-design, wordpress-website-development-is-a-smart-choice, benefits-of-a-professional-business-website
  - Preserve canonical URLs https://mashzidultanun.com/{slug}/
  - Media: Keep existing assets/img/blog path for old posts to avoid broken images, new uploads go to uploads/
  - Internal links: Keep rewrite_internal_links mapping
  - Sitemap: Regenerate from DB

---

## 7. Security Considerations

- **Users:** password_hash VARCHAR(255) for bcrypt $2y$, never plaintext, email UNIQUE, is_active flag, role enum
- **No hardcoded creds:** No admin email/password in schema.sql, only empty seeds; admin creation via separate tool in Milestone 3
- **SQL Injection:** Future backend must use PDO prepared statements (schema supports)
- **XSS:** Blog content LONGTEXT will be purified via HTMLPurifier on save (allowlist h2/h3/p/ul/ol/a/strong etc.)
- **File Uploads:** media table stores sanitized filename, mime, extension allowlist check in PHP, width/height, file_size, alt_text; .htaccess will deny php execution in uploads/
- **Path Traversal:** file_path validated in PHP, no ../, basename
- **Config:** DB creds in config.php outside public_html, 600 perms, .gitignore, not in repo
- **Timestamps:** created_at/updated_at for audit trail

---

## 8. Required Versions

- **PHP:** 8.2+ (8.3 recommended) — needs PDO, pdo_mysql, GD or Imagick, mbstring, fileinfo, openssl, json, curl
- **MySQL:** 8.0+ or **MariaDB:** 10.6+ — JSON column support, CHECK constraints, utf8mb4_unicode_ci
- **Apache:** 2.4+ with mod_rewrite enabled
- **cPanel:** Any standard cPanel with MySQL Databases wizard + phpMyAdmin + Select PHP Version

---

## 9. Validation Performed

- [x] SQL syntax checked manually, SET FOREIGN_KEY_CHECKS 0/1, consistent charset/collation
- [x] Every FK has matching PK type INT UNSIGNED, explicit constraint name, ON DELETE SET NULL or CASCADE as per audit
- [x] Every UNIQUE: email, slugs, names, page_type, (project_id, technology), (post_id, tag_id)
- [x] Every index: order_index, is_visible, status, category, published dates DESC, created_at DESC, email
- [x] Charset/collation consistent utf8mb4_unicode_ci on all tables
- [x] Deletion behavior: SET NULL for optional (media, category, uploaded_by, parent), CASCADE for dependent (sections, technologies, features, post_tags)
- [x] Blog slugs can remain unchanged: slug VARCHAR(255) UNIQUE, original_id preserved
- [x] Project slugs can remain unchanged: slug UNIQUE
- [x] Supports future admin CRUD: All tables have is_visible, order_index, status, timestamps, FKs
- [x] Supports cPanel deployment: InnoDB, utf8mb4, no exotic features, JSON supported in MySQL 8/MariaDB 10.6

---

## 10. Files Created

- `database/schema.sql` — production-ready schema, 17 tables, seeds for site_settings/pages/navigation/blog_categories/seo_settings
- `database/README.md` — this file

## Files Modified

- None — per Milestone 1 rules, existing assets, content.json, blog_posts_real.json, HTML, CSS, JS, build.py untouched

---

## 11. Next Steps (Milestone 2, awaiting approval)

- Do NOT auto-continue
- Milestone 2 will be: Migration scripts (tools/migrate.php) to import JSON + media into DB, preserving complete blog content and URLs
- Stop after Milestone 1, wait for approval

---

**End of Milestone 1**
