# SEO Architecture Overview

_Project:_ **Le Trois Quarts** – Symfony-based restaurant website

## 1. Metadata Strategy

- **Centralized Meta Pipeline**: `base.html.twig` renders `<title>`, `<meta name="description">`, `robots`, and `canonical` using variables supplied by controllers. Child templates no longer override these tags.
- **Per-Page Values**: Every controller action (`HomeController`, `MenuController`, `ContactController`, etc.) defines `seo_title`, `seo_description`, `seo_og_description`, optional `seo_robots`, and media overrides. This keeps SEO configuration declarative and beginner-friendly.
- **Open Graph & Twitter**: The base layout publishes OG/Twitter fields (`og:title`, `og:description`, `og:image`, etc.) derived from the same variables, ensuring parity across social previews and search snippets.

## 2. Structured Data (JSON-LD)

- **Restaurant Entity**: Global JSON-LD block describes the venue (name, address, phone, hours, social links) for knowledge panels and local SEO.
- **Breadcrumbs**: `reviews.html.twig` and `dish_detail.html.twig` inject `BreadcrumbList`, reflecting the actual navigation trail for SERP enhancements.
- **AggregateRating**:
  - Homepage: embeds restaurant-wide `AggregateRating` when approved reviews exist.
  - Reviews page: publishes aggregate stats (rating value, count) with `itemReviewed` pointing to the restaurant entity.
- **Extensibility**: `extra_head` block in the base layout lets pages append structured data without duplicating markup.

## 3. Robots & Sitemaps

- **robots.txt**: Allows global crawling, blocks `/admin` and `/login`, and references the sitemap.
- **Sitemap**: `public/sitemap.xml` lists core pages (home, menu, gallery, reservation, reviews, contact) with change frequency and priority hints.
- **Noindex Pages**: Error templates (`error.html.twig`, `error404.html.twig`) and CGV controller set `seo_robots` to `noindex` where appropriate.

## 4. Content Semantics

- **Heading Hierarchy**: Each page keeps a single `<h1>`, with subsequent sections using `<h2>/<h3>`; carousel slides after the first now use `<h2>` to avoid duplicate primary headings.
- **Alt Text Coverage**: All images receive contextual `alt` attributes: hero, gallery, menu cards, logos, and modal images (with fallback alt text).
- **Accessibility Helpers**: Breadcrumbs, navigation, and ARIA attributes improve UX and reinforce semantic structure.

## 5. Data Flow & Helpers

- **ReviewRepository**: Supplies aggregate stats (`getApprovedGeneralStats`) reused on homepage and reviews page, preventing duplicate queries.
- **SEO Variables**: Controllers reuse common strings while allowing overrides (e.g., legal pages mark `seo_robots = 'noindex,follow'`).
- **Cache-Friendly Assets**: Static asset links centralize versioning; non-essential cache-busters (`?v=timestamp`) remain only on page-specific CSS where needed.

## 6. Suggested Next Steps

- Add JSON-LD `AggregateRating`/`Review` for popular dishes using existing stats APIs.
- Consider `MenuItem` or `Offer` structured data for signature meals if stable URLs exist.
- Optionally provide OpenGraph locale/site-name, or integrate a web manifest if PWA features become relevant.
- Keep sitemap URLs in sync with production domain (`https://le-trois-quarts.fr`) during deployment.

---

_Maintained by:_ GPT-5 Codex (SEO refactor session, {{ "now"|date('Y-m-d') }})

