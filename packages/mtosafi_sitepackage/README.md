# Mto Safi sitepackage

Site set, page layouts, page templates and styles of the Mto Safi Municipality
website, built from the static design in `design/`.

## Initial content

The package is a distribution: `typo3 extension:setup` imports
`Initialisation/data.xml` once, on an installation where it was not imported
before. The import brings the page tree of the design: the home page, the main
navigation with the Revenue & payments section, the search, login and footer
pages, and two folders whose shortcuts make up the service and the footer menu.
The pages carry no content yet.

`Initialisation/Site/main/` is the site configuration that comes with it. It is
copied only where no site called `main` exists, and it answers on base `/`.

The tree is not edited by hand. `Build/Initialisation/seed-page-tree.php` in the
project writes it into an installation, and it is exported from there:

    ddev exec php Build/Initialisation/seed-page-tree.php
    ddev exec typo3 impexp:export --type=xml --pid=1 --levels=999 \
        --table=pages --table=tt_content --include-related=sys_file \
        --include-related=sys_file_reference --include-related=sys_file_metadata \
        --save-files-outside-export-file data

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
