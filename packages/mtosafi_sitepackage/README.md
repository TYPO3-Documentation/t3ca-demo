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
list page and the detail page. The other pages carry no content yet.

`Initialisation/Site/main/` is the site configuration that comes with it. It is
copied only where no site called `main` exists, and it answers on base `/`.

The tree is not edited by hand. Two scripts in the project write it into an
installation, `Build/Initialisation/seed-page-tree.php` the pages and
`Build/Initialisation/seed-news.php` the news on top of them, and it is exported
from there:

    ddev exec php Build/Initialisation/seed-page-tree.php
    ddev exec php Build/Initialisation/seed-news.php
    ddev exec typo3 impexp:export --type=xml --pid=1 --levels=999 \
        --table=pages --table=tt_content --table=sys_category \
        --table=tx_news_domain_model_news --table=tx_news_domain_model_link \
        --table=tx_news_domain_model_tag --include-related=sys_file \
        --include-related=sys_file_reference --include-related=sys_file_metadata \
        --save-files-outside-export-file data

A table missing from that list is missing from the export without a warning.

The export lands in `public/fileadmin/user_upload/_temp_/importexport/`; move
`data.xml` (and `data.xml.files/`, once there are files) into `Initialisation/`.

### Page uids in the site settings

`Initialisation/Site/main/settings.yaml` names three pages by uid:
`mtosafi.navigation.serviceMenu`, `footerMenu` and `searchPage`. The import
remaps the root page in the site configuration and nothing else. These three are
right on an installation that starts empty, where the import hands out the uids
the export had. On any other installation, set them in the site settings
(Site Management > Settings) to the "Service navigation" folder, the
"Footer navigation" folder and the "Search" page.

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
- pages, content, news, categories, tags, links and files, with all their fields
- every content type but "HTML" and "Insert records"
- the file mount "News images", `fileadmin/user_upload/news/`, which is also
  where uploads go

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
