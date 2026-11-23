# Stage 10-fresh-install: prompts and answers

## 1. Prompt

> try installing the project fresh, do the import scripts etc work?

I installed it fresh. The seed scripts work, but a fresh install from the shipped import ends up with broken navigation, search and 404 page. Your running `demo` install was not touched: I did everything in a separate clone with its own DDEV project, `demo-fresh` (`/tmp/claude-1000/-home-lina-wolf-projects-typo3-t3ca-2026-demo/8142fd57-70b8-4e62-bd7e-1a4a7fb0656d/scratchpad/fresh`).

#### What I ran

1. `ddev start`, `ddev composer install`, `ddev restart` worked. The second start wrote `config/system/additional.php`.
2. `typo3 extension:setup` on the empty database fails with `Table 'db.be_users' doesn't exist`. That is TYPO3's normal behaviour: the command logs in the command-line user before it creates any tables.
3. `typo3 setup --no-interaction` refuses because `config/system/settings.php` is committed. **`typo3 setup --force --no-interaction`** worked, and it also imported `data.xml`: 31 pages, 7 news, 8 content elements and the "Editors" group.
4. The frontend (with the site's address set to the clone's hostname), the backend login page, `/news` and `/search` all returned 200.

#### Problems found

**1. The page uids in the site configuration don't match a fresh import.** `config/sites/main/settings.yaml` and `config.yaml` (and the copies in `Initialisation/Site/main/`) use the uids from your working database. The import gives the pages different uids:

| Setting | Configured | Fresh import |
|---|---|---|
| `serviceMenu` | 22 | 9 |
| `footerMenu` | 23 | 4 |
| `searchPage` | 16 | 20 |
| 404 page | 33 | 14 |

On the fresh site:
- The service and footer menus are empty.
- The header search sends you to `/access-to-information`.
- An unknown address shows "Invalid error handler configuration: t3://page?uid=33".

The sitepackage README says these uids "are right on an installation that starts empty". That is not true, because the import assigns new uids.

**2. There is no documented way to do a fresh install.** The top-level README says nothing about it. The sitepackage README points to `extension:setup`, which fails on an empty database (step 2). The only route that works is `setup --force`, and that rewrites the committed `settings.php`:
- it drops the whole `EXTENSIONS` block (ke_search, news and backend settings),
- it changes `encryptionKey`, `installToolPassword` and `sitename`.

After that, the working tree is dirty and has to be reset with `git checkout config/system/settings.php`.

#### Seed scripts

After emptying everything below the root page, I ran the five scripts in the README's order:
- All five exited 0. They rebuilt the tree, 7 news and 8 content elements, and wrote their own uids into the site configuration (49, 50, 43 and 60), so on that install the menus, search form and 404 page all worked.
- Running each one again stops with exit 1, as intended.
- `typo3 ke_search:indexing` indexed 7 entries, and searching for "water" gives 3 results.

A second `setup --force` on the filled database refuses with "contains already 57 tables". So that step can't simply be run twice.

#### Suggested fixes (I haven't changed anything)

- **For problem 1:** after the import, look the pages up by title and write their uids into the site configuration. This would work like the existing `CreateEditorGroupOnPackageInitialization`. Alternatively, accept the problem and document setting the uids by hand.
- **For problem 2:** add a short "Fresh install" section to the README: `ddev start`, `ddev composer install`, `ddev restart`, `typo3 setup --force -n` with the environment variables, `git checkout config/system/settings.php`, then `typo3 ke_search:indexing`. Optionally wrap it in a DDEV command.

The `demo-fresh` project is still running. Should I remove it with `ddev delete -Oy demo-fresh`, or do you want to look at it first?

## 2. Prompt

> fix the uids with a listener like the editor group

I added the listener, and on a fresh install the menus, search form and 404 page now point at the right pages. It's tested on the `demo-fresh` clone; nothing is committed.

**New file:** `packages/mtosafi_sitepackage/Classes/Initialization/RemapSitePageUidsOnPackageInitialization.php`

- It runs after the core's import, like the editor group listener.
- It doesn't guess by page title. TYPO3's import keeps a map of old page uids to new ones, and the listener uses that to correct the three menu/search settings and the 404 page link of site `main`.
- It only acts in the setup run that actually imported `data.xml`, and only if site `main`'s root page is one of the imported pages. It works whether the site config came from `Initialisation/Site/main/` or was already in `config/sites/main/`. A uid that wasn't imported stays as it is.
- Your live `demo` install isn't affected, because its import is already recorded so the listener never runs there. After a cache flush it still answers 200 on both the frontend and the backend.

**Result on a fresh install** (database emptied, committed site config, `typo3 setup --force -n`):

| Setting | Before | After |
|---|---|---|
| `serviceMenu` | 22 | 9 "Service navigation" |
| `footerMenu` | 23 | 4 "Footer navigation" |
| `searchPage` | 16 | 20 "Search" |
| 404 page | `uid=33` | `uid=14` "Page not found" |

On the frontend, the service menu shows E-services portal and Contact, and the footer shows its four links. The header search goes to `/search` ("water" gives 3 results). `/does-not-exist` returns 404 with the "Page not found" page.

**Also changed:**
- The "Page uids in the site settings" section of `packages/mtosafi_sitepackage/README.md` no longer claims the uids stay the same on an empty install, and it describes the listener.
- The comment above the settings write in `Build/Initialisation/seed-page-tree.php` now says the same.

One side effect: on a fresh install, the listener rewrites the committed `config/sites/main/config.yaml` and `settings.yaml` to the new uids, so a colleague's working tree shows them as changed after setup. That's expected, but they shouldn't commit those changes back.

The fresh-install steps (the `setup --force` part) are still undocumented, and `demo-fresh` is still running. Should I write the README section, draft a commit message, or remove `demo-fresh`?

## 3. Prompt

> write the fresh install section in the README

I added a "Fresh install" section to the top-level `README.md`, and the steps work exactly as written on a brand-new clone. Nothing is committed.

**What the section contains:**
- **The commands:** `ddev start`, `ddev composer install`, `ddev restart`, the unattended `typo3 setup --force --no-interaction` with its environment variables, `git checkout config/system/settings.php`, and `ddev exec typo3 ke_search:indexing`.
- **Why each step is needed:** the second start that writes `additional.php`, the password rules the setup enforces, why `--force` is required and what it overwrites, and why `extension:setup` alone fails on an empty database.
- **Login:** `admin` at `/typo3/`.
- **A note about `git status`:** after setup it shows `config/sites/main/config.yaml` and `settings.yaml` as changed. Those are the remapped uids; keep them locally and don't commit them.
- **"Starting over":** `ddev delete --omit-snapshot --yes` and `git checkout config/`, because the setup refuses a database that already has tables.
- A short pointer to the seed scripts, whose procedure stays in the sitepackage README.

**Test:** I made a second fresh clone (`/tmp/claude-1000/-home-lina-wolf-projects-typo3-t3ca-2026-demo/8142fd57-70b8-4e62-bd7e-1a4a7fb0656d/scratchpad/fresh2`, DDEV project `demo-fresh`) with the uncommitted listener and README copied in, and ran the commands verbatim:
- Every step exited 0 and the index has 7 entries.
- `git status` showed exactly the two site config files the README mentions.
- With the site address pointed at the clone's host, `/`, `/search` and `/typo3/` return 200 and `/does-not-exist` returns 404. The menus and search form are correct, and the `admin` user and the "Editors" group exist.

**Not fully tested:** I ran `ddev delete --omit-snapshot --yes` on the first clone's project and it worked. The `git checkout config/` that follows it is plain git, and I didn't run the whole "Starting over" sequence in one go.

The safety check blocked me from deleting `public/*` in the first clone, which is why I made a second clone instead. Two clone folders are left in the scratchpad (`/tmp/claude-1000/-home-lina-wolf-projects-typo3-t3ca-2026-demo/8142fd57-70b8-4e62-bd7e-1a4a7fb0656d/scratchpad/fresh` and `/tmp/claude-1000/-home-lina-wolf-projects-typo3-t3ca-2026-demo/8142fd57-70b8-4e62-bd7e-1a4a7fb0656d/scratchpad/fresh2`), and `demo-fresh` is still running. Delete the folders yourself if you want them gone. To remove the DDEV project, run `ddev delete --omit-snapshot --yes demo-fresh`.

**Uncommitted changes in your repo:**
- `README.md`
- `packages/mtosafi_sitepackage/README.md`
- `Build/Initialisation/seed-page-tree.php`
- the new `RemapSitePageUidsOnPackageInitialization.php`

Should I draft a commit message for them?
