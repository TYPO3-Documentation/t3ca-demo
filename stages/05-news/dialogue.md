# Stage 05-news: prompts and answers

## 1. Prompt

> The home page is empty. In the design it has a slider, service tiles, news, events and a newsletter sign-up. What do I need for that?

The page itself is ready: the Home layout has a `main` area and the CSS for slider, tiles, cards, events and newsletter is already in the sitepackage. What's missing is anything an editor can put into that area. Each of the five sections needs something different.

| Section in `design/index.html` | What you need | Recommendation |
|---|---|---|
| **Slider** | A content element of your own with slides as child records (image, heading, text, link label, link). Copy `design/js/slider.js` into the package and load it. | Build it in the sitepackage. The core has no slider, and a slider extension would bring markup and JS that don't match the design. |
| **Popular services** (tiles) | A list of links, each with an icon. You can do this with a core menu element ("Menu of selected pages") and your own template. The icon has to be stored somewhere, though: either a field on the page or the element's own items. | A small content element whose items each hold a link, a label and an icon. This keeps the icon off the page record. |
| **News** (3 latest + "All news") | News records with date, category, teaser and body, a list view, a detail page and a "latest 3" view for the home page. | **georgringer/news**: 14.1.2 supports TYPO3 14.3 (`>=13.4.29 <=14.99.99`). Add templates that output the design's `.card` markup, plus a "News" page with a detail page. |
| **Events** | Records with a date, a time and a place, shown as an upcoming list. | `eventnews`, which adds events to the news extension, only supports up to TYPO3 12. So either build a small event record in the sitepackage (I'd do this, there are only a few fields), or use news records in an "Events" category and put the time and place in the teaser. |
| **Newsletter sign-up** | Storing subscribers with topics and consent, a double opt-in email, an unsubscribe link, and something that sends the newsletter. The core form extension can show the form, but it doesn't do double opt-in or manage subscribers. | This depends on your answer to the question below. Either way, copy `design/js/forms.js` over for the validation in the browser. |

**The one decision only you can make: who sends the newsletter?**
- **An external service** (Brevo, Mailchimp, CleverReach…): the site only collects the sign-up and passes it on, and the service handles double opt-in, sending and unsubscribing. This is the least work in TYPO3.
- **TYPO3 itself**: you need an extension such as `codingms/newsletters` (topics plus double opt-in; the CleverReach sync is in the paid Pro version) or `sgalinski/sg-campaigns`. I haven't checked either one for TYPO3 14.3 support yet. I'd do that before picking one.

**Also needed for all of it:**
- **Initial content.** The texts for the slider and the tiles are in `index.html`. The news and events are in `design/docs/news-and-events.docx`. That document has errors to fix before importing it:
  - typos ("the the old market keep there stall number", "Pedestrains", "helth", "volunter", "Contcat")
  - one date set in **2062**
  - at least one entry with no teaser

  After that, everything goes into `Initialisation/data.xml` as described in the package README.
- **Re-exporting the distribution.** Once there are news and event records, the export needs their tables as well (`--table=tx_news_domain_model_news` etc.), and the slider images go into `data.xml.files/`.

I'd go in this order: the slider and the tiles first (they're sitepackage-only), then news, then events, then the newsletter once you've picked how it's sent. Should I start with the slider?

## 2. Prompt

> That sounds like a lot. What should I start with, and why?

I'd start with **News**.

**Why news first:**

1. **It's mostly installing, not building.** `georgringer/news` already supports TYPO3 14.3, so there's no TCA or JavaScript to write. You install it, add a News page and a detail page, and write templates that output the design's `.card` markup. The slider and tiles each need a new content element with its own fields and templates, which is more code.
2. **It's the most real content.** The news and events document is the main thing the municipality keeps updated, and it's the part of the home page that changes every week. The slider and tiles are a few fixed entries that rarely change.
3. **It starts the content cleanup early.** The document has typos, a date set in 2062 and a missing teaser. Someone at the municipality has to fix those. If news goes first, that review can happen while you build the other sections.
4. **It settles events cheaply.** Once news is running, you can see whether news records in an "Events" category are good enough before you build a separate event record.
5. **Other parts link to it.** The home page's "All news" link and the "See the forum dates" slide need news pages to point to.

**It's less work than the table made it look.** The five sections don't depend on each other, and the home page fills in one section at a time. Each step is finished and visible on its own. The newsletter is the only part waiting on your decision about who sends it, and nothing else depends on it, so it can go last.

**Suggested order:** news → tiles (small, quick) → slider → events (once you've seen how news handles them) → newsletter.

Should I set up news now? I'd add the extension with composer, create the News and detail pages in the seed script, and override the list template to match the design's cards.

## 3. Prompt

> will the editors be able to change the news themselves?

Yes. That's one of the main reasons to use the news extension rather than writing news into pages by hand. A few things have to be set up first, though, and none of them exist in the project yet.

**What editors can do once it's set up:**
- Add, edit, hide and delete news in the backend (Web > List on a "News" folder), with title, date, category, teaser, body text and images.
- Schedule news with start and end times, so an entry appears or disappears on its own.
- Manage the categories ("Health", "Roads & transport" and so on).

The home page and the "All news" page update by themselves. The newest three entries always show on the home page without anyone touching it.

**What has to be set up for that to work:**
1. **A storage folder for news records.** Editors work in this folder, and the news plugins on the home and News pages read from it.
2. **A backend user group for editors.** The project has no editor groups or users yet; the only account is the admin you install with. The group needs:
   - access to the News folder and the content pages
   - permission to edit the news and category tables
   - a file mount for news images, so they can't upload anywhere else
3. **Readable URLs.** The site has no route configuration yet, so a news detail page would get a link like `?tx_news_pi1[news]=12`. A route enhancer turns that into `/news/new-fish-market-opens-at-the-jetty`.
4. **A restricted text editor (RTE).** The body text editor should only offer the formatting the design supports, so editors can't paste in styling that breaks the look.

**What editors can't change, and shouldn't:** the card layout, the number of news on the home page and the page structure. Those stay in the sitepackage.

I'd set up the folder, the editor group and the URLs together with the extension, so it's ready for editors from the start. You'd only need to create the actual editor accounts. Should I go ahead with that?

## 4. Prompt

> yes, go ahead with news

News is working on the home page, the News page and on article pages. The "Editors" group is set up, but nothing has been tested on a fresh installation yet. That test needs your OK (see the end).

**What visitors see** (checked on demo.ddev.site)
- The home page shows the three newest news as cards, with "All news" linking to `/news`, matching the design.
- `/news` lists all seven news items. Each one opens at a readable address like `/news/article/new-fish-market-opens-at-the-jetty`. The article's title is the page heading and the browser-tab title. An address that doesn't exist gives a 404.
- The breadcrumb now shows on News and article pages. It was also missing before on the other pages hidden from the menu (the E-services portal, for example), because the breadcrumb skipped pages hidden from the navigation. That's fixed. On article pages it currently ends in "Article" rather than the news title.

**What editors get**
- A "News records" folder holding 8 categories and 7 news items taken from the document.
- A backend group called "Editors", created automatically when the package is set up. I checked with a temporary test user (deleted afterwards). They can:
  - edit pages, content, news and categories on every page;
  - use the Page, List and Media modules;
  - upload images, which go to `fileadmin/user_upload/news/`.
  - They can't use the raw HTML content element.
- Pages created later belong to Editors automatically.
- A text editor limited to paragraphs, two heading levels, bold, italic, lists and links.
- You only need to create the editor accounts (System > Backend users) and put them in "Editors".

**Content I changed on my own** (the Communications Office should check this)
- I fixed the typos from the document: "the the", "there stall", "Pedestrains", "helth", "volunter".
- I read the boreholes date "2062" as 2026.
- I left out "Youth bursaries: applications open" because it breaks off mid-sentence.

**Something to know on this installation:** my first `extension:setup` run created a second copy of the whole page tree. This installation was filled by the seed script, so it had never recorded that the package's content was imported. I restored the database from the snapshot I took beforehand and recorded the import. Now the data is clean: 29 pages, one group. The README explains how to avoid this.

**Where things are:** the news templates are in `Resources/Private/Extensions/News/`, routing in `route-enhancers.yaml`, the group in `Classes/Initialization/CreateEditorGroupOnPackageInitialization.php`, and the seed script is `Build/Initialisation/seed-news.php`. The updated `data.xml` export includes all the news tables. Nothing is committed.

**Not done yet: the test on a fresh installation.** It's the only proof that the package works on an empty installation, and that test was blocked. The test needs the database dropped and `typo3 setup` run from scratch. I've already saved a database snapshot (`after-news`) and copies of `settings.php` and `config/sites` in `/tmp/claude-1000/-home-lina-wolf-projects-typo3-t3ca-2026-demo/f23fa177-4cff-4c08-bce2-fed5e033b09b/scratchpad/fresh-backup`, so everything can be restored afterwards. One part has never run at all: my last change, which takes over pages whose exported group doesn't exist on the new installation. Should I run the fresh-install check that way (you'd need to allow it), or do you prefer to test on a separate installation yourself?

`composer require` also reported 10 security advisories affecting 7 packages. `composer audit` failed without output, so I don't know whether news brought any of them in.
