# Stage 04-pages: prompts and answers

## 1. Prompt

> Show me the website in my browser.

I opened https://demo.ddev.site in your browser. The DDEV project is running and the site returns HTTP 200. The "broken pipe" lines in the terminal output are routine Chrome messages when it hands a link to a window that's already open, so the page should be in a new tab.

The TYPO3 backend is at https://demo.ddev.site/typo3 if you need it.

## 2. Prompt

> The website only shows a page called Home, and the menu is almost
>        empty. What is missing, compared with the design?

The site package itself is mostly fine: header, footer, the five page layouts and the menu wiring are all there. What's missing is almost all content. The database has a single page, "Home", with one "Welcome to your default website" text element, and no files. The menus are built from the page tree, so with no pages under Home the main menu shows only "Home".

Compared with `design/README.md` and `design/index.html`:

**Pages**
- **Main menu sections:** Revenue & payments, Health, Environment & water, Trade & tourism, Public participation, Contact.
- **Revenue & payments subpages:** Land rates should sit under it, since it uses the sidebar-left layout with a sub-navigation.
- **Contact** (full width), **Search** (sidebar right) and **Login / e-services portal** (narrow).
- **A folder for the top service menu** (E-services portal, Contact).
- **A folder for the footer menu:** Accessibility statement, Privacy policy, Access to information, Tenders.
- **The Home page's layout:** no backend layout is assigned to it, so it isn't using the "Home" layout.

**Site settings that are still 0**
- `mtosafi.navigation.serviceMenu`, `footerMenu` and `searchPage`. Until these point at real pages, the top service links, the footer's "Information" column and the header search form stay hidden.

**Home page content**
- The slider with three slides and its images.
- The "Popular services" tiles.
- News and events: three news cards and three events. The texts are in `design/docs/news-and-events.docx`.
- The newsletter sign-up.

**Features**
- The site package only includes `navigation.js`. The design's `slider.js`, `forms.js`, `calculator.js` and `search.js` aren't there yet.
- Nothing handles the forms yet: contact, newsletter (double opt-in) and login. The design says the website has to do the real processing.
- There's no search and no land rates calculator element. The calculator's rules are in `design/docs/land-rates-calculation.md`.
- The site has only English. The design has a Kiswahili language switch.

The obvious next step is to build the page tree, set the three settings and give Home its layout; that gets the menus, footer and search form working. I could also ship it as initial content inside the site package so it comes up on a fresh install. Do you want me to do either?

## 3. Prompt

> What do you suggest I do first, and why?

Start with the page tree, and ship it with the site package as initial content rather than clicking it together in the backend.

**Why the page tree first**
- **Everything else depends on it.** The menus, the three site settings (service menu, footer menu, search page), the sub-navigation on Land rates and the breadcrumb all need real pages. The settings even need their page IDs, which don't exist yet.
- **It's the fastest visible result.** With no new code, the main menu fills in, the footer's "Information" column and the service links show up, the search form appears, and you can check all five layouts on real pages.
- **It tests the site package you just built.** If a layout or menu is wrong, you'll see it now, before content elements are layered on top.

**Why as initial content in the package**
Your commits are stages (`Stage 00` to `Stage 03`), so this looks like a demo that has to come up the same way on a fresh install. If the pages exist only in your local database, the next stage can't be rebuilt from the repository. If they ship as initial content in `mtosafi_sitepackage`, a fresh install gets the same page tree, layouts and settings every time.

**After that, in this order**
1. **The home page content elements:** slider, service tiles, news and events teasers. These are the most visible part of the design and need the pages in place first.
2. **The interactive parts:** the forms (contact, newsletter, login), search, and the land rates calculator. Each needs its own processing behind it, so each is a separate piece of work.
3. **Kiswahili:** add it last. A second language means translating every page and element, so it makes sense once the content has settled.

Shall I start on the page tree as initial content?

## 4. Prompt

> Is there anything I could break, and can I undo it?

Very little can break, and all of it can be undone, as long as we take a database snapshot first.

**What could go wrong**
- **A second "Home" page.** Initial content is imported as a new page tree. The existing Home page (uid 1) stays, so you'd see two root pages, and the site would keep pointing at the old one until its config is changed.
- **Wrong page IDs.** The import gives pages new IDs. If the settings (service menu, footer menu, search page) or the site's root page refer to fixed numbers, they can point at the wrong page or none. The menus would then stay empty or link to the wrong place, but nothing would crash.
- **The import runs only once.** TYPO3 marks the initial content as imported. If I fix something in it later, setting up the extension again won't re-import it, so to test it again we'd have to restore the database first.
- **Your existing content:** there's only the one "Welcome to your default website" element, so there's nothing of value to lose.

The design files, the templates and the backend stay untouched, and nothing leaves your machine.

**How to undo it**
- **Database:** before the import, run `ddev snapshot --name before-page-tree`. `ddev snapshot restore before-page-tree` brings back exactly today's state in a few seconds. This is the important one, because the database isn't in git.
- **Files:** everything I add goes into the site package and is visible in `git status`. `git restore` and `git clean` on those paths remove it, or you can just not commit it.
- **Single pages:** pages deleted in the backend can be brought back with the Recycler.

My plan is snapshot first, then the import. I'll check the result in the backend and the browser, and leave it uncommitted so you can review the diff. Shall I go ahead?

## 5. Prompt

> yes, take the snapshot and go ahead

The site now has the full page tree from the design, and the tree is packaged as initial content. The last check, importing it on a brand-new installation, hasn't run: auto mode blocked the step that would have emptied the database.

**Working now** (reload https://demo.ddev.site):
- **Main menu:** Home, Revenue & payments, Health, Environment & water, Trade & tourism, Public participation, Contact.
- **Revenue & payments:** uses the sidebar-left layout. Its sub-navigation shows Land rates, Single business permit, Parking fees, Market fees and Waiver of interest.
- **Header:** the service links (E-services portal, Contact) and the search form appear.
- **Footer:** the "Information" column shows its four links.
- **Layouts:** Home uses the home layout, Search the sidebar-right layout and the E-services portal the narrow layout.
- **Pages:** all of them return 200 and none is hidden. They have no content yet, and the "Welcome to your default website" element is deleted.

**Files (nothing committed):**
- `Build/Initialisation/seed-page-tree.php`: the script that creates the tree. To change the tree, change this script, not the pages by hand.
- `config/sites/main/settings.yaml`: points the service menu, footer menu and search page settings at pages 22, 23 and 16.
- `packages/mtosafi_sitepackage/Initialisation/data.xml`: the exported tree, 26 pages.
- `packages/mtosafi_sitepackage/Initialisation/Site/main/`: a copy of the site config. I changed its base to `/` so it works on any domain.
- `packages/mtosafi_sitepackage/README.md`: how the tree is made, plus a warning about the three page IDs.
- `packages/mtosafi_sitepackage/composer.json` and `composer.lock`: the package now requires `typo3/cms-impexp`, without which the import is silently skipped.

**Not proven yet:** whether a fresh installation gets the pages, the site and working menus. In particular, I don't know whether the three page IDs in the settings come out the same there, so that sentence in the README is untested. Checking it means emptying the database, running `typo3 setup`, checking the site, then restoring with `ddev snapshot restore after-page-tree` and `git restore config/system/settings.php`. `typo3 setup --force` rewrites that file, so it needs restoring too. Your current work is saved in that snapshot, and the original state is still in `before-page-tree`.

Do you want me to run that test, or leave it unproven for now? If you'd rather do it yourself, typing the commands with `! ` in front runs them in this session.

## 6. Prompt

> show me the result in a browser

I opened https://demo.ddev.site/revenue-payments/land-rates in your browser. I picked that page because it shows the most at once: the full main menu, the breadcrumb, the sidebar sub-navigation for Revenue & payments, the service links and search form in the header, and the footer's "Information" links.

Other pages to compare:
- **Home**, https://demo.ddev.site/, home layout
- **Search**, https://demo.ddev.site/search, sidebar on the right
- **E-services portal**, https://demo.ddev.site/e-services-portal, narrow layout

The pages have no content yet, so the main area under each heading is empty. The slider, tiles, news and forms come next.
