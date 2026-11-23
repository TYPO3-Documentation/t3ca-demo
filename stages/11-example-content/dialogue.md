# Stage 11-example-content: prompts and answers

## 1. Prompt

> The pages in the menu are all empty. Can you fill them with example content, text with images, so we can see what the site will look like?

All 16 empty pages now have example content, and each one shows an illustration. Every page answers 200. I checked them in headless Chrome at desktop and phone width, and the search finds them now that the index has been rebuilt. Nothing is committed.

**The content**
- **Revenue & payments**: an intro with an image and a menu of its subpages.
- **Land rates, Single business permit, Parking fees, Market fees, Waiver of interest**: text with an image, plus a fee table or a "what you need" list. The land rates follow `design/docs/land-rates-calculation.md`.
- **Health, Environment & water, Trade & tourism, Public participation, Contact**: text with an image, plus a table or list (health centres, waste collection days, forum dates, opening hours). Contact reuses the address and hours from `design/contact.html`.
- **E-services portal, Access to information, Tenders**: text with an image; Access to information and Tenders also have a list or table.
- **Accessibility statement, Privacy policy**: these say they are placeholders. Their real text is for the municipality to write, so I didn't invent legal text.
- **Images**: 16 flat SVG drawings in the design's green, red and gold, each with alternative text. They land in `fileadmin/user_upload/pages/`.

**How it's built** (following the project's existing pattern)
- `Build/Initialisation/seed-pages.php` writes the content, and its drawings are in `Build/Initialisation/images/`. Like the other seed scripts, it refuses to run if any of those pages already has content.
- I re-exported `Initialisation/data.xml` and replaced it with the new export, adding 16 files to `data.xml.files/`. Record counts for all other tables are unchanged.
- The sitepackage README lists the new script and describes the content.

**Changes beyond content.** Without these, the pages didn't look right:
- **Image layout:** the site didn't load fluid_styled_content's CSS, so images never sat beside the text. I added `typo3/fluid-styled-content-css` to the site set's dependencies.
- **Styles:** I added a table style in the design's colours and made images sit above the text on small screens, where the side-by-side layout squeezed the text into a narrow column. The changes are in `_components.scss`, with `main.css` rebuilt.
- **Editors:** the "Editors" group gets a new "Page images" file mount for the new folder. This only applies to new installations. In this one, the group exists already and would need the mount added in the backend.

**Not done yet:** I haven't tested the import on a fresh installation. That means the README's `ddev delete` procedure, which would wipe this database. I can run it if you want. To undo the seed here, run `ddev snapshot restore before-seed-pages`.

Screenshots are in `/tmp/claude-1000/-home-lina-wolf-projects-typo3-t3ca-2026-demo/b6f2d3ef-aa61-40d6-82a5-060ccc03595e/scratchpad/`, for example `revenue-payments-land-rates.png` and `mobile-health.png`. I can draft a commit message when you want one.

## 2. Prompt

> pictures need a lightbox where you can see them full size after a click

Clicking a picture now opens it full size in a lightbox. I tested this in headless Chrome on the Health page (desktop width) and Parking fees (phone width):
- A click opens the dialog with the image and its alt text, and focus moves to the Close button.
- Escape closes it and puts focus back on the picture.
- The page doesn't scroll behind the dialog while it's open.

Screenshots: `/tmp/claude-1000/-home-lina-wolf-projects-typo3-t3ca-2026-demo/b6f2d3ef-aa61-40d6-82a5-060ccc03595e/scratchpad/lightbox-desktop.png` and `/tmp/claude-1000/-home-lina-wolf-projects-typo3-t3ca-2026-demo/b6f2d3ef-aa61-40d6-82a5-060ccc03595e/scratchpad/lightbox-mobile.png`

**How it works**
- **Lightbox script:** `Resources/Public/JavaScript/lightbox.js` uses the browser's built-in modal dialog, with no library. It has a close button, Escape, click beside the image to close, and previous/next buttons plus arrow keys when one element has several images. Pages without zoomable images don't load it.
- **Image links:** I replaced one fluid_styled_content partial, `Templates/Partials/Content/Media/Type/Image.fluid.html`. When "Enlarge on click" is set, the image links to its large version (up to 1920 px), so it still works without JavaScript. Screen readers hear "(show larger)" on the link.
- **Labels:** "Close", "Previous image", "Next image", "Image 2 of 3" and "(show larger)" are in `frontend.xlf`, so they can be translated.
- **Default on:** new content elements have "Enlarge on click" switched on (`TCAdefaults` in `page.tsconfig`). I checked that the setting is picked up for the site's pages. Editors can still switch it off per element.
- **News:** images on the news detail page open in the same lightbox. No news item has an image yet, so this part hasn't been seen working.
- **Seeded content:** I rolled the database back to the snapshot and re-ran the seed with "Enlarge on click" on all 16 images. Then I re-exported `data.xml`: it has the same records and files as before, with the flag now set. The README describes the lightbox.

**Not tested yet:**
- Previous/next: no element has more than one image, so that part hasn't run in a browser.
- The fresh-install import, as before: it needs the README's `ddev delete`, which wipes this database.

Nothing is committed.

## 3. Prompt

> We also need subtitles for the images

Captions are already part of fluid_styled_content: an image's "Description" field renders as a `<figcaption>` under it, and the lightbox shows it too. So this needs a caption for each seeded image and a caption style in the design. I'm adding both, then re-seeding and re-exporting.

## 4. Prompt

> Put multiple images on one page to test it out

Every image now has a caption, and Trade & tourism has a four-image gallery for testing the lightbox: https://demo.ddev.site/trade-tourism

**Captions:** fluid_styled_content already shows an image's "Description" field as a caption, so this was content plus a little styling:
- All 20 images have a short caption, shown in small grey text under the image and centred under the picture in the lightbox.
- Editors fill in "Description" on an image to add or change its caption.
- The accessibility and privacy images say they are example images, like their text.

**Gallery:** I added an "Images" element, "Impressions of Mto Safi", with four pictures in two columns, each with its own caption. I tested it in headless Chrome:

| Step | Shows |
|---|---|
| Click the 2nd image | market picture, "Image 2 of 4", its caption |
| → | image 3 of 4, caption changes with it |
| → | image 4 of 4 |
| Previous button | back to 3 of 4 |

Wrapping from the last image back to the first wasn't covered by that run. Screenshots: `/tmp/claude-1000/-home-lina-wolf-projects-typo3-t3ca-2026-demo/b6f2d3ef-aa61-40d6-82a5-060ccc03595e/scratchpad/gallery-page.png` and `/tmp/claude-1000/-home-lina-wolf-projects-typo3-t3ca-2026-demo/b6f2d3ef-aa61-40d6-82a5-060ccc03595e/scratchpad/gallery.png`

**Other changes:**
- The lightbox background is a little darker. The page text behind it showed through faintly under the caption.
- The seed script now handles elements with several images. I restored the snapshot, re-ran the seed and re-exported `data.xml`; it differs from the previous export only by the gallery element and its four image references. The gallery reuses existing illustrations, so no new files were added.
- The README mentions the captions and the gallery.

**Not done yet:** the test on a fresh installation still hasn't run, since it needs the `ddev delete` that wipes this database. Nothing is committed.
