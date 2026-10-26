# CaptureHaus WordPress build

The static site (repo root) rebuilt as a WordPress theme. Same design, CSS and JS; content is edited in WordPress.

Requires **WordPress 6.5+**, **PHP 8.1+** and **Advanced Custom Fields Pro**.

## What's here

```
wordpress/
  capturehaus/                 the theme
    inc/fields.php             all ACF fields (registered in code, so they're version-controlled)
    inc/setup.php              menus, case study post type, scripts/styles
    inc/helpers.php            template helpers
    template-parts/sections/   one template per page section
    template-parts/quote.php   quote form (HubSpot) shown at the bottom of every page
    assets/                    css, js and logos
  import-static-site.php       one-off: copies index.html content + media into WordPress
```

## How editing works

- **Pages** are built from **Page sections** (Hero, Services list, Statement band, Before/after + tiles,
  Sectors, How it works, Photo strip, CTA band, FAQs, Text block, Latest case studies, Ticker).
  Add, remove and drag to reorder. Plain pages (e.g. Privacy Policy) can just use the normal editor.
- **Site settings** (left menu): phone number, header button, quote form text/image, HubSpot IDs, footer lines.
- **Case studies** (left menu): title, summary (Excerpt), featured image, location, sector, services,
  tour / 3D plan links, gallery and the story. Listed at `/case-studies/`.
- **Blog**: normal Posts, listed on the Blog page.
- **Menus**: Appearance → Menus (Header menu, Footer menu). Links like `/#capture` jump to a section
  with that Anchor ID on the homepage.
- Text shortcuts: wrap a word in `*asterisks*` to italicise it (hero) or highlight it yellow (statement band).
- **Videos**: upload compressed MP4s (H.264, ideally under 6 MB, 10–15 s loops). Add a poster image
  (the first frame) so something shows while it loads.
- The quote form appears on every page; tick "Hide the quote form" in Page options to remove it.

CSS/JS are versioned by file modified time, so browsers pick up theme changes automatically.

## Local (Local by Flywheel)

The theme folder is symlinked into the Local site:

```
~/Local Sites/capturehaus/app/public/wp-content/themes/capturehaus -> wordpress/capturehaus
```

Edits in the repo show up on http://capturehaus.local immediately.

To rebuild the homepage from the static `index.html` (re-runnable; media isn't duplicated):

```
wp eval-file wordpress/import-static-site.php /path/to/CaptureHaus
```

## Going live on Cloudways

1. Create a new WordPress application on the server and point the domain at it.
2. Move the Local site across (database + uploads), e.g. with a migration plugin
   (All-in-One WP Migration / WP Migrate) or Local's export plus the Cloudways migrator.
3. Install and activate ACF Pro with the licence key.
4. Deploy theme updates by copying `wordpress/capturehaus` to `wp-content/themes/capturehaus`
   (SFTP, or Cloudways Git deployment).
5. Check: quote form submissions arrive in HubSpot, permalinks are "Post name", and an SEO plugin
   (e.g. Yoast or Rank Math) is set up for titles, descriptions and social images.
