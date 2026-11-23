# Stage 08-error-page: prompts and answers

## 1. Prompt

> when I click on the search button an error page appears

The search button works now. `/search?q=test` returns 200 instead of the 404 error page, and `/search` and `/` still load normally.

**Cause:** The header search form sends the search term as a `q` parameter in the URL. Your `config/system/settings.php` has `FE.cacheHash.enforceValidation = true`, so TYPO3 rejects any URL that has an unknown parameter but no `cHash` (a security checksum on URL parameters). It answers those requests with "Page Not Found".

**Fix:** I added `q` to the parameters TYPO3 leaves out of that check, in `packages/mtosafi_sitepackage/ext_localconf.php`. I appended it to the existing list so the defaults (`L`, `utm_*` and so on) still apply. I put it in the sitepackage because that's where the search form lives, so the setting goes wherever the sitepackage is installed. The change isn't committed yet.

**Still missing:** The Search page (uid 16) has no content elements, and no search extension is installed. You land on an empty page rather than seeing results. Real search needs something like `indexed_search` or `ke_search`, and its results plugin has to be non-cacheable, because `q` is now ignored when TYPO3 caches the page.

## 2. Prompt

> https://demo.ddev.site/apple-tree gives me the same error page

That one isn't the same bug. `/apple-tree` isn't a page on your site: the tree has no page with that slug, so a 404 is the right answer. What you're seeing is TYPO3's built-in error page. It appears because `config/sites/main/config.yaml` (and the copy in `packages/mtosafi_sitepackage/Initialisation/Site/main/config.yaml`) has `errorHandling: {  }`, so the site has no 404 page of its own.

The search bug only looked the same because it ended in that same built-in page.

To get a styled "Page not found" page inside your site layout, I'd do this:

1. **Add the page:** create a "Page not found" page under Home, hidden from the menu, with a short text and links to Home and Search.
2. **Point the site at it:** add a 404 handler to the site config:
   ```yaml
   errorHandling:
     - errorCode: 404
       errorHandler: Page
       errorContentSource: 't3://page?uid=<new uid>'
   ```
3. **Keep it in the distribution:** add the page to `Build/Initialisation/seed-page-tree.php` and update the Initialisation site config, so a fresh install gets it too.

Should I go ahead? If so, tell me whether you want your own wording for the page text.

## 3. Prompt

> yes go ahead

Addresses that don't exist, like `/apple-tree`, now show a "Page not found" page in the site's own layout instead of TYPO3's built-in error page. It keeps the header, navigation, search and footer, and still returns status 404.

**What's on your site now**
- **New page:** "Page not found" (uid 33) under Home. It's hidden from the menu and marked `noindex`/`nofollow` so search engines skip it. It uses the Narrow layout and has one text element saying the page may have moved or the address may be mistyped, with links to the home page and to Search.
- **Error handling:** `config/sites/main/config.yaml` now sends 404 errors to that page.
- **Checked:** `/apple-tree` and `/revenue-payments/apple-tree` both return 404 and show the new page. `/search?q=x` still returns 200.

**For fresh installs**
- **Seed script:** `Build/Initialisation/seed-not-found.php` writes the page and the 404 setting, the same way earlier stages did. It runs after `seed-home.php`.
- **Package content:** I re-exported `Initialisation/data.xml`. Compared with the old file, only the new page and its text element were added, plus a timestamp on the home page. The image files are unchanged.
- **Shipped site config:** `Initialisation/Site/main/config.yaml` has the same 404 setting.
- **README:** it now lists the fourth script, and it notes that the 404 setting points at page uid 33. That uid is only right on an empty installation, the same limit the search and menu settings already have.

**Not tested:** installing the package on a fresh installation. I couldn't do that here, and the project has no tests that import the file.

Nothing is committed yet. That includes the search fix in `ext_localconf.php` from earlier. Before running the seed script I took a DDEV database snapshot named `before-not-found`, so you can roll back to it.
