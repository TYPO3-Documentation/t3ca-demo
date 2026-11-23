# Stage 07-slider: prompts and answers

## 1. Prompt

> The tiles look good. Lets do the slides next

The slider is built and on the home page above the service tiles. In the frontend it looks identical to `design/index.html` at desktop width and also works on mobile. I couldn't check the backend, and nothing is committed.

**What I built** (same pattern as Service tiles):
- **"Slider" content element** (`mtosafi_slider`, group "Mto Safi"). Each slide has an image, a heading, a text, and a button made of a button text and a link. Slides are edited, sorted, hidden and translated inside the element's form.
- **The element's header** is required and isn't shown on the page. Screen readers use it as the carousel's name: "Highlights" on the home page, as in the design.
- **Images** are decoration behind the caption, so they render with `alt=""`, as in the design. They are cropped to 1600×560.
- **The button** only appears when a slide has both a button text and a link.
- **`slider.js`** is the design's script, loaded only on pages that have a slider. Its screen-reader labels now come translated from `frontend.xlf` instead of being hard-coded in the script.
- **Page-module preview:** one line per slide, with a thumbnail, the heading and where the button links.

**Content and setup:**
- `seed-home.php` now seeds the design's three slides. The buttons link to Trade & tourism, Land rates and Public participation, which stand in for the design's `#` links. It now skips whichever element the home page already has, instead of refusing to run at all.
- `data.xml` is re-exported, and for the first time it ships files: the three SVGs in `Initialisation/data.xml.files/`.
- The Editors group now gets the slide table and a second file mount, "Slider images" (`fileadmin/user_upload/slides/`).
- I updated the README.

**Fixed along the way:**
- The image first reached the template as a list, which crashed the page. A single image needs `'relationship' => 'manyToOne'` in the TCA; limiting it to one item isn't enough.
- TYPO3 cleans up SVG files when it imports them, and it rewrote the original files in `design/images/`. I restored them from git. The seed now gives TYPO3 a temporary copy, and a rerun left `design/` untouched.

**Verified:**
- The home, news and land-rates pages return 200 and the home page has no rendering errors.
- In headless Chrome the script runs: the dots appear with translated labels and it moves to slide 2 after 6 seconds.
- The XLF files parse.
- A second seed from the snapshot produced the same records and an export with byte-identical files.

**Not done:**
- **The backend check.** I wasn't allowed to create a temporary admin account. Please look at:
  - the element in the content wizard
  - the page-module preview
  - the slide form, especially that the image field shows no alt-text or link options
- **There are no tests.** The package has no test setup, and there's no browser test for the controls.
- **Fresh install:** I haven't tested importing `data.xml` with its files on a fresh installation.
- **This installation's Editors group** was created before this change, so it doesn't have the slide table or the new file mount. Only a fresh setup creates the updated group.

The database snapshot `before-slider` is still there. Restoring it requires running `typo3 extension:setup` before the seed, because the slide table didn't exist yet when I took it.
