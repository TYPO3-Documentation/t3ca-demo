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
list page and the detail page. The events of the document are news records too,
marked as events, in the same folder; they have their own "Events" list page with
an "Event" detail page, and the home page lists the upcoming ones below the news.
The home page carries the slider and the service
tiles above the news; the slider images come along in `Initialisation/data.xml.files/`
and land in `fileadmin/user_upload/slides/`. The "Page not found" page carries the
text the site shows for an address that does not exist. Every other page carries
example content: a "Text & Images" element with an illustration, most of them a
table, a list or the menu of the subpages beside it; "Trade & tourism" also has a
gallery of four images. Every image has an alternative text and a caption, which
shows under it and in the lightbox. The illustrations land in
`fileadmin/user_upload/pages/`. The texts are made up for the fictional
municipality; the accessibility statement and the privacy policy say that they are
placeholders, because their real text is the municipality's to write. The "Contact" page
carries the contact form beside the address and the opening hours. The "Search index" folder holds what the search needs: the filter
"Show only" and the indexers for pages and news (see Search below).

`Initialisation/Site/main/` is the site configuration that comes with it. It is
copied only where no site called `main` exists, and it answers on base `/`. Its
error handling shows the "Page not found" page for HTTP 404.

The tree is not edited by hand. Eight scripts in the project write it into an
installation, `Build/Initialisation/seed-page-tree.php` the pages,
`Build/Initialisation/seed-news.php` the news on top of them,
`Build/Initialisation/seed-home.php` the slider and the service tiles of the home page
and `Build/Initialisation/seed-not-found.php` the "Page not found" page with the
404 error handling of the site, `Build/Initialisation/seed-search.php` the
search and `Build/Initialisation/seed-pages.php` the example content of the other
pages, with the illustrations in `Build/Initialisation/images/`, and
`Build/Initialisation/seed-contact-form.php` the contact form on the "Contact" page,
and `Build/Initialisation/seed-events.php` the events with their pages and the events
list on the home page, and it is exported
from there:

    ddev exec php Build/Initialisation/seed-page-tree.php
    ddev exec php Build/Initialisation/seed-news.php
    ddev exec php Build/Initialisation/seed-home.php
    ddev exec php Build/Initialisation/seed-not-found.php
    ddev exec php Build/Initialisation/seed-search.php
    ddev exec php Build/Initialisation/seed-pages.php
    ddev exec php Build/Initialisation/seed-contact-form.php
    ddev exec php Build/Initialisation/seed-events.php
    ddev exec typo3 impexp:export --type=xml --pid=1 --levels=999 \
        --table=pages --table=tt_content --table=sys_category \
        --table=tx_news_domain_model_news --table=tx_news_domain_model_link \
        --table=tx_news_domain_model_tag --table=tx_mtosafisitepackage_tile \
        --table=tx_mtosafisitepackage_slide \
        --table=tx_kesearch_indexerconfig --table=tx_kesearch_filters \
        --table=tx_kesearch_filteroptions \
        --include-related=sys_file \
        --include-related=sys_file_reference --include-related=sys_file_metadata \
        --save-files-outside-export-file data

A table missing from that list is missing from the export without a warning.

The export lands in `public/fileadmin/user_upload/_temp_/importexport/`; replace
`data.xml` and `data.xml.files/` in `Initialisation/` with it.

### Page uids in the site settings

The site settings `mtosafi.navigation.serviceMenu`, `footerMenu` and
`searchPage` and `mtosafi.events.detailPage` name pages by uid, and so does the 404 error handling
(`t3://page?uid=…`). Those are the uids the pages had where the export was made.
The import gives every page a new uid and remaps only the root page of a site
it copies from `Initialisation/Site/`.

`Classes/Initialization/RemapSitePageUidsOnPackageInitialization.php` translates
the other five with the uid map the import kept, in the setup that imported the
data. It changes the site `main` where its root page is one of the imported
pages, whether the site came from `Initialisation/Site/main/` or was in
`config/sites/main/` already. A uid the import did not bring stays as it is.

Elsewhere, set them in the site settings (Site Management > Settings) to the
"Service navigation" folder, the "Footer navigation" folder, the "Search"
page and the "Event" page, and the error handling (Site Management > Sites > Error Handling) to the
"Page not found" page.

The news and events plugins name their pages (the news folder, the list and the
detail page) in FlexForm fields the import remaps, so they need no site setting.

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
  where uploads go, "Slider images", `fileadmin/user_upload/slides/`, and "Page
  images", `fileadmin/user_upload/pages/`

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
`/news/page-2` (`Configuration/Sets/Sitepackage/route-enhancers.yaml`). On the
detail page, the breadcrumb ends with the title of the news instead of the page
"Article" (`Classes/DataProcessing/NewsTitleInBreadcrumbProcessor.php`, built on the
`AddNewsToMenuProcessor` of EXT:news); on "Event" with the title of the event.

## Events

The events come from `georgringer/eventnews`, which makes a news record an event:
"Is event" beside the date, then an end, "Full day" and, on the tab "Eventnews",
a place ("Location (simple)"). The date of the news is the start. The extension ships
no site set; the site set imports its TypoScript, which makes every news plugin list
news only unless the plugin chooses otherwise on its tab "Eventnews".

Events have pages of their own, beside the news: the "Events" page lists every
upcoming event, and its subpage "Event" shows one, at `/events/event/<event>`. So the
breadcrumb of an event leads to the events and not to the news. The list plugins
choose "Only events", the time limit `today` and the order by date, oldest first; an
event that has started stays in the list until its last day. The one on the home page
uses the template layout "Home page: the upcoming events", lists the next three and
links to the "Events" page; the one on the "Events" page uses "Events page: all
upcoming events". Both name "Event" as detail page. The detail plugin on "Event"
names "Events" as list page, which the detail page links to as "All events".

Each event in a list shows a date badge, the title as link to the detail page and the
teaser; an event over several days has its first and its last day in the badge, one
on each line. The detail page shows the days and the place instead of the date of the
news. The partial is `Resources/Private/Extensions/News/Partials/List/Event.html`.

The search indexes events with the news, through the indexer "News", which knows
one detail page for all of them. `Classes/Search/LinkEventsToEventPage.php` links the
events in the index to the page of the site setting `mtosafi.events.detailPage`
(Site Management > Settings > Mto Safi > Events) instead. Like "Article", the "Event"
page is excluded from the page indexer.

An event can have a registration: on the tab "Eventnews", "Registration" takes a page,
a website or an email address, and "Text of the registration button" the text of the
button, "Register" where it is empty. The detail page shows the button below the text;
the list on the home page does not. The two fields are the sitepackage's own
(`Configuration/TCA/Overrides/tx_news_domain_model_news.php`). They reach the
templates as `registrationLink` and `registrationLabel` through the class extension of
EXT:news, which merges `Classes/Domain/Model/News.php` into its news model, as
EXT:eventnews does with its fields; after a change to that class, flush the caches.

The site shows days, not times: PHP runs on the installation's time zone, which is
not necessarily the municipality's. The seeded events carry their times in the text,
together with the cost and the registration, which have no field; the teaser repeats
the place and the time the way the design does.

The home page is cached. An event leaves the list when the page cache is rebuilt
after its last day, at the latest when the cache expires.

## Text, images and tables

The elements of fluid_styled_content render with its own templates. The site set
depends on `typo3/fluid-styled-content-css`, which places the images of "Text &
Images" beside or above the text; `Resources/Private/Scss/_components.scss` gives
them and the table the design's look, and puts an image beside the text above it on
small screens.

An image's description is its caption, under the image and in the lightbox.

An image with "Enlarge on click" opens full size (up to 1920 pixels) in a lightbox, a
modal dialog with previous and next for the images of the same element, the arrow
keys, Escape and a click beside the image to close. New elements have "Enlarge on
click" on (`TCAdefaults` in `page.tsconfig`). The link goes to the large image, so it
works without JavaScript too. The site replaces one partial of fluid_styled_content
for it, `Resources/Private/Templates/Partials/Content/Media/Type/Image.fluid.html`,
which loads `Resources/Public/JavaScript/lightbox.js` through the asset collector on the
pages that need it. The images of a news article open in the same lightbox. The labels
the script writes are in `Resources/Private/Language/frontend.xlf`.

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

## Contact form

The "Contact" page uses the page layout "Sidebar right": the content element
"Form" in the main area, the address, the opening hours and "Report a problem" in
the sidebar. The form comes from the core form framework (`typo3/cms-form`, set
`typo3/form`). Its definition is a file of the package,
`Resources/Private/Forms/contact.form.yaml`, so it is under version control. The
Forms module lists it but cannot change or delete it; change the file instead and
flush the page cache, because the first page of a form is cached.

`Configuration/Form/Sitepackage/config.yaml` is the form setup of the site. EXT:form
finds it by its place and merges it over its own. It allows the form directory of
the package and gives the form elements the classes of the design (`form-field`,
`form-hint`, `form-error`, `has-error`, `check`, `button`) instead of Bootstrap's.
The form tag gets the class `form` from
`Resources/Private/Extensions/Form/Templates/Form.fluid.html`, the core template with
that one attribute added.

A message goes to `info@mtosafi.example`, with the visitor's address as reply-to.
It is sent from `noreply@mtosafi.example`; the server that sends the mail has to be
allowed to send for that domain. Editors change the recipient in the content
element ("Override finisher settings") without touching the file. The mail uses the
layout of the site, `Resources/Private/Extensions/Form/Layouts/Email.fluid.html` and
`.txt`, instead of the core layout "SystemEmail" with the TYPO3 logo. The consent
checkbox is left out of it. On DDEV it lands in Mailpit (`ddev mailpit`).

The visitor gets no copy by mail, only the confirmation on the page. A copy would go
to whatever address was entered, with the text that was entered, so anybody could
use the form to send mail from the municipality's domain to a stranger, and a
mistyped address would hand the message to somebody else.

Two things keep spam out. A honeypot field, which people do not see, catches bots
that fill in every field. ALTCHA (`bbysaeth/typo3-altcha`, set
`bbysaeth/typo3-altcha`) adds the field "I'm not a robot": a click lets the browser
solve a small proof-of-work puzzle, and the form is sent only with a solution the
server signed and has not seen solved before. It needs no outside service and sets
no cookie. Its widget loads JavaScript and CSS on the pages that carry a form.

ALTCHA stores every challenge it hands out in `tx_typo3altcha_domain_model_challenge`
so that a solution counts once, a row per form view. Nothing deletes them on its own;
run this regularly, for example from cron:

    vendor/bin/typo3 altcha:remove-obsolete-challenges --include-solved-challenges

The messages themselves are not stored in the database: the mail is the only copy.

ALTCHA still registers its form setup through TypoScript
(`plugin.tx_form.settings.yamlConfigurations`), which TYPO3 14.2 deprecated and 15
removes. It works on 14.3; the extension has to move to `Configuration/Form/` before
the site can go to TYPO3 15.

The consent label links to the privacy policy by its address, `/privacy-policy`,
rather than by page uid, so the link holds on any installation the package is
imported into, as long as the page keeps that address.

## Search

The search comes from `tpwd/ke_search`, without its paid premium add-on. The
search field in the page header sends the search word as `q` to the "Search" page
(`mtosafi.navigation.searchPage`), which ke_search reads through
`plugin.tx_kesearch_pi1.searchWordParameter = q`.

The "Search" page uses the page layout "Sidebar right". Its main area holds the
result list, its sidebar the search box plugin, which on this site renders only
the filter "Show only": the search field is the one in the header. The search box
carries the configuration of both; the result list loads it from there.

The index holds pages and news; files are not indexed. The indexer "Pages" starts
at the home page and takes every page that has indexable content. A page without
it does not appear in the results until it has text.
The "Search", "Article" and "Page not found" pages are excluded with "Include in
search: no". The indexer "News" takes the news of the "News records" folder and
links them to the "Article" page.

Each result has a type, shown as its badge and filtered by "Show only": `service`
for a page, `news` for a news record. The filter options carry these words as their
tags, and `Classes/Search/TagIndexEntryWithResultType.php` writes them into the
index. The indexers do not set the tag themselves, because an indexer names its
filter option by uid and the import does not keep uids.

The templates are in `Resources/Private/Extensions/KeSearch/`. One result renders
through `Resources/Private/Extensions/Search/Partials/Search/Result.html`, which does
not depend on ke_search, so a later switch to another search engine (EXT:solr)
can reuse it with the CSS. `Classes/Search/AddSearchWordToResultList.php` hands
the search word to the result list, which ke_search gives to the search box only.

The index is built by a command, nothing builds it on its own:

    ddev exec typo3 ke_search:indexing

Run it after the setup of a new installation and after content changes; on a live
site, run it from cron. The installation has no scheduler.

The search box plugin names its filter by uid in the field "Filters", which the
import does not remap. It is right where the "Search index" folder holds the only
filter, as it does after the import. Otherwise choose "Show only" there again.

