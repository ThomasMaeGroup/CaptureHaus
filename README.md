# Giraffe360 Microsite

Static HTML/CSS microsite selling the Giraffe360 camera + Content Studio. No build step.

## View locally
    python3 -m http.server 8000   # then open http://localhost:8000

## Structure
    index.html              page shell (placeholder until brief)
    PRODUCT-NOTES.md        product research: features, specs, plans, proof points, brand
    brief/                  drop the design brief here
    kit/                    drop the client brand/sales kit here (logos, fonts, imagery, copy)
    assets/css/base.css     reset + brand tokens (from live giraffe360.com CSS)
    assets/css/site.css     section/page styles
    assets/js/main.js       interactions
    assets/fonts/           licensed brand fonts from the kit
    assets/img/reference/   images pulled from giraffe360.com for reference; replace with kit assets

## Next steps
1. Add brief to /brief and kit to /kit
2. Swap tokens/fonts in base.css if the kit differs
3. Build sections per brief
