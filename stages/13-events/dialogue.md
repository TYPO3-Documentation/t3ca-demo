# Stage 13-events: prompts and answers

## 1. Prompt

> where are the events displayed at?

Right now the events don't appear anywhere on the TYPO3 site. They only exist in the design and the source document.

- **In the design:** `design/index.html:127-134` has an "Events" section on the home page, right below the news cards. It's a `<ul class="events">` with three hard-coded entries (15 Nov beach clean-up, 22 Nov budget forum, 5 Dec job fair). Each entry has a date badge.
- **The styling is already in the sitepackage:** `packages/mtosafi_sitepackage/Resources/Private/Scss/_components.scss:200` defines `.events` and `.events__date`, and that compiles into `Resources/Public/Css/main.css`.
- **The source data:** the "Events" part of `design/docs/news-and-events.docx`, after the news entries.

What's missing:
- No Fluid template, TypoScript or site set in the sitepackage uses `events`.
- `Initialisation/data.xml` contains no event records.
- The README's initial-content section only says the news was imported (list page, detail page, news folder, home page plugins). It doesn't mention events.
- There's no events extension in `composer.json`, only `georgringer/news`.

So the news from the docx was carried over, but the events weren't. If you want them on the home page as in the design, one way is to store them as news records in their own category and add a second news plugin under the news on the home page. Should I prepare that?

## 2. Prompt

> what technique should we use to display them?

**I recommend `georgringer/eventnews`.** It extends the news extension with events, which suits this project:

- **It runs on your versions.** Version 7.2.0 (released 2026-10-09 on Packagist) requires `typo3/cms-core ^13.4.29 || ^14.3` and `georgringer/news ^13 || ^14`. The project uses `cms-core ^14.3` and `news ^14.1`, so it fits. It's Composer-only: the TER listing stops at 6.x for TYPO3 12, which doesn't matter for a Composer project.
- **The events fit its fields.** A news record gets an "is event" switch, an end date, a full-day flag, a location and an organizer. Two of the docx events span several days ("Festive market at the jetty", 19–24 Dec, and "Land rates help desk", Jan–Mar), so they need a real end date. Plain news records can't store one. The docx also lists Cost and Registration, which have no field. Those can go in the teaser or body text.
- **It reuses what's already set up.** Editors keep working with news records in the "News records" folder. The news detail page, the categories and the search indexer for news cover the events as well. You don't need a second data model or plugin set.
- **Past events drop off by themselves.** The events list can show upcoming events only, sorted by start date, so the "Past events" part of the docx needs no manual handling. The home page news plugin is then set to leave events out, so the two lists stay separate.

On the home page, a second news plugin under the news, set to upcoming events with a limit of 3, renders through a small Fluid template override. That template outputs the design's `<ul class="events">` with the `events__date` badge, and the SCSS for it is already in the sitepackage.

Two alternatives I'd rule out:
- **Plain news with an "Events" category:** no end date, no location, and showing future dates in date order would take workarounds.
- **`lochmueller/calendarize` (15.1.0, TYPO3 14 compatible):** a separate event model with recurrence and calendar views. That's more than six one-off events need, and editors would have to learn a second system.

One thing I still need to check before building: the exact plugin setting names in eventnews 7.2 for "upcoming only" and "exclude events". I'll read them from the package once it's installed.

Should I go ahead? That means adding the package, extending `seed-news.php` with the events from the docx, re-exporting `data.xml`, and adding the home page plugin and template.

## 3. Prompt

> is it still actively maintained?

Yes, it's actively maintained, though by one person, Georg Ringer, the author of the news extension.

- **Latest release:** 7.2.0 on 2026-10-09, six weeks ago. That's the release that added TYPO3 14.3 and news 14 support.
- **Recent work:** at the end of August 2026 there were about ten commits of real fixes and features. Two of the fixes matter for your multi-day events: an event now shows in every month it runs through, and searching a date range finds events that span it. Location records also gained address fields. The last commit was on 2026-10-09.
- **Issues:** that batch closed long-standing issues too, some of them years old (#81, #153, #184, #191, #198). 29 issues are still open.
- **Release pattern:** about one major or minor release a year, following the TYPO3 versions: 6.0 in 2023 for TYPO3 12, 7.0 in 2024 for 13, 7.1 in 2025, and 7.2 in 2026 for 14. The repository isn't archived.

**What to keep in mind:** work comes in bursts, and everything depends on one maintainer. That's the same risk you already take with `georgringer/news`, and eventnews tends to get its update soon after news supports a new TYPO3 version. If it were abandoned, the events would still be ordinary news records with a few extra columns, so moving them to plain news or another extension would be manageable.

My recommendation stands. Should I go ahead with it?

## 4. Prompt

> What happens if we have events spanning multiple days?

I read the query code of eventnews 7.2.0 to check this. Multi-day events are handled properly. You'd only need to adjust the template.

**How it's stored**
- The start goes in the normal news date field (`datetime`).
- eventnews adds `event_end`, a date-and-time field, and a "full day" checkbox. These fields only appear when "is event" is ticked.
- The festive market becomes one record: start 19 Dec 2026 16:00, end 24 Dec 2026 22:00.

**Which list it shows up in**
- **Upcoming events list:** for an events-only plugin, eventnews replaces the news time restriction with "end date is still to come, or start date is still to come" (`Classes/EventListener/ModifyDemandRepositoryEventListener.php`, around line 133). An event that has already started stays in the list until it ends. The "Land rates help desk" (18 Jan to 31 Mar 2027) stays visible through March. Plain news would drop it on 18 January.
- **Month and year views:** an event appears in every month it overlaps. That's the #198 fix from August 2026: before, a January to March event showed up in January only.
- **Events without an end date** only use the start date. Once that time has passed, they drop off.

**What you'd have to build yourself**
- **Display:** the news list and detail templates don't show `eventEnd` at all. Only eventnews' own month template does. Our home page template override has to output the range, for example "19–24 Dec". The design's date badge (`.events__date`, a fixed 4rem width) only has room for a single day. It needs a range variant, for example "19 Dec – 24 Dec" on two lines, or a wider badge. That's a decision for the design.
- **Repeating opening hours:** there's no field for something like the help desk's "Monday to Friday, 9:00–15:00". eventnews has one start and one end and no recurrence. That text goes in the teaser or body text. If you ever need real recurring events with separate dates, that's when calendarize would be worth it. For the events in the docx, start and end are enough.
- **Sorting:** the list is sorted by start date in ascending order. An event that's already running therefore appears above events that start later. For a "what's on" list that's usually what you want.

Should I go ahead on that basis? Before I build the template, I'd need one choice from you: how the date badge should show a range.

## 5. Prompt

> go ahead, show the range on two lines in the badge

The events are now on the home page below the news. Multi-day events show their first and last day on two lines in the date badge. I checked everything in this installation, but the proof on a fresh installation did not run, so the new `data.xml` hasn't been imported anywhere yet.

**What changed**
- **eventnews 7.2.0** is required in the root `composer.json` and in the sitepackage's. Installing it also added an `eventnews` entry to `config/system/settings.php`. The extension has no site set, so `setup.typoscript` imports its TypoScript. That makes every existing news plugin list news only by default.
- **`Build/Initialisation/seed-events.php`** (new) writes the six upcoming events from the docx into the "News records" folder. Each has its start, end, place and category. Time, cost and registration go in the body text, because eventnews has no fields for them.
- It also adds an "Events" plugin under the news on the home page: events only, from today, soonest first, limit 3. An event that has started stays listed until its last day.
- I left out the docx's "Past events". They would never be shown, and the health camp is already a news item.
- **Templates:** a new template layout "events" in `List.html` and a new partial `Partials/List/Event.html`. A one-day event keeps the design's "22 / Nov" badge; a range shows "19 Dec –" on the first line and "24 Dec" on the second. Each title links to the detail page. There, `Detail.html` shows the date range and the place, and no "All news" link.
- **CSS:** the events list is now a grid, so all badges in the list are as wide as the widest one and line up. `main.css` is rebuilt; only the events rules changed.
- **`data.xml`:** re-exported and placed in `Initialisation/`. It has 6 more news records and 1 more content element. The image files are the same 19 as before.
- **Docs:** a new "Events" section in the sitepackage README, plus the seed-script list and the export steps. The root README now mentions the events.

**What I checked on this installation**
- The home page shows 22 Nov, 5 Dec and 12 Dec. The 15 Nov beach clean-up is already past and correctly drops out.
- The news on the home page and the news list page show no events.
- With the limit raised to 6, both range badges render. Screenshots at 1280 and 375 px look right.
- With "today" temporarily set to 1 Feb 2027, the list showed only the land rates help desk (18 Jan – 31 Mar), so a running event stays listed.
- The festive market's detail page shows "19 December 2026 – 24 December 2026 · Jetty and fish market".
- I set both plugin settings back afterwards. Snapshots `before-events` and `after-events` exist.

**Limitations**
- The site shows dates but no times. PHP here runs on Europe/Berlin time, not Nairobi, so a time read from the database would be off by two hours. The times are in the teaser and body text instead.
- The home page is cached. A finished event leaves the list when that cache is rebuilt, at the latest after the cache lifetime.
- Once the six seeded events have passed, the home page shows "There are no upcoming events at the moment."

**Fresh-install proof (not run)**
To check the new `data.xml` actually imports, I wanted to run the root README's fresh install. That means emptying the `db` database and running `typo3 setup`, then restoring with `ddev snapshot restore after-events`. Auto mode blocked dropping the database, so I put `fileadmin` back and left everything as it was. You can either allow that step and I'll run it and restore afterwards, or run the README's fresh install yourself.

`composer require` also reported 10 security advisories against TYPO3 core 14.3.10 (backend, core, form, impexp, install and others). They were there before this change; a core update is a separate task.

Nothing is committed. Should I show you the full diff and a commit message?

## 6. Prompt

> how does registration work?

Right now registration is only text. It isn't a feature of the site.

**How it works now**
- eventnews has no registration or booking of any kind. An event is a news record with a start, an end and a place.
- The seed puts the docx's "Registration" line into each event's body text, next to time and cost. It shows on the detail page as "**Registration:** …". Editors change it like any other text.
- That fits the docx as it stands. Four of the six events say "Not needed". The other two are handled at an office, not on the website:
  - Youth job and skills fair: "Recommended, at the Education Office"
  - Festive market: "Traders apply for a stall at the Trade & Markets Office until 1 December"
- The contact form doesn't count either. It's a general form on the "Contact" page and isn't linked to any event.

**If you want people to register online**, there are two ways:

1. **A form for each event that needs one.** The site already has the core form framework, which runs the contact form. You'd add a small registration form (name, phone, email) that emails the office, and place it on that event's detail page or link it from the event. This is little work and needs no new data model. But registrations only arrive as emails: there's no participant list, no seat limit and no deadline.
2. **An event extension with registration**, for example `derhansen/sf_event_mgt` (version 9.0.6, released 2026-10-09, TYPO3 `^14.3`). It handles registrations as records, with seat limits, deadlines, waitlists, confirmation emails and a participant list in the backend. But it brings its own event model, so it would replace eventnews rather than sit next to it. The events would move out of the news records, and the templates and seed would be redone.

For what the docx asks for today, the current text is enough. If the job fair or the market should take registrations online, I'd start with option 1. Option 2 only pays off once the municipality needs seat limits or participant lists.

## 7. Prompt

> can we link it to an email address or page?

Yes. The registration line is in the body text, which editors write in the text editor. Its link button can already link to a page, a URL or an email address. Editors can do this today with no code change, and it shows up on the event's detail page.

To build it into the seed, here's what the site has to link to:

- **Pages:**
  - Festive market (traders apply for a stall): "Trade & tourism" (`/trade-tourism`) or "Market fees" (`/revenue-payments/market-fees`).
  - Job fair (registration at the Education Office): there's no education page. "Contact" (`/contact`) is the nearest fit.
- **Email addresses:** the site only uses `info@mtosafi.example` (Contact page) and `report@mtosafi.example` ("Report a problem"). The docx gives phone numbers for the offices but no email addresses. I wouldn't invent addresses for the Education Office or the Trade & Markets Office, because the municipality has to supply those.

Two ways to do it:

1. **A link in the text (recommended):** for example "Registration: Recommended, at the [Education Office](mailto:…)". The seed script writes it the way the text editor would. Each event can use whatever fits, and no template change is needed.
2. **A separate registration link field** shown as a button ("Register") on the detail page and maybe in the list. That looks tidier, but eventnews has no such field. The sitepackage would have to add a column to the news table and render it. That's only worth it if most events will have registration.

Tell me where the two registration lines should point, for example "market → Market fees page, job fair → info@mtosafi.example", and I'll add the links to the seed and re-export `data.xml`.

## 8. Prompt

> option 2 sounds good

Events now have a registration button. Editors fill in two new fields on the "Eventnews" tab of an event: a link (a page, a website or an email address) and an optional button text that defaults to "Register". The button appears on the event's detail page below the text, and the home page list is unchanged.

**What I added**
- **The two fields:** `Configuration/TCA/Overrides/tx_news_domain_model_news.php` adds them next to the event's place, and they only appear when "Is event" is ticked. The labels are in a new `Resources/Private/Language/news.xlf`.
- **Access in templates:** eventnews has no such fields, so the sitepackage adds them to the news model the same way eventnews adds its own. That's `Classes/Domain/Model/News.php` plus one line in `ext_localconf.php`. Templates can then use `registrationLink` and `registrationLabel`. I checked that the generated class contains both getters.
- **The button:** `Detail.html` shows a `button`-styled link when an event has a registration link. The "Register" text is in `frontend.xlf`.
- **Seed and export:** `seed-events.php` now sets these targets, as you chose:
  - **Festive market:** links to the "Market fees" page, button text "Apply for a stall".
  - **Job fair:** links to the "Contact" page with the default "Register".
- I restored the `before-events` snapshot, ran the seed again and re-exported `data.xml`. The two page links are exported as soft references, so the import will point them at the new page uids. The state after this run is saved as snapshot `after-events-registration`.

**What I checked:** the festive market page shows "Apply for a stall" linking to `/revenue-payments/market-fees`, and the job fair page shows "Register" linking to `/contact`. Events without a link get no button, and the home page list still shows the next three events. The screenshot of the detail page looks right.

**Open points**
- **Editors on this installation can't see the event fields yet.** The "Editors" group here was created before eventnews was installed, so it allows none of the new fields: neither "Is event" nor the registration fields. On a fresh install the group is created after the fields exist and gets them automatically. Here, someone has to tick them in the group (System > Backend users), or I can add them for you.
- **The fresh-install proof still hasn't run.** The new `data.xml` hasn't been imported anywhere yet. It needs the database emptied, which auto mode blocked earlier, so it's your call. The full fresh-install steps are in the root README.
- **I haven't opened the event form in the backend.** I checked the database columns, the export and the frontend, but not how the fields look in the event form.

Nothing is committed. Should I show you the diff and a commit message?

## 9. Prompt

> the events are on the news page and when you use de breadcrumb you only see news not events

Events now have their own pages, so the breadcrumb on an event leads back to the events list instead of the news.

**What changed**
- **New pages:** an "Events" page after "News", hidden from the main navigation like News, with an "Event" detail page below it. Events are at `/events/event/<event>`, and the breadcrumb is Home › Events › Event.
- **Events page:** lists every upcoming event with the same date badges as the home page. It uses a new template layout, "Events page: all upcoming events".
- **Home page:** the Events section now has an "All events" link next to its heading, like "All news" on the news section. The event links go to the Event page.
- **Event detail page:** shows "All events" at the bottom, pointing to the Events page. News articles still show "All news".
- **Search:** ke_search indexed events with the news and linked them to the news Article page, so the breadcrumb problem came back through search. A new listener, `Classes/Search/LinkEventsToEventPage.php`, now sends events to the Event page.
  - It reads the Event page from a new site setting, `mtosafi.events.detailPage` (Site Management > Settings > Mto Safi > Events).
  - The existing remap class adjusts that setting after the import, like the other page settings.
  - The Event page itself is excluded from the page search, like Article.
- **Seed and export:** I restored the `before-events` snapshot, ran `seed-events.php` again and re-exported `data.xml`. It now has 33 pages and 45 content elements; the image files are unchanged. The current state is snapshot `after-events-pages-2`.
- The README describes the pages, the search behaviour and the new setting.

**Verified on this installation:** the home page, `/events`, two event pages, `/news`, a news article and a search all return 200. The breadcrumb on an event shows Home › Events › Event, with Events linking to `/events`. The search for "market" links the festive market to `/events/event/…` and the news to `/news/article/…`. After re-indexing, all six events in the index point at the Event page.

**Still open**
- **Last breadcrumb item:** it shows the page title "Event", not the event's title. News has the same behaviour ("Article"). I can change both so the breadcrumb ends with the title of the event or article.
- **Editor permissions on this installation:** the "Editors" group here still can't see the event and registration fields. It needs those fields allowed, either in the backend or by me.
- **Fresh install not proven:** the new `data.xml` hasn't been imported on an empty database yet. Emptying the database is blocked unless you allow it.
- `config/sites/main/settings.yaml` now has `events: detailPage: 36`, which is this installation's uid. That matches the uids already in that file.

Nothing is committed.

## 10. Prompt

> yes change the last breadcrum item

The breadcrumb on a detail page now ends with the title of the event or news item instead of "Event" or "Article":

- Event: Home › Events › Festive market at the jetty
- News: Home › News › New fish market opens at the jetty

**How it works:** the news extension already has a data processor that adds the news record to a menu, but it adds it *after* the last item. That would give Home › Events › Event › Festive market, with "Event" linking to an empty page. A small subclass, `Classes/DataProcessing/NewsTitleInBreadcrumbProcessor.php`, replaces the last item instead. It's wired into the breadcrumb in `setup.typoscript` (entry `41`). Pages without an event or article in the address keep their normal breadcrumb.

**Checked on this installation:** I loaded both detail pages above and got the expected breadcrumbs. `/events`, `/revenue-payments/land-rates` and `/news/article` without an article are unchanged. The README's News section mentions the new behaviour. This is configuration and code only, so `data.xml` doesn't need a new export.

Still open from earlier: the "Editors" group on this installation can't edit the event fields yet, and the fresh-install proof hasn't run. Nothing is committed.
