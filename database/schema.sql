-- =============================================================================
-- MTB Portfolio — CMS Database Schema
-- Milestone 1: Database Foundation Only
-- Target: cPanel / MySQL 8.0+ / MariaDB 10.6+ / PHP 8.2+
-- Engine: InnoDB / Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
-- Author: Audit approved architecture, no data migration in this milestone
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ---------------------------------------------------------------------------
-- USERS — admin authentication, bcrypt, no plaintext
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'bcrypt $2y$ or argon2id',
  `name` VARCHAR(255) DEFAULT NULL,
  `role` ENUM('admin','editor') NOT NULL DEFAULT 'admin',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_is_active` (`is_active`),
  KEY `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin users, bcrypt hashes only';

-- ---------------------------------------------------------------------------
-- MEDIA — supports WebP, JPG, PNG, SVG, sanitized filenames, variants
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `media` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename` VARCHAR(255) NOT NULL COMMENT 'Sanitized unique filename e.g. 65f3a-website-speed-optimization-480.webp',
  `original_filename` VARCHAR(255) DEFAULT NULL COMMENT 'Original client filename',
  `file_path` VARCHAR(500) NOT NULL COMMENT 'Relative path e.g. uploads/2026/09/filename.webp',
  `file_url` VARCHAR(500) NOT NULL COMMENT 'Public URL e.g. /uploads/2026/09/filename.webp',
  `mime_type` VARCHAR(100) NOT NULL COMMENT 'image/webp, image/jpeg, image/png, image/svg+xml',
  `extension` VARCHAR(20) NOT NULL COMMENT 'webp, jpg, png, svg',
  `file_size` INT UNSIGNED NOT NULL COMMENT 'Bytes',
  `width` INT UNSIGNED DEFAULT NULL,
  `height` INT UNSIGNED DEFAULT NULL,
  `alt_text` VARCHAR(255) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `uploaded_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_media_extension` (`extension`),
  KEY `idx_media_mime` (`mime_type`),
  KEY `idx_media_uploaded_by` (`uploaded_by`),
  KEY `idx_media_created_at` (`created_at`),
  CONSTRAINT `fk_media_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Media library, supports existing blog WebP assets';

-- ---------------------------------------------------------------------------
-- SITE_SETTINGS — global values editable from future admin
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_name` VARCHAR(255) NOT NULL DEFAULT 'Mashzidul Tanun Borshon',
  `site_title` VARCHAR(255) NOT NULL DEFAULT 'Web Designer & Full Stack Web Developer',
  `site_domain` VARCHAR(255) NOT NULL DEFAULT 'https://mashzidultanun.com' COMMENT 'Canonical domain, no trailing slash',
  `site_description` TEXT DEFAULT NULL COMMENT 'Meta description global',
  `portfolio_url` VARCHAR(255) DEFAULT NULL COMMENT 'Portfolio URL if separate',
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `phone_href` VARCHAR(50) DEFAULT NULL,
  `whatsapp` VARCHAR(50) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  -- Logo & favicon references to media (SET NULL if media deleted)
  `logo_combination_id` INT UNSIGNED DEFAULT NULL,
  `logo_lettermark_id` INT UNSIGNED DEFAULT NULL,
  `logo_pictorial_id` INT UNSIGNED DEFAULT NULL,
  `logo_emblem_id` INT UNSIGNED DEFAULT NULL,
  `logo_wordmark_id` INT UNSIGNED DEFAULT NULL,
  `logo_abstract_id` INT UNSIGNED DEFAULT NULL,
  `mascot_id` INT UNSIGNED DEFAULT NULL,
  `favicon_32_id` INT UNSIGNED DEFAULT NULL,
  `favicon_64_id` INT UNSIGNED DEFAULT NULL,
  `apple_touch_id` INT UNSIGNED DEFAULT NULL,
  `og_default_image_id` INT UNSIGNED DEFAULT NULL,
  -- Social links JSON preserves current content.json structure
  `social_links` JSON DEFAULT NULL COMMENT '[{"platform":"Facebook","label":"Facebook","url":"https://...","icon":"facebook"}]',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_site_logo_combination` FOREIGN KEY (`logo_combination_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_site_logo_lettermark` FOREIGN KEY (`logo_lettermark_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_site_logo_pictorial` FOREIGN KEY (`logo_pictorial_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_site_logo_emblem` FOREIGN KEY (`logo_emblem_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_site_logo_wordmark` FOREIGN KEY (`logo_wordmark_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_site_logo_abstract` FOREIGN KEY (`logo_abstract_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_site_mascot` FOREIGN KEY (`mascot_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_site_favicon_32` FOREIGN KEY (`favicon_32_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_site_favicon_64` FOREIGN KEY (`favicon_64_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_site_apple_touch` FOREIGN KEY (`apple_touch_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_site_og_default` FOREIGN KEY (`og_default_image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Global site settings, single row id=1';

-- ---------------------------------------------------------------------------
-- NAVIGATION_ITEMS — menu items, order, visibility, parent for future dropdown
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `navigation_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(100) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `order_index` INT NOT NULL DEFAULT 0,
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nav_order` (`order_index`),
  KEY `idx_nav_visible` (`is_visible`),
  KEY `idx_nav_parent` (`parent_id`),
  CONSTRAINT `fk_nav_parent` FOREIGN KEY (`parent_id`) REFERENCES `navigation_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Navigation menu, self-referencing parent for dropdowns';

-- ---------------------------------------------------------------------------
-- PAGES — home, about, contact, etc. with SEO fields
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(255) NOT NULL COMMENT 'home, about, services, projects, pricing, blog, contact, faq, booking, terms, privacy',
  `title` VARCHAR(255) DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT DEFAULT NULL,
  `canonical_url` VARCHAR(255) DEFAULT NULL,
  `og_title` VARCHAR(255) DEFAULT NULL,
  `og_description` TEXT DEFAULT NULL,
  `og_image_id` INT UNSIGNED DEFAULT NULL,
  `twitter_title` VARCHAR(255) DEFAULT NULL,
  `twitter_description` TEXT DEFAULT NULL,
  `twitter_image_id` INT UNSIGNED DEFAULT NULL,
  `robots` VARCHAR(100) NOT NULL DEFAULT 'index, follow' COMMENT 'index, follow / noindex, nofollow etc',
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pages_slug` (`slug`),
  KEY `idx_pages_visible` (`is_visible`),
  CONSTRAINT `fk_pages_og_image` FOREIGN KEY (`og_image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pages_twitter_image` FOREIGN KEY (`twitter_image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Static pages with SEO';

-- ---------------------------------------------------------------------------
-- PAGE_SECTIONS — flexible sections per page (hero, stats, etc.)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `page_sections` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id` INT UNSIGNED NOT NULL,
  `section_key` VARCHAR(100) NOT NULL COMMENT 'hero, why_choose, stats, services, about, skills, portfolio, pricing, blog_latest, cta',
  `content_json` JSON NOT NULL COMMENT 'Flexible JSON for section content',
  `order_index` INT NOT NULL DEFAULT 0,
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pagesections_page` (`page_id`),
  KEY `idx_pagesections_key` (`section_key`),
  KEY `idx_pagesections_order` (`order_index`),
  CONSTRAINT `fk_pagesections_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Page sections, CASCADE delete with page';

-- ---------------------------------------------------------------------------
-- SERVICES — title, slug, desc, icon, order, visibility
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `icon_key` VARCHAR(50) DEFAULT NULL COMMENT 'palette, code, globe, cart, monitor, refresh, wrench, gauge, search, bug',
  `order_index` INT NOT NULL DEFAULT 0,
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_services_slug` (`slug`),
  KEY `idx_services_visible` (`is_visible`),
  KEY `idx_services_order` (`order_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Services, 10 currently';

-- ---------------------------------------------------------------------------
-- PROJECTS — title, slug, category, featured image, URLs, client, date, featured, visibility, order
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `duration` VARCHAR(50) DEFAULT NULL,
  `cost` VARCHAR(50) DEFAULT NULL,
  `role` VARCHAR(255) DEFAULT NULL,
  `overview` TEXT DEFAULT NULL COMMENT 'Short overview + full description',
  `live_url` VARCHAR(255) DEFAULT NULL COMMENT 'Project URL',
  `github_url` VARCHAR(255) DEFAULT NULL COMMENT 'GitHub URL',
  `client_name` VARCHAR(255) DEFAULT NULL,
  `project_date` DATE DEFAULT NULL,
  `art_media_id` INT UNSIGNED DEFAULT NULL COMMENT 'Featured image / SVG art',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `order_index` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_projects_slug` (`slug`),
  KEY `idx_projects_category` (`category`),
  KEY `idx_projects_visible` (`is_visible`),
  KEY `idx_projects_featured` (`is_featured`),
  KEY `idx_projects_order` (`order_index`),
  CONSTRAINT `fk_projects_art_media` FOREIGN KEY (`art_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Portfolio projects, 4 currently';

-- ---------------------------------------------------------------------------
-- PROJECT_TECHNOLOGIES — many-to-many normalized, CASCADE
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_technologies` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` INT UNSIGNED NOT NULL,
  `technology` VARCHAR(100) NOT NULL COMMENT 'HTML, CSS, JavaScript, React, etc.',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_proj_tech` (`project_id`, `technology`),
  KEY `idx_projtech_project` (`project_id`),
  KEY `idx_projtech_tech` (`technology`),
  CONSTRAINT `fk_projtech_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Project technologies M2M, CASCADE';

-- ---------------------------------------------------------------------------
-- PROJECT_FEATURES — dependent features, CASCADE
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_features` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` INT UNSIGNED NOT NULL,
  `feature` TEXT NOT NULL,
  `order_index` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_projfeat_project` (`project_id`),
  KEY `idx_projfeat_order` (`order_index`),
  CONSTRAINT `fk_projfeat_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Project features, CASCADE';

-- ---------------------------------------------------------------------------
-- BLOG_CATEGORIES — Performance, Web Design, Development, Business
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blogcat_slug` (`slug`),
  UNIQUE KEY `uq_blogcat_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Blog categories';

-- ---------------------------------------------------------------------------
-- BLOG_TAGS — website speed optimization, SEO, etc.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_tags` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blogtag_slug` (`slug`),
  UNIQUE KEY `uq_blogtag_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Blog tags';

-- ---------------------------------------------------------------------------
-- BLOG_POSTS — critical, supports original_id, slug unchanged, full HTML content
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `original_id` VARCHAR(100) DEFAULT NULL COMMENT 'Original id from JSON e.g. website-speed-optimization',
  `slug` VARCHAR(255) NOT NULL COMMENT 'Must remain unchanged for SEO: website-speed-optimization etc.',
  `title` VARCHAR(255) NOT NULL,
  `excerpt` TEXT DEFAULT NULL,
  `content` LONGTEXT NOT NULL COMMENT 'Full HTML content, DO NOT shorten',
  `featured_image_id` INT UNSIGNED DEFAULT NULL COMMENT 'FK to media, replaces base name',
  `featured_image_alt` VARCHAR(255) DEFAULT NULL,
  `author` VARCHAR(255) NOT NULL DEFAULT 'Mashzidul Tanun Borshon',
  `published_date` DATE DEFAULT NULL COMMENT 'YYYY-MM-DD',
  `published_date_iso` DATETIME DEFAULT NULL COMMENT 'ISO8601 e.g. 2026-09-17T03:27:41',
  `category_id` INT UNSIGNED DEFAULT NULL,
  `reading_time` INT UNSIGNED DEFAULT NULL COMMENT 'Minutes',
  `word_count` INT UNSIGNED DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT DEFAULT NULL,
  `canonical_url` VARCHAR(255) DEFAULT NULL COMMENT 'https://mashzidultanun.com/{slug}/',
  `og_title` VARCHAR(255) DEFAULT NULL,
  `og_description` TEXT DEFAULT NULL,
  `og_image_id` INT UNSIGNED DEFAULT NULL,
  `twitter_title` VARCHAR(255) DEFAULT NULL,
  `twitter_description` TEXT DEFAULT NULL,
  `twitter_image_id` INT UNSIGNED DEFAULT NULL,
  `robots` VARCHAR(100) NOT NULL DEFAULT 'index, follow',
  `status` ENUM('draft','published') NOT NULL DEFAULT 'published',
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blogposts_slug` (`slug`),
  UNIQUE KEY `uq_blogposts_original_id` (`original_id`),
  KEY `idx_blogposts_category` (`category_id`),
  KEY `idx_blogposts_status` (`status`),
  KEY `idx_blogposts_visible` (`is_visible`),
  KEY `idx_blogposts_published_iso` (`published_date_iso` DESC),
  KEY `idx_blogposts_published_date` (`published_date` DESC),
  CONSTRAINT `fk_blogposts_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_blogposts_featured_image` FOREIGN KEY (`featured_image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_blogposts_og_image` FOREIGN KEY (`og_image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_blogposts_twitter_image` FOREIGN KEY (`twitter_image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Blog posts, critical, preserve complete content';

-- ---------------------------------------------------------------------------
-- BLOG_POST_TAGS — M2M, CASCADE both sides
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_post_tags` (
  `post_id` INT UNSIGNED NOT NULL,
  `tag_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`, `tag_id`),
  KEY `idx_blogposttags_tag` (`tag_id`),
  CONSTRAINT `fk_blogposttags_post` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_blogposttags_tag` FOREIGN KEY (`tag_id`) REFERENCES `blog_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Blog post tags M2M, CASCADE';

-- ---------------------------------------------------------------------------
-- CONTACT_MESSAGES — name, email, phone, subject, message, form_type, IP, read/unread, status
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `form_type` ENUM('contact','booking') NOT NULL DEFAULT 'contact',
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `agenda` JSON DEFAULT NULL COMMENT 'For booking: ["Project Requirements", ...]',
  `preferred_date` DATE DEFAULT NULL,
  `budget` VARCHAR(100) DEFAULT NULL,
  `honeypot_field` VARCHAR(255) DEFAULT NULL COMMENT 'Should be empty, bot trap',
  `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IPv4/IPv6',
  `user_agent` TEXT DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('new','read','replied','archived') NOT NULL DEFAULT 'new',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contact_form_type` (`form_type`),
  KEY `idx_contact_is_read` (`is_read`),
  KEY `idx_contact_status` (`status`),
  KEY `idx_contact_created` (`created_at` DESC),
  KEY `idx_contact_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contact & booking messages';

-- ---------------------------------------------------------------------------
-- TESTIMONIALS — future, client name, role/company, testimonial, image, rating, visibility, order
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_name` VARCHAR(255) NOT NULL,
  `role_company` VARCHAR(255) DEFAULT NULL,
  `testimonial` TEXT NOT NULL,
  `image_id` INT UNSIGNED DEFAULT NULL,
  `rating` TINYINT UNSIGNED DEFAULT NULL COMMENT '1-5',
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `order_index` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_testimonials_visible` (`is_visible`),
  KEY `idx_testimonials_order` (`order_index`),
  CONSTRAINT `fk_testimonials_image` FOREIGN KEY (`image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_testimonials_rating` CHECK (`rating` IS NULL OR (`rating` >= 1 AND `rating` <= 5))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Testimonials, future use';

-- ---------------------------------------------------------------------------
-- SEO_SETTINGS — global + per page_type OG/Twitter/robots/json_ld
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `seo_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_type` VARCHAR(100) NOT NULL COMMENT 'global, home, about, services, projects, blog, contact, etc.',
  `og_default_image_id` INT UNSIGNED DEFAULT NULL,
  `twitter_card_type` VARCHAR(50) NOT NULL DEFAULT 'summary_large_image',
  `robots_default` VARCHAR(100) NOT NULL DEFAULT 'index, follow',
  `json_ld_extra` JSON DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seo_pagetype` (`page_type`),
  CONSTRAINT `fk_seo_og_image` FOREIGN KEY (`og_default_image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Global SEO settings';

SET FOREIGN_KEY_CHECKS=1;

-- =============================================================================
-- INITIAL DATA SEEDS (optional, minimal)
-- =============================================================================
-- Default site_settings row (id=1) — will be populated via migration later, but create empty for FK safety
INSERT INTO `site_settings` (`id`, `site_name`, `site_title`, `site_domain`, `site_description`, `email`, `phone`, `location`) VALUES
(1, 'Mashzidul Tanun Borshon', 'Web Designer & Full Stack Web Developer', 'https://mashzidultanun.com', 'Mashzidul Tanun Borshon is a web designer and full stack web developer in Khulna, Bangladesh. He builds modern, responsive, fast and SEO-optimized websites for businesses.', 'mail@mashzidultanun.com', '+8801330132141', 'Khulna, Bangladesh')
ON DUPLICATE KEY UPDATE `site_name`=VALUES(`site_name`);

-- Default pages (slugs must match current routes)
INSERT INTO `pages` (`slug`, `title`, `meta_title`, `is_visible`) VALUES
('home', 'Home', 'Mashzidul Tanun Borshon — Web Designer & Full Stack Web Developer', 1),
('about', 'About', 'About | Mashzidul Tanun Borshon', 1),
('services', 'Services', 'Services | Mashzidul Tanun Borshon', 1),
('projects', 'Projects', 'Projects | Mashzidul Tanun Borshon', 1),
('pricing', 'Pricing', 'Pricing | Mashzidul Tanun Borshon', 1),
('blog', 'Blog', 'Blog | Mashzidul Tanun Borshon', 1),
('contact', 'Contact', 'Contact | Mashzidul Tanun Borshon', 1),
('faq', 'FAQ', 'FAQ | Mashzidul Tanun Borshon', 1),
('booking', 'Book an Appointment', 'Book an Appointment | Mashzidul Tanun Borshon', 1),
('terms', 'Terms & Conditions', 'Terms & Conditions | Mashzidul Tanun Borshon', 1),
('privacy', 'Privacy Policy', 'Privacy Policy | Mashzidul Tanun Borshon', 1)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

-- Default navigation (matches current NAV)
INSERT INTO `navigation_items` (`label`, `url`, `order_index`, `is_visible`) VALUES
('Home', '/', 1, 1),
('About', '/about/', 2, 1),
('Services', '/services/', 3, 1),
('Projects', '/projects/', 4, 1),
('Pricing', '/pricing/', 5, 1),
('Blog', '/blog/', 6, 1),
('Contact', '/contact/', 7, 1)
ON DUPLICATE KEY UPDATE `label`=VALUES(`label`);

-- Default blog categories from current 4 posts
INSERT INTO `blog_categories` (`slug`, `name`) VALUES
('performance', 'Performance'),
('web-design', 'Web Design'),
('development', 'Development'),
('business', 'Business')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Default SEO global
INSERT INTO `seo_settings` (`page_type`, `twitter_card_type`, `robots_default`) VALUES
('global', 'summary_large_image', 'index, follow')
ON DUPLICATE KEY UPDATE `page_type`=VALUES(`page_type`);

-- =============================================================================
-- END OF SCHEMA
-- =============================================================================
