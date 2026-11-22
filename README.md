# Demo project

The AI builds the TYPO3 project here, stage by stage.

## Fresh install

A clone holds the code and the configuration, not the database or the files in
`fileadmin/`. These steps bring it up as https://demo.ddev.site/ with the
content the sitepackage ships (`packages/mtosafi_sitepackage/Initialisation/`).

    ddev start
    ddev composer install
    ddev restart
    ddev exec bash -c 'TYPO3_DB_DRIVER=mysqli TYPO3_DB_HOST=db TYPO3_DB_PORT=3306 \
        TYPO3_DB_DBNAME=db TYPO3_DB_USERNAME=db TYPO3_DB_PASSWORD=db \
        TYPO3_SETUP_ADMIN_USERNAME=admin TYPO3_SETUP_ADMIN_PASSWORD="<password>" \
        TYPO3_SETUP_ADMIN_EMAIL=admin@example.com TYPO3_PROJECT_NAME="Mto Safi" \
        TYPO3_SERVER_TYPE=other vendor/bin/typo3 setup --force --no-interaction'
    git checkout config/system/settings.php
    ddev exec typo3 ke_search:indexing

What each step is for:

- `ddev restart` is not optional. DDEV writes `config/system/additional.php`
  (database and trusted hosts) only on a start that finds TYPO3 installed, and
  the first start ran before `composer install`.
- `typo3 setup` creates the tables and the backend user `admin`, and sets up
  every extension. For the sitepackage that imports the page tree, the news, the
  home page and the search from `data.xml`, and creates the backend user group
  "Editors". Choose a password with upper and lower case letters, a digit and a
  special character, or the setup refuses it.
- `--force` is needed because `config/system/settings.php` is in the repository.
  The setup rewrites that file: it drops the extension configuration and writes
  a new encryption key and install tool password. `git checkout` restores the
  committed one; the installation runs on it.
- `typo3 extension:setup` alone does not work on an empty database: it logs in
  the command line user before it creates any table and fails with
  `Table 'db.be_users' doesn't exist`.
- The search index is built by `ke_search:indexing` only, nothing builds it on
  its own.

Log in at https://demo.ddev.site/typo3/ as `admin`.

After the setup, `git status` shows `config/sites/main/config.yaml` and
`settings.yaml` as changed. The import gives the pages new uids, and the
sitepackage points the menus, the search page and the 404 page at them (see
"Page uids in the site settings" in `packages/mtosafi_sitepackage/README.md`).
Keep the change in your installation and do not commit it.

### Starting over

The setup refuses a database that holds tables already, so it cannot run twice
on the same installation. Remove the project with its database and run the
steps again:

    ddev delete --omit-snapshot --yes
    git checkout config/

## Seed scripts

The content in `data.xml` is not edited by hand. It is written by the scripts in
`Build/Initialisation/` and exported from there; the order and the export
command are in `packages/mtosafi_sitepackage/README.md`.
