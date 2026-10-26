# 360 Capture Microsite

Single-page static site selling 360° virtual tours, 2D & 3D floor plans and full walkthroughs
to estate agents, restaurants, pubs, shops and developers. Plain HTML/CSS/JS with no build step.

## View locally
    python3 -m http.server 8000   # then open http://localhost:8000

## Deploy
Push to GitHub and enable **Settings → Pages → Deploy from branch (main / root)**.
`.nojekyll` is included so files are served as-is. Any static host works (Netlify, Vercel, S3…).

## Structure
    index.html              the whole page (sections: hero, capture, LiDAR, extras, sectors,
                            process, gallery, CTA, FAQs, contact form, footer)
    assets/css/base.css     reset + brand tokens (colours, fonts, buttons)
    assets/css/site.css     section styles + responsive rules
    assets/js/main.js       nav, lazy video, before/after slider, form prefill + submit
    assets/img/             images (posters for each video are poster-*.jpg)
    assets/video/           mp4 loops
    PRODUCT-NOTES.md        Giraffe360 product research

## Before going live
- [x] **Brand**: CaptureHaus logos in `assets/img/logo/` (`capturehaus-white` over the hero, `capturehaus-black` on the white scrolled header, `capturehaus-powered-white` in the footer).
- [x] **Form**: HubSpot embed (portal 9342384, EU). Field styling is set in HubSpot's form editor, not this CSS.
- [ ] **Media**: images/video are placeholders from giraffe360.com. Replace them with your own,
      keeping the same filenames (or update the paths). Export a poster frame for each video.
- [ ] **FAQ TODOs**: confirm on-site capture time, delivery turnaround and coverage area
      (search `TODO` in index.html).
- [ ] Add favicon, real `og:image` absolute URL, and analytics if wanted.

## How the CTA buttons work
Every "Get a quote" / "Enquire" button is an anchor to `#contact`. Buttons carrying
`data-sector="…"` or `data-service="…"` pre-select that option in the form, and the form pulses
briefly so the eye lands on it. A sticky "Get a quote" bar appears on mobile once you scroll
past the hero (hidden while the form is on screen).
