# CaptureHaus

Marketing site for CaptureHaus (360° virtual tours, LiDAR floor plans, photography, video, drone), based in Marlborough, Wiltshire. Part of Bread & Butter (trading name of Thomas Mae Group Ltd). Live at https://capturehaus.co.uk on WordPress (Cloudways). Jon and Kevin edit content in the CMS.

Server details, SSH and helper scripts are in `CLAUDE.local.md` (not in Git).

## Repo layout

- `wordpress/capturehaus/` – the WordPress theme (the only thing that deploys).
- `index.html`, `assets/` – the original static site (pre-WordPress), kept for reference.
- `mockups/`, `High Res Imagery/`, `Logos/` – local only (gitignored).

## How changes reach the site

1. **Code:** edit the theme in this repo → commit and push to `main`. A GitHub Action splits `wordpress/capturehaus` into the `wordpress-theme` branch.
2. Cloudways (staging, then live) → **Deployment via Git → Pull** on branch `wordpress-theme`. Deployment path must be `public_html/wp-content/themes/capturehaus`. If it's blank, the Pull dumps the theme into `public_html` and overwrites WordPress's `index.php` (white screen). Cloudways only deploys when there's a new commit.
3. **Content** (pages, copy, images, Yoast fields) lives in the database, not Git. Staging is the content master; live gets it by a Cloudways staging → live **database** push (content tables only, not `wp_options`, with a backup of live first). Don't edit content on live meanwhile.
4. After deploys, purge Varnish (and Cloudflare). Local (`capturehaus.local`, Flywheel Local) symlinks the theme from this repo, for testing only; its content is behind staging.

## Theme structure

- Pages are built from ACF flexible-content **sections** (`inc/fields.php` → `template-parts/sections/*.php`): hero, page_header (inner pages, H1 + breadcrumbs + optional image/live embed, "show whole image" option), features, statement, extras, sectors, process, gallery, cta_band, faqs (adds FAQPage schema), text, links (related-page cards), case_studies (filterable by sector/service).
- **Blueprints** (`inc/blueprints.php`): Pages → "Add service / sector / area page" creates a pre-filled draft under the Services / Who it's for / Areas hub.
- Header menu: top-level page items get dropdowns of their child pages automatically (`capturehaus_menu_links()` in `inc/helpers.php`).
- Footer: logo | Explore (footer menu) | Areas we cover (area pages + Site settings → Footer → Towns) | Get in touch (phone + social icons). Stacks below 1024px.
- Schema (`inc/schema.php`): extends Yoast's graph – Organization + ProfessionalService (phone, address, areas served), parent organisation Bread & Butter with its socials, Service node on service/sector pages. Business details and social links: Site settings → Business (code has defaults).
- Site settings (ACF options page): phone, header CTA, quote form (HubSpot portal/form IDs), footer lines, towns, business details.

## Rules learned the hard way

- **Never round-trip ACF fields** (`get_field()` → change → `update_field()`) on a page with video/file fields: formatted values (video URLs) save back as empty. Use `get_field( 'sections', $id, false )` (raw) or write individual meta keys (`sections_{i}_{name}`) directly.
- Draft pages have no slug; set `post_name` explicitly when creating pages by script, then look them up by path.
- Content changed via WP-CLI doesn't refresh Yoast's indexables; re-save the post (`wp_update_post( array( 'ID' => $id ) )`) afterwards.
- Breeze (Cloudways cache plugin) is inactive but its `advanced-cache.php` drop-in was still serving stale pages; removed on staging, still present on live (rename it before/after the push).
- Only state facts already on the site or confirmed by Jon in copy: no invented prices, turnaround times or credentials.
- British English, plain copy. Each page: H1 with the focus keyphrase, Yoast title ≤ 60 chars, meta description ≤ 155 chars, unique focus keyphrase per page.

## Content so far (staging pushed to live on 2026-10-29; live is indexable)

- Services: hub, 360° virtual tours, Floor plans, Photography and video, Drone photography.
- Who it's for: hub + 7 sector pages. H1s are audience-only ("Built for *estate agents*…"); Yoast titles keep the service term on purpose.
- Areas: hub, Wiltshire, and 16 town pages under `/areas/`: Bath, Calne, Cheltenham, Chippenham, Cirencester, Devizes, Hungerford, Lacock, Malmesbury, Marlborough, Melksham, Newbury, Purton, Royal Wootton Bassett, Swindon, The Cotswolds. H1 "Virtual tours and floor plans in *Town*", focus "virtual tours Town". Created by `.claude-tools/towns-content.php` (pass slugs to create only those; running it with no slugs overwrites every town page).
- How it works (`/how-it-works/`) and FAQs (`/faqs/`, 12 Qs, FAQPage schema). Header and footer menu items point at these pages. Script: `.claude-tools/how-faqs-content.php`.
- Footer "Areas we cover" lists the area pages automatically; Site settings → Footer → Towns is empty on purpose (Salisbury dropped).
- Homepage hero "Built for" links and sector cards point at the sector pages.
- Brand dark blue: `--c-ink` #1A222E (hover #121821), in `assets/css/base.css`.

## Rule for new pages

- No hidden/doorway pages. Service × sector combos only as real, linked pages with distinct content.
- Don't reuse an image twice on one page; prefer images not used elsewhere.

## Open items

- [ ] Jon's copy review of all pages, especially the town pages' local facts (written from general knowledge) and estate-agent/architects/health copy.
- [ ] Town pages: header images are stand-ins from existing shoots; add local photos, local jobs and reviews over time (about 70% of each page's copy is shared).
- [ ] Shops and retail page needs a real retail photo (currently camera-in-hand).
- [ ] Yoast logo is a screenshot; replace with the real logo (Yoast → Settings → Site representation).
- [ ] Live: confirm `wp-content/advanced-cache.php` (Breeze) is renamed and caches purged; no SSH user on live yet.
- [ ] SEO quick fixes (offered, not done): Services hub Yoast title is 70 chars; alt text missing on image IDs 8, 16, 18, 21, 23, 25, 35, 84, 86, 88; FAQ answers on `/faqs/` have no links; homepage repeats all 10 FAQs (trim to 4–5 + "See all FAQs").
- [ ] Search Console via Site Kit + submit `/sitemap_index.xml`; Google Business Profile (16 service areas); ask clients for Google reviews.
- [ ] Later: case studies (Chapel Gardens draft, tag by town), Bread & Butter site link + client credit links, local directories, thank-you page + HubSpot redirect, guides (phase 3 of the SEO plan: https://claude.ai/artifact/1P16EFEfKLoUijx8t6MYtZ).
