# Stage 09-search: prompts and answers

## 1. Prompt

> How would you recommend to implement the search?

I'd use **ke_search** (version 7.5.0 on TER, works with TYPO3 13 and 14). It covers your `design/search.html` mock-up with very little custom code.

#### What the design needs

The mock-up (`design/search.html`) shows:
- Results of three kinds, each with a badge: **Service** (pages), **News** (EXT:news records) and **Document** (PDFs).
- A sidebar filter with "Show only" checkboxes for those three kinds.
- A count and the search term in the heading.

Your sitepackage already has some of this in place: the header form (`Header.fluid.html`), the `searchPage` setting (page 16), `q` excluded from cHash, the `SidebarRight` page layout, and `TYPO3SEARCH_begin/end` markers in the news list.

#### Options

| | Pages | News | PDFs | Type filter | Effort |
|---|---|---|---|---|---|
| **ke_search** | ✅ indexer | ✅ built-in news indexer | ✅ needs `pdftotext` | ✅ facets via tags | Low |
| indexed_search (core) | ✅ while pages render | ⚠️ only detail pages a visitor or crawler has opened | ⚠️ external tools | ❌ no real facets | Medium |
| Solr | ✅ | ✅ | ✅ | ✅ | High (needs a Solr server) — too much for this site |
| Custom Extbase SQL `LIKE` | ✅ | ✅ | ❌ | ✅ | Medium, and no ranking |

#### Why ke_search

- **It indexes from the database, not from rendered pages.** News records get indexed without a crawler, and the index doesn't depend on someone opening a page first.
- **The filter maps onto facets.** Give each indexer a tag (`service`, `news`, `document`) and the "Show only" checkboxes become a ke_search filter of type checkbox.
- **The output is Fluid.** You can override the templates in the sitepackage, the same way you already do for News under `Resources/Private/Extensions/`, and reproduce the markup: `ol.search-results`, `.badge`, `data-type`.

#### Implementation steps

1. `composer require tpwd/ke_search` (check the Composer name with `composer show`), then run `typo3 extension:setup`.
2. Add a "Search indexes" storage folder, and configure three indexers there: pages (limited to the service/content tree), news (pointing at the "News records" folder) and files (fileadmin documents). Each indexer adds its tag.
3. Create a filter "Type" with three options whose tags match the indexers.
4. On page 16, place the **search box + results** plugin in `main`. Put the filter in `sidebar` (the "Sidebar right" layout), or render the facets inside your result template.
5. **Connect the header form.** ke_search reads `tx_kesearch_pi1[sword]`, not `q`. Either rename the input in `Header.fluid.html` (and switch the cHash exclusion to `tx_kesearch_pi1[sword]`), or keep `q` and add a small route enhancer/middleware that maps it. I'd rename it: it's simpler, and the `q` exclusion in `ext_localconf.php` goes away.
6. Put the template overrides under `Resources/Private/Extensions/KeSearch/` and wire them through the site set's TypoScript.
7. Schedule the indexer with the `ke_search:indexing` command in the scheduler or as a DDEV cron job. For a demo you can also run it by hand after import.
8. **Distribution content:** add the indexer and filter records to `Initialisation/data.xml` so a fresh install has search working. Add `pdftotext` (poppler-utils) to `.ddev/config.yaml` with `webimage_extra_packages`.

I checked TER for ke_search's versions; I haven't installed it or tested any of these steps here yet. Want me to go ahead with it as the next stage (`09-search`)?

## 2. Prompt

> who maintains ke_search?

The registries I checked don't name a maintainer, so most of this answer is from memory and I haven't verified it.

**Confirmed from TER and Packagist:**
- The current package is **`tpwd/ke_search`**. Version 7.5.0 came out on 2026-11-13 and supports TYPO3 13 and 14.
- It has about 1.3 M downloads on TER and about 1.2 M on Packagist.
- An older package, **`teaminmedias-pluswerk/ke_search`**, is still listed on Packagist with about 400 k downloads. That fits the package having moved from one vendor to another.

**From memory:**
- The lead developer for many years has been **Christian Bülter**.
- "ke" stands for **Kennziffer**, the agency that first built the extension. It then moved to team+medias / +Pluswerk, and is now with **tpwd**, the vendor in the current package name.
- It's open source on GitHub, and there's a paid support and sponsoring option (ke_search premium).

To confirm, check the `authors` field in `composer.json` and the TER page: https://extensions.typo3.org/extension/ke_search

Since releases came out on 2026-10-09, 2026-11-06 and 2026-11-13, it's clearly actively maintained.

## 3. Prompt

> what does it cost?

The base extension costs nothing, and the search we planned doesn't need the paid add-on.

| Version | Price (net, plus VAT) | What it adds |
|---|---|---|
| **ke_search (base)** | **0 €** | Indexers for pages, news and files, plus facets and filters. That's everything the search page design needs. |
| **ke_search premium** | **890 €** (existing customers get 100 € off) | Autocomplete, "Did you mean…?", synonyms, boost keywords, custom ranking, in-word search, headless JSON output, remote indexer, Sphinx for very large data sets, distance search with a map. Also 1 hour of support, a 6-month upgrade guarantee and access to the GitHub repository. |
| **AI version** | 290 € (free when you order premium) | The pricing page doesn't describe it. |

- **One-time or yearly?** The pricing page doesn't say. Ask TPWD before relying on either.
- **Who can buy:** premium is sold only to business customers, and you pay by invoice after ordering.
- **Support for the free version** can be bought as needed.
- **Licence:** none of the pages I read names the base extension's licence. I'd expect GPL, as usual for TER extensions, but check `composer.json` after installing.

**Correction to my last answer:** kesearch.de names the maintainer as **TPWD AG** in Berlin, a dedicated TYPO3 team there. The docs credit the "ke_search Dev Team". I haven't confirmed the person's name I gave from memory.

For this demo the free base version is enough. Autocomplete is the only premium feature I'd expect anyone to miss.

Sources:
- [kesearch.de – prices](https://www.kesearch.de/preise)
- [kesearch.de](https://www.kesearch.de/)
- [ke_search docs – Premium version](https://docs.typo3.org/p/tpwd/ke_search/main/en-us/Premium/Index.html)
- [ke_search docs 7.1 – Premium](https://docs.typo3.org/p/tpwd/ke_search/7.1/en-us/_sources/Premium/Index.rst.txt)

## 4. Prompt

> is there a free option?

Yes. The **base ke_search I recommended is free** (0 €), and it covers everything the search page design needs: pages, news and PDFs, the type badges and the "Show only" filter. The 890 € premium version only adds extras such as autocomplete and "Did you mean…?".

Other free options:

| Option | Fit for this site |
|---|---|
| **ke_search (base)** | ✅ Best fit. It covers the whole design. |
| **indexed_search** (`typo3/cms-indexed-search`, TYPO3 core) | ⚠️ It only indexes pages after they've been rendered, so news and PDFs are unreliable. It has no real type filter. |
| **ext:solr + Apache Solr** | ⚠️ The extension is free, but you have to run a Solr server. That's too much for this site. |
| **Your own Extbase search** (SQL `LIKE` over pages and news) | ⚠️ No PDFs, no ranking, and more code to maintain. |

So the plan doesn't change: free ke_search.

## 5. Prompt

> Our other websites are using solr I believe

That changes my recommendation. I called Solr "too much" because you'd have to run a server, but if your team already runs Solr and knows it, that cost is mostly paid. I haven't checked your other projects (I stay inside this one), so this assumes they really do use Solr.

**ext:solr works with this site.** Version 14.0.2 came out on 2026-09-16 and requires TYPO3 14.3 or newer. You're on 14.3.10.

**Why Solr makes sense if you already run it:**
- **Same setup as your other sites:** the same knowledge, the same hosting, and one way of doing search.
- **Everything in the design is covered:** pages, news through the TypoScript record indexer, PDFs through Tika, and type facets for the "Show only" filter.
- **Features ke_search only has in premium come free:** autocomplete, "Did you mean…?", synonyms and better ranking.

**What it costs:**
- This site needs its own Solr core on your Solr server, plus Tika if you want PDF text.
- Local development needs Solr in DDEV, for example the `ddev/ddev-solr` add-on with the configsets from EXT:solr.
- Setup is heavier than ke_search: connection settings per site, an index queue, and scheduler tasks. A fresh install from the distribution also only gets working search if Solr is reachable.

**How to decide:**
- **Use Solr** if this site goes live on the same hosting as your other sites, or if search should behave the same across all of them.
- **Use ke_search** if this is a standalone demo, for example a conference demo that has to come up with `ddev start` and nothing else.

Can you confirm the other sites use Solr, and tell me what this site is for? Then I'll plan the `09-search` stage the right way.

## 6. Prompt

> what core version would we need

You'd need **Apache Solr 10.0.0** with the **`ext_solr_14_0_0` configset**, which comes with EXT:solr 14.0. EXT:solr 14 itself needs TYPO3 14.3 or newer and PHP 8.2 or newer, and this site has both.

| Component | Version |
|---|---|
| EXT:solr | 14.0.x (latest is 14.0.2) |
| Apache Solr server | **10.0.0**, a hard requirement since 14.0.0 |
| Configset | `ext_solr_14_0_0` |
| EXT:tika (text from PDFs) | 14.0 |
| EXT:solrfal (indexing files) | 14.0, **funding contribution, so paid** |

**What this means for your other sites:**
- If they're on EXT:solr 13 or older, their Solr server is most likely **version 9**. That server can't host a core for this site. You'd need a Solr 10 instance, or upgrade the shared server along with all the other sites, which also means a schema update and a full re-index.
- If they're already on TYPO3 14 with EXT:solr 14, you can add a core for this site to the existing server.
- You can ask the existing server for its version in the Solr admin UI or at `/solr/admin/info/system`.

**Correction to my last answer:** I said Solr covers PDFs through Tika. That's only half right. Tika is open source, but the extension that puts files from fileadmin into the index, **EXT:solrfal**, is a paid funding-contribution extension. Pages and news are free with Solr. For the "Documents" results in the design you'd need solrfal, unless your other sites already have a funding partnership that includes it. Free ke_search does index PDFs.

Sources:
- [EXT:solr version matrix](https://docs.typo3.org/p/apache-solr-for-typo3/solr/main/en-us/Appendix/VersionMatrix.html)
- [EXT:solr 14.0.0 release notes](https://github.com/TYPO3-Solr/ext-solr/releases/tag/14.0.0)
- [EXT:solr 14.0.2 release](https://github.com/TYPO3-Solr/ext-solr/releases/tag/14.0.2)
- [EXT:solr README](https://github.com/TYPO3-Solr/ext-solr)

## 7. Prompt

> oh we dont have a solr core version 10.0 yet. Can we go with ke_search now and replace it with solr once cores become available?

Yes, that works well. ke_search and EXT:solr have the same overall shape: a search page, indexers or index queues, facets, and Fluid templates. Most of what we build can stay when you switch. Set up a few things now to keep the switch cheap.

**Things to build so they outlive ke_search**
1. **Keep the search page (uid 16)** and the `mtosafi.navigation.searchPage` setting. Only the plugin on the page changes.
2. **Make the query parameter a setting.** ke_search uses `tx_kesearch_pi1[sword]` and Solr uses `tx_solr[q]`. If the header form takes the field name from a site setting such as `mtosafi.search.queryParameter`, switching becomes a one-line change. The cHash exclusion in `ext_localconf.php` has to follow it too.
3. **Write the result markup as partials of our own**: the result item, the badge and the filter list. These are the `ol.search-results`, `.badge` and `data-type` parts of the design. Keep the ke_search templates thin wrappers around them, so the Solr templates can reuse the same partials and the CSS stays as it is.
4. **Use the same type keys everywhere.** Name the ke_search tags `service`, `news` and `document`, and use the same values for the Solr facet later. Then the badges and filters don't change.
5. **Use free ke_search features only.** Nothing from premium, so you don't miss anything after the switch.

**What gets thrown away when you switch**
- The indexer and filter records, both in the database and in `Initialisation/data.xml`.
- The ke_search composer dependency, the scheduler task and the `pdftotext` package in DDEV.
- On existing installations, the `tx_kesearch_*` tables: remove them with the database analyzer after uninstalling.

**Plan the PDFs now.** With Solr, the "Documents" results need EXT:solrfal, which is paid. Before you switch, check whether your other sites already have a funding partnership that covers it. Otherwise the switch loses PDF search.

Shall I start the `09-search` stage this way? I'd begin with the dependency and the indexer setup, then the templates in the design markup, the header form setting and the distribution content.

## 8. Prompt

> yes, go ahead, skip the pdfs

Search now works with the free ke_search (pages and news, no PDFs), and the shipped content sets it up on a fresh installation. Nothing is committed yet. But the fresh-install test showed that the import doesn't keep record IDs, and that breaks two things from earlier stages, including the header search on a fresh install (details below).

**What I changed**
- **Dependency:** `tpwd/ke_search` ^7.5 is required by the project and the sitepackage, and added to the site set.
- **Header search form unchanged:** I set ke_search to read the search word from `q`, so the form still sends `q`.
- **Search page (16):** the result list is in the main area. The sidebar holds the search-box plugin, which here shows only the "Show only" filter, because the search field is the one in the header.
- **Templates:** overrides in `Resources/Private/Extensions/KeSearch/` produce the design's markup and use the CSS you already have. Each result goes through `Extensions/Search/Partials/Search/Result.html`, which has nothing ke_search-specific in it, so Solr can reuse it later.
- **Two small PHP classes:**
  - `TagIndexEntryWithResultType` tags each index entry with its type (`service` or `news`). I did it in code because ke_search's own indexer setting points at the filter option by record ID, and the fresh-install test showed those IDs change on import.
  - `AddSearchWordToResultList` passes the search word to the result list, which ke_search only gives to the search box. That's what makes "3 results for “water”" possible.
- **Seed and export:** `Build/Initialisation/seed-search.php` creates the "Search index" folder, the filter, two indexers and both plugins. I re-exported `data.xml` with the three ke_search tables added, and updated the README.
- **Minimum word length:** lowered from 4 to 3 in `config/system/settings.php`, so words like "tax" and "fee" can be searched.

**How I tested it**
- On this installation: searches with results, with none, empty, with a too-short word, and with each filter.
- I also checked that a search word containing HTML is escaped.
- On an empty database rebuilt from the package with `typo3 setup`: the records arrived, the index built, the search and filters worked, and `/`, `/news`, `/contact` and `/search` all returned 200.
- Afterwards I put your database snapshot and config back. I also deleted a `public/.htaccess` that the test setup had written.

**Things you should know**
- **Only news shows up for now.** ke_search skips pages that have no indexable content, and the service pages are still empty. They'll appear once they have text.
- **Nothing builds the index automatically.** Run `ddev exec typo3 ke_search:indexing` after setup and after content changes. There's no scheduler extension installed, so a live site would need a cron job.
- **The search page heading still reads "Search".** The design has "Search results for “…”" as the heading; for now that wording appears in the line above the results.
- **The search-box plugin's "Filters" field still refers to the filter by record ID.** It's correct after a fresh import, where it's the only filter, and the README says so.

**Two bugs from earlier stages, not fixed:**
1. **The page IDs in the site settings are wrong on a fresh install.** The README says a fresh import keeps the same IDs, but it doesn't. Search landed on 20 instead of 16, so the header search form sent people to "Access to information". The service and footer menus (22/23) and the 404 page (33) are also off.
2. **Every news item gets the "Education" category on a fresh install,** on top of its real one.

Should I fix those two next? Otherwise I can commit this as "Stage 09-search" now.

## 9. Prompt

> Why does "new" not find anything?

"new" finds nothing because of how MySQL/MariaDB's full-text search is set up, not because of ke_search or our templates. ke_search stores its index in a MyISAM table (`ENGINE = MyISAM` in its `ext_tables.sql`). MyISAM's full-text index leaves out "new" for two reasons:

1. **Word length.** `ft_min_word_len` is **4** on this server, so words of 1 to 3 letters never go into the full-text index. Lowering `searchWordLength` to 3 in ke_search only stopped the "too short" message; the database still drops those words. "tax" and "fee" fail the same way.
2. **Stopwords.** MyISAM's built-in stopword list includes **"new"**, so it would be ignored even with a lower minimum length. The InnoDB settings you may have seen (`innodb_ft_min_token_size = 3`) don't apply to this table.

What I checked: `MATCH(...) AGAINST('+new*')` returns 0 rows, while a plain `LIKE '%new %'` finds 4 index entries. The words are in the index table, but the full-text index skips them.

**Fix:** change the database server settings in DDEV and rebuild the full-text index:

```ini
# .ddev/mysql/fulltext.cnf
[mysqld]
ft_min_word_len = 3
ft_stopword_file = ''
```

Then `ddev restart`, followed by `REPAIR TABLE tx_kesearch_index QUICK` or simply re-running `ddev exec typo3 ke_search:indexing`.

The catch is that this is a server setting. The live database server needs the same two lines or the problem comes back there, and on shared hosting you often can't change it. Turning off stopwords also lets words like "the" match, but ke_search ranks results by relevance, so that does little harm.

Should I add the DDEV config, rebuild the index and note the hosting requirement in the README?
