# MTB Portfolio Website — Complete Implementation Plan
**Client:** Mashzidul Tanun Borshon — Web Designer & Full Stack Web Developer
**Reference theme:** https://reeni-wp.laralink.com/ (WordPress/Elementor personal-portfolio theme, dark demo)
**Source of truth for content:** `uploads/portfilio website content.pdf` (15 pages)
**Status:** ANALYSIS + PLAN ONLY — no build started. Awaiting client instruction.

---

## 0. Analysis Summary

### 0.1 Sources analyzed
| Source | What was extracted |
|---|---|
| `portfilio website content.pdf` (15 pp.) | Full site copy: Home hero, About + personal info, Skills (13), Services (10), Portfolio (4 projects with full case-study data), Pricing (3 packages), Blog (8 titles), FAQ (6 Q&A), Contact info, Terms (7 clauses), Privacy Policy, Booking agenda |
| 7 × PNG brand assets | Logo system, gold gradient values (pixel-sampled), transparency/usage constraints |
| reeni-wp.laralink.com | IA (nav + 8 page templates), 13 homepage sections, header/off-canvas menu, footer structure, fonts (Rajdhani + Rubik), motion stack (Swiper, GSAP, WOW reveal), breadcrumb pattern, dark visual language |

### 0.2 Reference site (Reeni) — patterns adopted
- **Dark, high-contrast personal portfolio** with one accent color, giant display type, numbered service rows, marquee/ticker strips.
- **Header:** logo left, horizontal nav with dropdowns, off-canvas side menu containing a short self-intro + contact info.
- **Homepage rhythm:** Hero → stat/service strip → skills (bars) → numbered services accordion → education/experience → logo marquee → portfolio grid → skill cards → testimonial slider → CTA band → blog cards → footer CTA.
- **Subpages:** breadcrumb hero (`Home > Page`), then template body; portfolio detail = gallery + "Project Details" sidebar; contact = info cards + form; footer on every page with Quick Links + Contact column + socials + copyright bar.
- **Motion:** swiper carousels, scroll-reveal (WOW/GSAP), marquee tickers, hover lifts.
- **Deviations from Reeni (deliberate):** no testimonials section, no counters/stats, no skill percentage bars, no client-logo marquee — the PDF contains **no data** for these and we do not invent content (see §16 Open Questions).

### 0.3 Brand asset audit
| File | Px | Mode | Visual | Assigned role |
|---|---|---|---|---|
| `MTB_Lettermark_Gold-Transparent.png` | 2212×1492 | RGBA ✓ | "MTB" monogram | Header logo (with wordmark), favicon source, preloader |
| `MTB_Wordmark_Gold-Transparent.png` | 3520×1074 | RGBA ✓ | Name + tagline, 2 lines | Header (paired w/ lettermark), footer brand block |
| `MTB_Combination_Gold-Transparent.png` | 4736×1066 | RGBA ✓ | Monogram + rule + name + tagline, 1 line | Footer primary logo, OG/fallback lockup, print/export |
| `MTB_Emblem_Gold-Transparent.png` | 2728×2712 | RGBA ✓ | Circular seal w/ name ring + `</>` | About page seal, footer badge, OG image, booking page accent |
| `MTB_Abstract_Gold-Transparent.png` | 1802×1958 | RGBA ✓ | Hexagonal abstract monogram | Decorative watermark (low opacity), section backgrounds, 404 |
| `MTB_Pictorial_Gold-Transparent.png` | 1926×1592 | RGBA ✓ | Code-bracket `</>` mark | Developer/services accent, empty-state & placeholder art, favicon alt |
| `MTB_Mascot_Vector-Color.png` | 1344×1155 | **RGB ✗ (black bg baked in)** | Illustrated portrait w/ laptop | **Secondary illustration accent only** (404 / empty states / booking decor) — superseded as personal image by official photo (§10.1) |
| `Mashzidul Tanun Borshon Portrait backgroud removed (2).png` | 1114×1412 | RGBA ✓ (43 % transparent) | **Official photo** — half-body, arms crossed, true cutout | **PRIMARY personal image → Hero** (`mtb-portrait-hero`) |
| `Mashzidul Tanun Borshon Picture backgroud removed.png` | 1114×809 | RGBA ✓ (61 % transparent) | **Official photo** — head-and-shoulders cutout | **About framed photo**, secondary personal spots (`mtb-portrait-headshot`) |
| `Mashzidul Tanun Borshon Picture.png` | 388×388 | RGBA, alpha opaque (dark studio bg) | **Official photo** — square studio shot | **Avatar / circular thumb only ≤128 px** (low-res master) (`mtb-avatar-square`) |

**Asset constraints (must respect):**
1. All marks are **gold-on-transparent** → site background must be dark; a light theme is impossible without new artwork (none provided) → **dark-only theme**.
2. Mascot has **no alpha channel** (near-black `#080807` background baked in) → place only on near-black surfaces, or mask inside a circle/arch shape; never on cards lighter than `#0A0A09`.
3. Source PNGs are oversized for web (up to 4736 px wide) → generate optimized WebP/AVIF + sized PNG fallbacks at build time (see §15).
4. **Official photography directive (client, binding):** no AI-generated replacement person · no facial-identity alteration · no distortion · no AI art styling · preserve original quality & proportions · composition must work at desktop/tablet/mobile · **no text over the face** · styling must stay within the black-&-gold brand language.
5. Photo masters max **1114 px wide** → never upscale; hero renders ≤ 560 CSS px (≥2× covered). Square master is **388 px** → avatar use only (≤128 px render).
6. Photo cutouts carry **true alpha** → sit directly on `--bg`; no matte, no box-shadow on the person; all framing (gold ring/arch/glow) renders *behind* the cutout layer.
7. Web derivatives already generated (lossless-proportion downscales only, no crops, no filters): `site/assets/img/portrait/` → `mtb-portrait-hero{-1114,-720,-480}.webp` (134/75/41 KB) + PNG master, `mtb-portrait-headshot{-1114,-720,-480}.webp` (72/38/21 KB) + PNG master, `mtb-avatar-square{-388,-194}.webp` (13/5 KB) + PNG master.

### 0.4 Gold values sampled from the assets (basis of the color system)
`#F3CC6C` / `#F4C76A` (light gold) · `#ECBD61` / `#EAB85D` (mid gold) · `#E09E57` (warm gold) · `#A76B20` (deep bronze, gradient end) — on `#000000`–`#080807` backgrounds.

---

## 1. Required Pages
| # | Route | File | Purpose | PDF source |
|---|---|---|---|---|
| 1 | `/` | `index.html` | Homepage (12 sections, §2) | p.1–3, 9–11 |
| 2 | `/about/` | `about.html` | About Me, personal info, skills, emblem | p.1–2 |
| 3 | `/services/` | `services.html` | 10 services, detailed cards + process CTA | p.2–3 |
| 4 | `/portfolio/` | `portfolio.html` | Project grid (4) + category filter | p.3 |
| 5 | `/portfolio/seo-agency-website/` | case-study ×4 | Full case study per project (overview, role, tech, features, meta) | p.3–9 |
| 6 | `/portfolio/web-hosting-company-website/` | ″ |  | p.5–6 |
| 7 | `/portfolio/creative-agency-portfolio/` | ″ | ″ | p.6–7 |
| 8 | `/portfolio/digital-creative-studio/` | ″ | ″ | p.8–9 |
| 9 | `/pricing/` | `pricing.html` | 3 packages + comparison notes | p.9–10 |
| 10 | `/blog/` | `blog.html` | Article index (8 titles; bodies pending — §16 Q6) | p.11 |
| 11 | `/contact/` | `contact.html` | Contact info + form + socials + map-less location block | p.12–13 |
| 12 | `/booking/` | `booking.html` | "Schedule a Consultation" + agenda + Book Now | p.14–15 |
| 13 | `/terms/` | `terms.html` | Terms & Conditions (7 clauses) | p.13 |
| 14 | `/privacy/` | `privacy.html` | Privacy Policy | p.14 |
| 15 | `/404` | `404.html` | Branded not-found (Abstract mark) | — (structural) |

**FAQ placement decision (proposed):** FAQ accordion lives as a **homepage section** (§2 S9) *and* is repeated on `/contact/`; no standalone `/faq/` page (matches reference-site IA). Alternative (standalone page) flagged as Q11.

---

## 2. Homepage Sections (in order)
| # | Section | Content (PDF-verbatim where quoted) | Component |
|---|---|---|---|
| S1 | **Hero** | Eyebrow `Hi, I'm` · H1 `Mashzidul Tanun Borshon` · gold-gradient role line `Web Designer & Full Stack Web Developer` · lead paragraph ("I build modern, responsive websites…") · primary CTA `Let's Build Something Great Together` → `/contact/` · secondary CTA `View My Work` → `/portfolio/` · **"Why Choose Me" checklist card** (5 bullets) · right column: **official photo cutout `mtb-portrait-hero`** (arms-crossed, true alpha) inside gold arch frame + radial glow (§10.1); checklist card overlaps only the frame's bottom-left corner (face-safe zone §10.1) | `hero`, `checklist-card`, `portrait-frame` |
| S2 | **Skills marquee** | Two opposite-direction tickers with the 13 skills (HTML, CSS, JavaScript, React, Node.js, Express.js, MongoDB, WordPress, Elementor, Bootstrap, Tailwind CSS, SEO, Git & GitHub) separated by `</>` pictogram | `marquee` |
| S3 | **About preview** | Eyebrow `About` · H2 `About Me` · both About paragraphs · 4-item info strip (Nationality, Study, Degree, Location) · link `More About Me →` · **framed official head-and-shoulders photo (`mtb-portrait-headshot`)** as section visual with Emblem as small corner seal on the frame (§10.1) | `section-head`, `info-strip`, `portrait-frame` |
| S4 | **Services (numbered rows)** | Eyebrow `Services` · H2 `What I Offer` · accordion rows `01–10` with the 10 service names + one-line descriptor derived **only** from service name (no invented copy; row expands to show related skills chips) · CTA `View All Services` | `service-row` accordion |
| S5 | **Portfolio preview** | Eyebrow `Portfolio` · H2 `My Work` · intro paragraph (PDF p.3 verbatim) · 2×2 project cards (title + category + duration) · CTA `View All Projects` | `project-card` |
| S6 | **Pricing preview** | Eyebrow `Pricing` · H2 `Choose Your Package` · 3 tier cards (Basic/Standard/Premium, feature bullets, "Starting From $X") · highlight middle card · CTA → `/pricing/` | `price-card` |
| S7 | **Process / Why-work-with-me band** | Uses PDF "Why Choose Me" 5 bullets as gold icon tiles (Responsive & Mobile-Friendly · Fast & SEO Optimized · Clean, Secure Code · Modern UI/UX Design · Reliable Support) over Abstract-mark watermark | `value-tile` |
| S8 | **Blog preview** | Eyebrow `Blog` · H2 `Latest Articles` · 3 newest titles as cards (date/category **pending** — Q6; cards show title + "Read soon" state until bodies exist) · CTA `All Articles` | `blog-card` |
| S9 | **FAQ accordion** | H2 `Frequently Asked Questions` · 6 Q&A verbatim | `faq-item` |
| S10 | **Testimonials** | **OMITTED — no content in PDF** (Q3) | — |
| S11 | **CTA band** | `Let's Work Together` · "Have a project in mind? I'd love to hear about it." · buttons `Book a Free Consultation` → `/booking/`, `Contact Me` → `/contact/` | `cta-band` |
| S12 | **Footer** | §4 | `footer` |

---

## 3. Header Structure
```
[sticky header — transparent over hero, blurs+solidifies on scroll]
 ├─ Logo zone: Lettermark (40px h) + Wordmark (hidden < 576px → lettermark only)
 ├─ Primary nav (≥992px): Home · About · Services · Portfolio ▾ (4 case links) · Pricing · Blog · Contact
 ├─ CTA button (gold): "Book a Consultation" → /booking/
 └─ Hamburger (<992px) → off-canvas panel:
      ├─ Nav list (large type, staggered slide-in)
      ├─ Intro snippet (PDF hero paragraph)
      ├─ Contact mini-block (email, phone)
      └─ Social row (icons; URLs pending Q1)
```
- Active-page link = gold underline/marker; dropdown = gold-bordered dark panel.
- Scroll behavior: height 88→64 px, `backdrop-filter: blur`, 1px gold-tinted bottom border.
- Off-canvas mirrors Reeni's side menu (self-intro + links) but with MTB content only.

## 4. Footer Structure
```
[pre-footer CTA strip]  "Let's Work Together" + Book/Contact buttons (marquee headline optional)
[main footer — 4 columns @ ≥992px]
 1. Brand: Combination lockup (h≈48px) + hero paragraph (short) + Emblem seal (small, right)
 2. Quick Links: Home, About, Services, Portfolio, Pricing, Blog, Contact, Booking
 3. Services: 5 anchor links → /services/ (#custom-website-design, #wordpress, #ecommerce, #seo, #maintenance)
 4. Contact: email (mailto), phone (tel), address (PDF verbatim), social icons (FB/IG/LinkedIn — URLs pending Q1; Fiverr/Upwork/Freelancer hidden until provided)
[bottom bar] © {year} Mashzidul Tanun Borshon · All Rights Reserved · Terms & Conditions · Privacy Policy
```

## 5. Components (inventory)
| Component | Used on | Notes |
|---|---|---|
| `btn` (primary gold-gradient / ghost gold-outline / text-arrow) | all | beveled corner clip-path, hover: lift + glow |
| `section-head` (eyebrow + H2 + gold rule) | all | eyebrow = uppercase, letterspaced, gold |
| `marquee` (skills / services ticker) | home, services | CSS keyframe, pause on hover, reduced-motion off |
| `service-row` (numbered accordion) | home, services | 01–10 numbering like Reeni |
| `service-card` (icon + name + chips) | services | pictogram/abstract as icon art |
| `project-card` (image area + title + meta) | home, portfolio | placeholder art = Pictorial/Abstract on black until screenshots (Q2) |
| `case-meta` sidebar (Category/Duration/Cost/Role/Tech) | case studies | Reeni "Project Details" pattern |
| `feature-list` (gold check bullets) | case studies, pricing | |
| `price-card` (tier, tagline, bullets, "Starting From $X", CTA) | home, pricing | middle = featured (gold border + badge) |
| `faq-item` (accordion) | home, contact | single-open behavior |
| `blog-card` (title + status) | home, blog | no fake dates/excerpts (Q6) |
| `info-list` (label: value rows) | about, contact | personal info block |
| `checklist-card` (Why Choose Me) | hero, home S7 | |
| `portrait-frame` (arch/hairline-gold frame + radial glow rendered **behind** the official photo cutout; face-safe overlay rules §10.1) | hero, about, booking | cutout sits directly on `--bg`; frame never in front of the person |
| `avatar` (circular photo w/ gold ring, 40–96 px) | contact, booking, off-canvas menu | from `mtb-avatar-square` (circle crops corners only → face-safe) |
| `breadcrumb` (Home > …) | all subpages | Reeni pattern |
| `form-field` (input/textarea/select + gold focus ring) | contact, booking | |
| `chip` (skill/tag pill) | about, services, case studies | |
| `back-to-top`, `preloader` (lettermark pulse) | global | |

## 6. Design System
- **Theme:** dark-only (gold-on-transparent assets; §0.3).
- **Tokens (CSS custom properties):**
  - Surfaces: `--bg:#060605` · `--surface-1:#0C0B09` · `--surface-2:#131109` · `--line:rgba(236,189,97,.16)` · `--line-strong:rgba(236,189,97,.32)`
  - Gold ramp: `--gold-100:#F8E7B4` · `--gold-300:#F4C76A` · `--gold-400:#F3CC6C` · `--gold-500:#ECBD61` · `--gold-600:#E09E57` · `--gold-700:#A76B20` · `--gold-900:#5E3D10`
  - Brand gradient: `linear-gradient(135deg,#F8E7B4 0%,#F3CC6C 28%,#ECBD61 52%,#C9964A 76%,#A76B20 100%)` (sampled §0.4)
  - Text: `--ink:#F4EFE4` · `--ink-muted:#B4AC9E` · on-gold: `#0B0A08`
  - Radii: `--r-sm:4px --r-md:8px`; **brand motif = beveled corner** (`clip-path: polygon(... 12px cut)`) on buttons/cards echoing logo angles
  - Spacing: 8-pt scale; section padding `clamp(72px,10vw,120px)`
  - Shadows: `--glow: 0 0 0 1px var(--line), 0 18px 50px -20px rgba(236,189,97,.25)`
- **Grid:** 12-col, container `min(1200px, 92vw)`; portfolio grid 2-col → 1-col mobile.
- **Visual language:** black canvas, gold gradient only for emphasis (H1/H2 keywords, buttons, rules, icons), hairline gold borders, uppercase letterspaced eyebrows, numbered rows, generous negative space, watermark abstract mark at 4–6% opacity.

## 7. Typography
- Logo lettering is a **custom geometric sans** (crossbar-less "A", wide tracking) — images only, never retyped.
- **Web pairing (proposal, mirrors reference stack & logo geometry):**
  - Display/headings: **Rajdhani** (600/700) — reference theme's display face; angular, techy, matches logo geometry; uppercase + `letter-spacing:.06em` for H2/eyebrows.
  - Body/UI: **Rubik** (400/500) — reference theme's body face; warm rounded sans, legible at 16–18 px.
  - Alt display candidate if client prefers wider geometry: **Sora** or **Montserrat 700** (tracked caps). *(Decision Q12.)*
- **Scale (fluid, clamp):** 12 / 14 / 16 / 18 / 20 / 24 / 32 / 40 / 52 / 68 / 88; H1 `clamp(40px,7vw,88px)`; line-heights 1.05 (display) / 1.6 (body); measure ≤ 68ch.

## 8. Color System
- **Primary:** gold gradient (§6) — CTAs, key headline words, icons, rules, active states.
- **Background:** near-black layers (§6) with subtle radial gold vignette in hero/CTA bands.
- **Text:** warm off-white primary, warm gray muted; **never** pure white.
- **Semantic:** success `#7FB069`, error `#E5484D` (forms only) — used sparingly on dark.
- **Contrast:** `#ECBD61` on `#060605` ≈ 10.9:1 ✓; `#F4EFE4` on `#060605` ≈ 17.8:1 ✓; on-gold text `#0B0A08` on `#ECBD61` ≈ 10.2:1 ✓ (WCAG AA/AAA).
- **No light mode** (asset constraint §0.3).

## 9. Logo Usage
| Context | Asset | Size rule |
|---|---|---|
| Header | Lettermark + Wordmark | h 40 px (scroll 32 px); wordmark hidden <576 px |
| Favicon / apple-touch | Lettermark (cropped square export) | 32/180 px |
| Footer | Combination | h 48 px |
| Hero/About accent | Emblem | ≤220 px, slow rotate optional |
| Watermarks, 404, loaders | Abstract / Pictorial | 4–8 % opacity / 64 px |
| Hero portrait | `mtb-portrait-hero` (WebP + PNG fallback) | frame ≤560 px wide, `object-fit:contain`, **never cropped** |
| About photo | `mtb-portrait-headshot` (WebP + PNG fallback) | frame aspect 1114:809, contain |
| Avatars | `mtb-avatar-square` | 40–96 px circle, gold ring |
| Illustration accent | Mascot | secondary only: 404 / empty states / booking decor; dark surfaces only |
| OG / social share | Emblem on `#060605` 1200×630 export | generated at build |
- Clear-space = height of monogram "M"; never recolor, outline, add effects, or place on light/photographic backgrounds; minimum render width 28 px (lettermark).

## 10. Image Usage
- **Provided art only:** the 7 brand PNGs. **No stock photos, no invented screenshots.**
- **Project visuals:** PDF has none → case-study hero area uses branded placeholder composition (Pictorial/Abstract mark + project title on black, gold frame) with a clearly-marked swap slot `img/project-{slug}.webp` until client supplies screenshots/live URLs (Q2).
- **Pipeline:** source PNG → build-time derivatives: WebP (+PNG fallback) at 480/800/1200/2400 w; `loading="lazy"` below fold; `fetchpriority="high"` for hero mascot; explicit `width/height` to prevent CLS; descriptive `alt` ("MTB monogram…", "Illustrated portrait of Mashzidul Tanun Borshon with laptop").
- Mascot: dark surfaces only (§0.3 #2).

### 10.1 Official photography — primary personal image (binding spec)
**Primacy rule:** the official photograph is the **primary personal image** everywhere a person is shown. The mascot vector is demoted to a *secondary illustration accent* (404 page, blog empty-states, booking-page decor) and never replaces the photo in hero/about/contact.

**Placement map**
| Spot | Asset | Treatment |
|---|---|---|
| Hero (S1, all pages' top fold) | `mtb-portrait-hero` (arms-crossed cutout) | gold arch frame + offset hairline ring + radial glow **behind**; cutout un-cropped on `--bg` |
| Home S3 + `/about/` | `mtb-portrait-headshot` (head-and-shoulders cutout) | rectangular hairline-gold frame, aspect 1114:809, Emblem as 56 px corner seal on frame |
| `/contact/` info card | `mtb-avatar-square` | 72–96 px circle w/ gold ring, beside name/profession text (text **next to**, never on, photo) |
| `/booking/` consultation card | `mtb-avatar-square` | 56–64 px circle beside "consult with Mashzidul" copy |
| Off-canvas menu | `mtb-avatar-square` | 48 px circle above intro snippet |
| Footer, blog cards, OG image | **no photo** | logo system only / Emblem OG — photo used "only when visually appropriate" per client |

**Face-safe zone (hard rule):** no text, badge, button or overlay inside the **top 45 %** of any rendered photo frame. The single permitted overlap is the hero "Why Choose Me" checklist card at the frame's **bottom-left corner (≥992 px only)**, sitting over the crossed-arms/torso area. Below 992 px the card stacks statically under the photo — zero overlap.

**Composition per breakpoint (cutouts are `object-fit:contain` — the person is never cropped, stretched or squashed):**
| Breakpoint | Hero photo | About photo | Avatar |
|---|---|---|---|
| ≥992 px | right column of 7/5 grid, frame ≤560 px wide, aspect 1114:1412; checklist card overlaps bottom-left | left of copy, frame ≤480 px wide | 72–96 px |
| 768–991 px | stacked **below** copy, centered, ≤420 px wide; card static below | above copy, full container width | 64 px |
| <768 px | centered, ≤78 vw; card static below; glow retained | full width, aspect kept | 56 px |

**Permitted treatments (brand consistency):** gold arch/hairline frame, offset ring, radial gold glow behind, soft ground-shadow ellipse under cutout, entrance reveal on the **wrapper** (arch clip-path / translate). **Forbidden:** any filter, duotone, recolor, AI restyle, facial retouch, crop of the person, upscale beyond master width, skew/scale animation on the `<img>` itself, text over the face.

**Markup/pipeline:** `<picture>` WebP → PNG fallback; `fetchpriority="high"` + eager load for hero cutout, lazy elsewhere; explicit `width`/`height` (1114×1412 / 1114×809 / 388×388) for CLS 0; alt = "Portrait of Mashzidul Tanun Borshon, web designer and full stack developer" (hero/about), "Mashzidul Tanun Borshon" (avatar). Derivatives live in `site/assets/img/portrait/` (§0.3 #7); masters in `uploads/` remain untouched.

**Composition proof:** `portrait-composition-proof.png` (this workspace) shows desktop/tablet/mobile hero, about frame and avatar card composed to these rules.

## 11. Responsive Behavior
| Breakpoint | Header | Hero | Grids | Misc |
|---|---|---|---|---|
| ≥1200 px | full nav | 2-col (copy / portrait) | portfolio 2-col, pricing 3-col | marquees both rows |
| 992–1199 | full nav, tighter | 2-col | pricing 3-col narrow | — |
| 768–991 | hamburger + off-canvas | stacked, portrait below copy | portfolio 2-col, pricing 1-col stacked cards | footer 2-col |
| ≤767 | hamburger | stacked; checklist card collapses | everything 1-col; service rows full-width | footer 1-col; marquee single row; type scale floors at clamp mins |
- Touch targets ≥44 px; accordions & carousels swipeable; off-canvas = focus-trapped, `Esc` closes; horizontal overflow guarded (`overflow-x:clip` on body).

## 12. Animations
| Element | Trigger | Effect | Spec |
|---|---|---|---|
| Preloader | load | lettermark fade/pulse → curtain up | ≤900 ms, skipped on repeat visits |
| Hero copy | load | staggered fade-up (eyebrow→H1→role→lead→CTAs) | 80 ms stagger, 600 ms, `cubic-bezier(.22,.61,.36,1)` |
| Hero portrait | load+scroll | **wrapper-only** reveal: arch clip-path open + glow fade-in + slight parallax translate on frame wrapper | GSAP/ScrollTrigger; the `<img>` itself is never scaled/skewed (no distortion) |
| Role line | — | gold gradient shimmer (background-position loop) | 6 s linear infinite |
| Marquees | always | infinite translateX, opposite dirs | 30 s linear, pause on hover |
| Section reveals | IntersectionObserver | fade-up 24 px, stagger children | once, threshold .15 |
| Service rows | hover/open | number → gold fill, row expand | 300 ms |
| Cards | hover | translateY(-6 px) + gold glow border | 250 ms |
| Counters | — | **none** (no stats in PDF) | — |
| Accordions (FAQ/services) | click | grid-template-rows expand | 320 ms ease |
| Back-to-top / header shrink | scroll | opacity/height transitions | 200 ms |
- Implement with **GSAP + ScrollTrigger** (or IO fallback) + **Swiper** for any carousel, matching reference stack; **`prefers-reduced-motion: reduce` disables all** (marquees static, reveals instant).

## 13. Content Mapping (PDF → site)
| PDF block | Destination |
|---|---|
| p.1 Home hero (name, role, lead, CTA, Why-Choose-Me ×5) | Home S1 + S7; footer brand snippet |
| p.1–2 About Me (2 paragraphs) | Home S3 (1st para), About (both) |
| p.2 Personal info (Birthday 22-09-2007 · Age 19 · Nationality Bangladeshi · Study Govt. Brajalal College · Degree BSC in statistics · Email · Phone · Address) | About `info-list`; Contact (email/phone/address only) |
| p.2 Skills ×13 | Home S2 marquee; About chips grouped (Frontend / Backend / CMS & Builders / Styling / SEO & Tools — grouping labels are editorial, items verbatim) |
| p.2–3 Services ×10 | Home S4 rows; Services page cards |
| p.3 Portfolio intro + 4 projects | Home S5; Portfolio grid |
| p.4–9 Project case data (Category, Duration, Project Cost, Overview, My Role, Technologies, Features) | 4 case-study pages (meta sidebar + body) |
| p.9–10 Pricing ×3 tiers | Home S6; Pricing page |
| p.11 Blog ×8 titles | Home S8 (first 3); Blog index (all 8) |
| p.11–12 FAQ ×6 | Home S9; Contact (repeat) |
| p.12–13 Contact block + socials | Contact page; footer col 4 |
| p.13 Terms ×7 | /terms/ |
| p.14 Privacy bullets | /privacy/ |
| p.14–15 Booking (intro + agenda ×6 + Book Now) | /booking/; CTA bands link here |
| Official photo ×3 masters (uploaded turn 2) | `site/assets/img/portrait/` derivatives → Hero S1 (hero cutout), Home S3 + /about/ (headshot frame), /contact/ + /booking/ avatars, off-canvas avatar — rules §10.1 |

## 14. SEO Structure
- **Titles:** `Mashzidul Tanun Borshon — Web Designer & Full Stack Web Developer` (home); `{Page} | Mashzidul Tanun Borshon` (others); case studies `{Project Title} | Portfolio | MTB`.
- **Meta descriptions:** composed from PDF copy only (e.g., home = lead paragraph trimmed ≤155 ch).
- **Heading hierarchy:** one H1/page (home H1 = name; subpages H1 = page title after breadcrumb); H2 = sections; H3 = cards/items.
- **Structured data (JSON-LD):** `Person` (name, jobTitle, email, telephone, address, birthday, sameAs→ pending Q1) · `WebSite` · `ProfessionalService` with `Offer`s from pricing (verbatim prices) · `FAQPage` (6 Q&A) · `BreadcrumbList` on subpages · `ItemList` on /portfolio/.
- **Technical:** canonical per page; `robots.txt` + `sitemap.xml` (14 URLs); OpenGraph + Twitter cards (Emblem OG image 1200×630); semantic landmarks (`header/nav/main/section/footer`); skip-link; alt text on all images; locale `en_US`… content language **en** (`<html lang="en">`); performance budget: LCP < 2.5 s (hero mascot WebP ≤180 KB), CLS < 0.1, total JS ≤ 120 KB gzip.

## 15. Technical Architecture
- **Stack:** hand-built **static multi-page site** — semantic HTML5 + CSS (custom-property tokens, no framework) + vanilla ES modules; **Swiper** (carousels) + **GSAP/ScrollTrigger** (reveals/parallax) self-hosted; no jQuery, no page builder. *(Rationale: hostable anywhere — cPanel/Netlify/GitHub Pages — and later wrappable in WordPress/Astro without rework. Alternative Astro SSG flagged Q13.)*
- **Repo layout:**
```
/site
  index.html about.html services.html pricing.html blog.html
  contact.html booking.html terms.html privacy.html 404.html
  portfolio/index.html + portfolio/{4 slugs}/index.html
  assets/css/ tokens.css base.css components.css pages.css
  assets/js/ main.js nav.js reveal.js accordion.js marquee.js form.js
  assets/img/brand/* (optimized webp+png)  assets/img/projects/* (swap slots)
  assets/img/portrait/* (mtb-portrait-hero / -headshot / -avatar-square, webp+png — DONE)
  assets/vendor/ swiper/ gsap/
  content/content.json   ← single source: all PDF copy, structured
  seo/ sitemap.xml robots.txt
```
- **Content-driven build:** every string lives in `content/content.json` (mirrors §13) so copy edits never touch markup.
- **Forms:** contact + booking forms POST to a client-chosen endpoint (Formspree/EmailJS/cPanel mailer — **undecided, Q8**); client-side validation, honeypot spam guard, success/error states.
- **Booking:** "Book Now" opens mailto pre-filled template OR embedded calendar (Calendly/Google — **undecided, Q9**); fallback = contact form with subject preset.
- **Quality gates:** W3C-valid HTML, Lighthouse ≥90 (P/A11Y/SEO/Best practices), axe-clean, tested 360 px→1440 px, Chrome/Firefox/Safari.

## 16. Missing Information — DO NOT INVENT (open questions for client)
| # | Gap | Interim plan |
|---|---|---|
| Q1 | Social URLs: FB/IG/LinkedIn given as names only; **Fiverr/Upwork/Freelancer blank** | Icons render but link `#` + `aria-disabled` until URLs supplied; blank platforms omitted |
| Q2 | Project screenshots / live links | Branded placeholders (§10) |
| Q3 | Testimonials — none in PDF | Section omitted entirely |
| Q4 | Stats/counters (years exp, projects count) — none | Counters omitted; only derivable facts shown (4 projects, 10 services, 13 skills) if client approves |
| Q5 | Skill percentages — none | Chips/marquee instead of progress bars |
| Q6 | Blog bodies, dates, categories — titles only | Index cards with title + "Article coming soon"; no fake excerpts/dates |
| Q7 | Experience timeline — only education present | About shows Education (Govt. Brajalal College, BSC in Statistics); no work-history timeline |
| Q8 | Form backend | Placeholder endpoint + config note |
| Q9 | Booking tool | mailto fallback |
| Q10 | ~~Mascot transparency~~ **RESOLVED (turn 2):** official photo (true alpha) is now the primary personal image; mascot = secondary illustration accent, dark surfaces only. Square master is 388 px → avatars ≤128 px only | Implemented per §10.1 |
| Q11 | FAQ standalone page vs section | Section (home + contact) |
| Q12 | Final font pairing | Rajdhani + Rubik (reference-matched) |
| Q13 | Static vs Astro/SSG | Static |
| Q14 | Domain/hosting, analytics IDs | Placeholders in config |
| Q15 | Age field: PDF says 19 (b. 22-09-2007) — ages over time | Show Birthday only, or compute age dynamically from DOB (recommend dynamic) |

## 17. Build Order (for the next instruction)
1. Tokens + base CSS + header/footer/global components → 2. Homepage → 3. About/Services → 4. Portfolio + 4 case studies → 5. Pricing/Blog → 6. Contact/Booking/Terms/Privacy/404 → 7. SEO pack (meta, JSON-LD, sitemap) + asset optimization → 8. QA (responsive, a11y, Lighthouse) → deliver.

---
*Plan complete. No code written, no pages built. Awaiting your instruction to proceed (and any answers to Q1–Q15 you wish to settle first).*
