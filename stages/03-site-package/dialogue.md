# Stage 03-site-package: prompts and answers

## 1. Prompt

> How should I turn the static design in design/ into a TYPO3 v14 site package? What are the options, and which do you suggest and why?

I've read the design and the project. Here's my recommendation, then the other options and why I'd pass on them.

**Where things stand:** TYPO3 14.3.10 runs in DDEV with a site called `main`. It has no extensions of its own, `packages/` is empty but already set up as a Composer path repository, and the site only uses the `fluid-styled-content` sets. The design has 5 pages with 5 different layouts, a shared header and footer, about 900 lines of SCSS compiled with plain `sass`, 5 small JS files, and several interactive parts: a slider, a newsletter sign-up, a contact form, a login form, search, and a land rates calculator.

#### Options

**1. Write a small sitepackage by hand (recommended).** You create `packages/mto-safi/` yourself, add one site set, render pages with `PAGEVIEW`, and keep all templates under one root (`Resources/Private/Templates/`).
- **For:** You only have the files you need, and the layout follows what v14 expects.
- **Against:** You write maybe 15–20 files yourself. For a design this size, that's an afternoon.

**2. Generate it with the Site Package Builder at get.typo3.org, then trim it.**
- **For:** It's fast, and Composer, `ext_emconf` and the set come pre-filled.
- **Against:** It creates many empty files, and its folders (`Resources/Private/PageView/…`, `PageTsConfig/BackendLayouts/`) don't match the v14 layout above. You'd delete and move about as much as you'd write. The console alternatives don't create a whole sitepackage either: `make:site-package` refuses, and `friendsoftypo3/kickstarter` only writes parts.

**3. Build on top of `theme_camino`, TYPO3 v14's built-in theme.**
- It's worth reading as an example, but don't depend on it. It's tied to one release line and is announced to move out of the core. Its look also has nothing to do with this design.

**4. Use a framework theme such as `bootstrap_package`.**
- It brings its own markup, CSS and content elements. Your design is fully custom with BEM classes, so you'd spend your time overriding it.

**5. Paste the HTML in and switch off TYPO3's page output (`config.disableAllHeaderCode`).**
- This is a trap. TYPO3 normally writes the `<html>`, `<head>`, CSS and JS includes and meta tags. With this switch, the template carries the whole document and TYPO3 adds nothing, so the meta tags and every extension's assets are lost.

#### Why option 1

- **The design maps cleanly onto TYPO3.** Each of the five layouts becomes a page layout editors can choose: `Home`, `SidebarLeft`, `Default` (full width), `SidebarRight` and `Narrow`. Each needs a page template with a matching name.
- **The header and footer become partials.** These are fragments of the body only. The `<head>` and the `<link>`/`<script>` tags go into the page setup as `includeCSS`/`includeJS`. The navigation and breadcrumb come from the menu processor instead of hard-coded links, and `logo.svg` is loaded via `f:uri.resource`.
- **The SCSS build moves in almost unchanged.** It goes into the package and writes to `Resources/Public/Css/main.css`, with the JS in `Resources/Public/JavaScript/`.
- **It's readable.** This repo looks like a staged demo (`Stage 00…02`), and a hand-written package is something people can follow commit by commit.

Two decisions to settle while setting it up:
- **Keep the `typo3/fluid-styled-content` set.** EXT:form, felogin and the search plugin all render through its `lib.contentElement`. If you drop it, they render as nothing.
- **Probably drop `fluid-styled-content-css`,** because your SCSS should style the content elements.

#### Decisions for you before content work

The page frame is the easy part. These are product decisions you should make:

| Design part | Likely answer |
|---|---|
| Slider, quick-link tiles | Custom content elements with fields, not markup in the template. You can write them as plain TCA or use `friendsoftypo3/content-blocks`. |
| Contact form | EXT:form, already installed. It needs a mail finisher and a decision on how long submissions are kept. |
| Login | EXT:felogin, already installed. |
| Site search | `indexed_search` (not installed) or something like `ke_search`. |
| News & events (`docs/news-and-events.docx`) | A records extension such as `georgringer/news`, or your own record type. |
| Newsletter with double opt-in | There's nothing in the core for this, so an extension or your own build is needed. |
| Land rates calculator | A custom element. Keep the rates as data attributes, filled from fields or site settings instead of in the script. |
| "Kiswahili" link | A second site language, plus a decision on what a page shows when no translation exists. |

The project declares no test or check commands. Once it's built, the proof is to clear the caches and render one page per layout.

If you agree with option 1, I'll start with the package skeleton, the site set, the five page layouts and the header and footer, and leave the content elements for the next step.

## 2. Prompt

> go ahead with option 1

The sitepackage is built and wired into the `main` site, and all five page layouts render. Nothing is committed yet, and `design/` is unchanged.

#### What's there

The package is `mtosafi/sitepackage` (extension key `mtosafi_sitepackage`) in `packages/mtosafi_sitepackage/`, and the root `composer.json` now requires it.

- **Site set `mtosafi/sitepackage`:** it depends on `typo3/fluid-styled-content`. The site now depends only on this set, so `fluid-styled-content-css` is gone. It also holds settings an admin can change in the backend: brand name, top-bar notice, address, opening hours, footer note, and the pages for the service menu, footer menu and search.
- **Five page layouts:** `Default` (full width), `Home`, `SidebarLeft`, `SidebarRight` and `Narrow`. Each one is a file under `BackendLayouts/` with a page template of the same name. They have two content columns, `main` and `sidebar`.
- **Templates:**
  - One page frame (`Layouts/Pages/Default`) plus partials under `Partials/Page/` for the header, footer, breadcrumb and menu links. They're in their own folder so they can't collide with fluid_styled_content's `Header` partial.
  - All navigation comes from page data: main nav, sub-navigation, breadcrumb, service menu, footer menu and language menu.
  - CSS, JS and favicon are included through TypoScript (`includeCSS`, `includeJSFooter`, `shortcutIcon`).
- **SCSS:** it moved into the package. `npm run build` writes `Resources/Public/Css/main.css`.
- **Site config:** `config/sites/main/setup.typoscript` held the installer's welcome page, which would have overridden the set. Its content is now a comment; the file is still there.

#### How I tested it

I flushed caches and rendered page 1 once with each layout:

| Layout | HTTP | `<html>`/`<head>`/`<body>`/`<h1>` | Sidebar column rendered |
|---|---|---|---|
| Default | 200 | 1/1/1/1 | no (has no sidebar) |
| Home | 200 | 1/1/1/1 (hidden h1) | no (has no sidebar) |
| SidebarLeft | 200 | 1/1/1/1 | yes |
| SidebarRight | 200 | 1/1/1/1 | yes |
| Narrow | 200 | 1/1/1/1 | no (has no sidebar) |

- **Navigation:** I added temporary "Revenue & payments › Land rates" pages. The main nav, breadcrumb and sub-navigation render with the right `aria-current` values.
- **Labels:** all page layout and column labels resolve in the backend.
- **Test data:** I switched page 1's layout and created test records through DataHandler, then restored the layout to empty. The test pages (uid 2–4) and content element (uid 2) are soft-deleted, so you can purge them in the Recycler.
- **Not tested:** I didn't open the page module in a browser.

#### Two bugs I fixed while testing

- **Empty page heading:** `{page.title}` rendered nothing. In v14 `page` is an object, so the right path is `{page.pageRecord.title}`.
- **Footer cut short:** the address and opening hours stopped after the first line, because `{settings}` reads TypoScript constants and those end at a line break. The footer now reads `{site.settings.all…}`.

#### Where I changed the design

- **Focus ring:** the design's gold outline has only 1.82:1 contrast on white, and WCAG AA (which the design's README targets) asks for 3:1. It's now dark green (12.3:1) on light backgrounds and stays gold on the dark header, nav, footer and slider (4.5–6.7:1).
- **Parent pages in menus:** a parent of the current page is now marked `aria-current="true"` and keeps the design's highlight. The design marked it as `"page"`, which tells screen readers it's the current page.
- **Page headings:** every layout except Home renders the page title as `<h1>`, so content element headings come out as `<h2>`. Home gets a heading only screen readers see, because the design shows none there.
- **Home spacing:** the gap between home sections now works for content elements, which aren't wrapped in `<section>` like the static HTML was.

#### Still to do

- **Content elements:** slider, quick-link tiles, news and events, newsletter, contact card, info box, downloads list and the land rates calculator. Only `navigation.js` is included site-wide; the other scripts should come in with their elements.
- **Search:** no search extension is installed. The header form still submits `q` and only shows once a search page is set.
- **Pages to create:** set the service menu, footer menu and search page in the site settings. Until then those parts stay hidden.
- **Browser tab title:** shows only the page title, because the site config has no `websiteTitle`.
- **Your decisions:**
  - `/sitemap.xml` and `/robots.txt` return 404. The fix is to add the `typo3/seo-sitemap` set and a static `robots.txt` route in the site config.
  - The Kiswahili language and what it shows when a page has no translation.
  - Error handling and a 404 page.
  - Legal pages. I wrote no legal text.
- **Composer audit:** `composer require` reported 10 security advisories in 7 existing packages. I didn't change those.

Want me to commit this as the next stage, or start on the content elements?
