# Stage 15-land-rates: prompts and answers

## 1. Prompt

> Now the land rates calculator from the design.

The land rates calculator from the design is now a content element in the sitepackage. It's on the "Land rates" page in English and Kiswahili. Nothing is committed yet.

**How editors set the rates.** Each land use is a child record of the element, with a name and a percentage of the rateable value. The current ones are Residential 0.5 %, Commercial 1.2 % and Agricultural 0.2 %. Editors can add, reorder, hide and translate them. The minimum rate (KES 1,500) and the monthly interest (1 %) are fields on the element. A translation only translates the heading and the land use names, and always calculates with the English rates. The design held the rates in one block of JSON; I switched to child records so editors can add a land use without touching the script.

**What I checked**
- **Database:** the new table and fields were created as expected.
- **Server-side rendering:** both languages render with translated labels, and the script only loads on pages that carry a calculator.
- **In a real browser:** the three examples in `design/docs/land-rates-calculation.md` come out right in both languages (12,500 / 30,900 / 1,500, the last one being the minimum rate). Both warnings appear when they should, months over 36 count as 36, and there are no script errors. The layout matches the design on desktop and at phone width. Amounts show as "KES" in English and as "Ksh" on the Kiswahili page.
- **Content export:** the page and its records are in `Initialisation/data.xml`, and the diff only adds them.

**What I couldn't check**
- **Editor side:** I wasn't allowed to create a temporary backend admin, so I haven't seen the content wizard entry, the form or the page module preview. The icon and the element are registered, but please open one in the backend.
- **Fresh install:** I didn't test importing `data.xml` into a new installation.
- **Tests:** the package has none, so none were added.

**Things to know**
- An existing "Editors" group won't get the new table. Only groups created by a new setup do, so on an installation that already has one, add the table to the group by hand.
- The due date, 31 March, is written into the label texts, not stored as a field.
- Without JavaScript the results stay empty, the same as in the design.
- I round the annual rate and the interest to whole shillings separately, so the lines add up to the total shown. The design only rounded for display, so its lines could differ from the total by a shilling.
- Please have a native speaker check the Kiswahili.
- Before seeding I took a database snapshot: `ddev snapshot restore before-land-rates-calculator` puts it back.

**Files**
- **New:** the land use table, `tt_content_landrates.php`, the TypoScript, the template, the preview, `land-rates-calculator.js`, the icon and `Configuration/Icons.php`, all in the sitepackage. Also `Build/Initialisation/seed-land-rates-calculator.php`.
- **Changed:** the label files (English and Kiswahili), `page.tsconfig`, the editor group setup, `data.xml`, and the sitepackage README.

If you want a commit, your history uses stage names, so it would be `Stage 15-land-rates-calculator`. The TYPO3 commit guide drafted this message instead:
```
[FEATURE] Add the land rates calculator content element

Editors maintain the rates in the element, as land use records with a
share of the rateable value plus a minimum and a monthly interest, so a
new rate needs no change to the script. Translations translate only the
names and calculate with the rates of the default language.
```

## 2. Prompt

> The rates change every year. Who changes them, and how?

An editor changes them in the backend by editing the calculator on the "Land rates" page. No developer is needed for the rates themselves, but a few other places on the site also state them and have to be updated alongside.

**Who**

Anyone in the "Editors" group, or an administrator. One catch: only a group created by a new setup can edit land uses. On an installation whose "Editors" group already existed, an administrator must first add "Land use" to the group's tables (System > Backend users > the group > Access lists). Otherwise editors can open the calculator but can't change the land uses.

**How**

1. Open the Page module and the "Land rates" page.
2. Edit the "Land rates calculator" element:
   - Under "Land uses", change each "Rate (% of the rateable value per year)". This is also where a land use is added, removed or reordered.
   - Change "Minimum rate (KES per plot and year)" and "Interest when paid late (% per month)" if they change too.
3. Save. Saving the element clears that page's cache, so the new rates show on the next visit.

The editor only changes the English element. The Kiswahili page calculates with the English rates, so there is nothing to change there, unless a land use is added. That one needs a Kiswahili name on the translation, otherwise it appears in English on the Kiswahili page. I haven't actually tested a rate change followed by a look at the Kiswahili page; I can run that test.

**What else states the rates**

The calculator doesn't update these, so the editor has to change them in the same session:
- **The rates table** on the same page, "Rates for 2026": its heading and every row.
- **The text above it,** "How your land rates are calculated", which names the interest of 1 %.
- **The due date, 31 March,** in the calculator's hints and in the slider text on the home page. The hints are in the label files, not in a field, so a different due date needs a developer.

**Timing**

A change is live as soon as it's saved; you can't schedule it for 1 January. An editor can work around that: copy the element, enter the new rates, and set the copy's "Publish date" to 1 January and the old one's "Expiration date" to the same day. The rates table needs the same treatment.

The table and the calculator can disagree, and they're the weak spot of this setup. I could make the calculator element also render the rates table from the same records, so the editor changes each rate once.

## 3. Prompt

> Show me the calculator in my browser.

I tried to open the "Land rates" page in your default browser with `ddev launch`, but I can't tell whether a tab opened. Chrome printed two "Broken pipe" errors while starting, which can be harmless but can also mean it failed to start.

If no tab opened, here are the links:

- English: https://demo.ddev.site/revenue-payments/land-rates
- Kiswahili: https://demo.ddev.site/sw/mapato-malipo/kodi-ya-ardhi

The calculator is below the "Rates for 2026" table. It opens with Residential and KES 2,500,000, which gives KES 12,500. To check the second example from the rates document, choose Commercial and enter 3 months late: the total should be KES 30,900, with the interest warning below it.

If you open the backend while you're there, please also look at the editor side, which I couldn't see. Check the "Land rates calculator" entry in the content wizard under "Mto Safi" and its icon. In the Page module, its preview should list the land uses with their rates, the minimum and the interest.
