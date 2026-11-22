# Mto Safi sitepackage

Site set, page layouts, page templates and styles of the Mto Safi Municipality
website, built from the static design in `design/`.

## Initial content

The package is a distribution: `typo3 extension:setup` imports
`Initialisation/data.xml` once, on an installation where it was not imported
before. The import brings the page tree of the design: the home page, the main
navigation with the Revenue & payments section, the search, login and footer
pages, and two folders whose shortcuts make up the service and the footer menu.
It also brings the news: the "News" list page with its "Article" detail page,
the "News records" folder with the categories and news from
`design/docs/news-and-events.docx`, and the news plugins on the home page, the
list page and the detail page. The home page carries the slider and the service
tiles above the news; the slider images come along in `Initialisation/data.xml.files/`
and land in `fileadmin/user_upload/slides/`. The "Page not found" page carries the
text the site shows for an address that does not exist. The other pages carry no
content yet.

`Initialisation/Site/main/` is the site configuration that comes with it. It is
copied only where no site called `main` exists, and it answers on base `/`. Its
error handling shows the "Page not found" page for HTTP 404.

The tree is not edited by hand. Four scripts in the project write it into an
installation, `Build/Initialisation/seed-page-tree.php` the pages,
`Build/Initialisation/seed-news.php` the news on top of them,
`Build/Initialisation/seed-home.php` the slider and the service tiles of the home page
and `Build/Initialisation/seed-not-found.php` the "Page not found" page with the
404 error handling of the site, and it is exported from there:

    ddev exec php Build/Initialisation/seed-page-tree.php
    ddev exec php Build/Initialisation/seed-news.php
    ddev exec php Build/Initialisation/seed-home.php
    ddev exec php Build/Initialisation/seed-not-found.php
    ddev exec typo3 impexp:export --type=xml --pid=1 --levels=999 \
        --table=pages --table=tt_content --table=sys_category \
        --table=tx_news_domain_model_news --table=tx_news_domain_model_link \
        --table=tx_news_domain_model_tag --table=tx_mtosafisitepackage_tile \
        --table=tx_mtosafisitepackage_slide \
        --include-related=sys_file \
        --include-related=sys_file_reference --include-related=sys_file_metadata \
        --save-files-outside-export-file data

A table missing from that list is missing from the export without a warning.

The export lands in `public/fileadmin/user_upload/_temp_/importexport/`; replace
`data.xml` and `data.xml.files/` in `Initialisation/` with it.

### Page uids in the site settings

`Initialisation/Site/main/settings.yaml` names three pages by uid:
`mtosafi.navigation.serviceMenu`, `footerMenu` and `searchPage`. The import
remaps the root page in the site configuration and nothing else. These three are
right on an installation that starts empty, where the import hands out the uids
the export had. On any other installation, set them in the site settings
(Site Management > Settings) to the "Service navigation" folder, the
"Footer navigation" folder and the "Search" page.

`Initialisation/Site/main/config.yaml` names one more: the 404 error handling
shows `t3://page?uid=33`, the "Page not found" page. On any other installation,
point it at that page in Site Management > Sites > Error Handling.

The news plugins name their pages (the news folder, the list and the detail
page) in FlexForm fields the import remaps, so they need no site setting.

### An installation the seed scripts wrote

`typo3 extension:setup` imports `data.xml` on every installation that has not
recorded the import. An installation the seed scripts filled has not, so the
setup would add a second page tree next to the seeded one. Record it once before
the first setup there:

    ddev mysql -e "INSERT INTO sys_registry (entry_namespace, entry_key, entry_value)
        VALUES ('extensionDataImport', 'mtosafi_sitepackage:Initialisation/dataImported', 'i:1;')"

## Editors

The setup creates the backend user group "Editors", after the import, on every
installation that has no group of that title (see
`Classes/Initialization/CreateEditorGroupOnPackageInitialization.php`). A group
and a file mount live on the root level, outside the exported page tree, so they
are built where the package is set up. The group gets:

- the site's page tree as its mount, and every page of it, with all page
  permissions; new pages are its own through `TCEMAIN.permissions` on the root page
- the Page, List and Media modules
- pages, content, news, categories, tags, links, tiles, slides and files, with all
  their fields
- every content type but "HTML" and "Insert records"
- the file mounts "News images", `fileadmin/user_upload/news/`, which is also
  where uploads go, and "Slider images", `fileadmin/user_upload/slides/`

Create the editors' backend users in the backend (System > Backend users) and
put them into "Editors". The text editor offers paragraphs, two heading levels,
bold, italic, lists and links (`Configuration/RTE/Mtosafi.yaml`).

## News

The news come from `georgringer/news`. Its templates are replaced by those in
`Resources/Private/Extensions/News/`, which render the design's cards. A news
list whose plugin chooses the template layout "Home page" renders as the home
page section, with the content element's header as its heading and a link to the
plugin's list page. The detail page uses the page layout "Article", which leaves
the heading to the news. Addresses are `/news/article/<news>` and
`/news/page-2` (`Configuration/Sets/Sitepackage/route-enhancers.yaml`).

## Service tiles

The content element "Service tiles" (group "Mto Safi" in the content wizard)
renders the design's "Popular services": a heading and a grid of tiles, each
with an emoji as icon, a label and a link. The tiles are records of
`tx_mtosafisitepackage_tile` that belong to the element and are edited, sorted,
hidden and translated inside its form. The heading is required, because the
section is named by it; header type "Hidden" keeps it for screen readers only.
The template is `Resources/Private/Templates/Content/Tiles.fluid.html`, the
page module preview `Resources/Private/Templates/ContentPreviews/Tiles.fluid.html`.

## Slider

The content element "Slider" (group "Mto Safi" in the content wizard) renders the
carousel at the top of the design's home page: slides with a large image and a
caption with a heading, an optional text and an optional button. The slides are
records of `tx_mtosafisitepackage_slide` that belong to the element and are
edited, sorted, hidden and translated inside its form. The button shows only where
a slide has both a button text and a link.

The element's header is not shown. It is required, because screen readers
announce the carousel by it ("Highlights" on the home page). The images are
decoration behind the caption and render without alternative text, so the heading
and the text have to carry the message. They are cropped to 1600 × 560 pixels.

Previous, next, the dots, the arrow keys and autoplay every six seconds come from
`Resources/Public/JavaScript/slider.js`, which the template loads through the
asset collector on the pages that carry a slider. Autoplay stops when the focus
moves into the slider and stays off where the visitor prefers reduced motion. The
labels the script writes are in `Resources/Private/Language/frontend.xlf`. The
template is `Resources/Private/Templates/Content/Slider.fluid.html`, the page
module preview `Resources/Private/Templates/ContentPreviews/Slider.fluid.html`.
