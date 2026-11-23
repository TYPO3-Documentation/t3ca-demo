# Stage 06-home-page: prompts and answers

## 1. Prompt

> Show me the home page in my browser.

The home page is open in your browser at https://demo.ddev.site/. The site responded normally (HTTP 200), and the browser opened it in a window you already had open. The terminal also printed some Chrome "zygote" errors, but those are usually just log noise when Chrome hands a link to a running window.

## 2. Prompt

> The home page only shows the news. In the design it also has a slider, service tiles, events and a newsletter sign-up. What do I need for that?

Your sitepackage already has the CSS for all four sections. The SCSS was copied over from the design, so `.slider`, `.tiles`, `.events`, `.newsletter` and the form styles are all in `main.css`. What's missing is the content behind them, plus two scripts. The home page renders whatever sits in its single "main" column, and today that's only the news plugin.

| Section | What you need | Already there |
|---|---|---|
| **Slider** | A custom content element with child slides (image, heading, text, button link), its Fluid template, and `design/js/slider.js` copied into the sitepackage and included like `navigation.js` | CSS |
| **Service tiles** | A custom content element with items (icon, label, link to a page). Alternatively, a menu of selected pages with an icon field on each page | CSS |
| **Events** | Somewhere to store events: date (sometimes a range), time, place, cost, registration. Also a list for the home page that shows only upcoming events, plus a detail page. EXT:news has no date fields for events, so this needs a decision (see below) | CSS; the content is in `design/docs/news-and-events.docx` |
| **Newsletter sign-up** | A form definition in EXT:form (email, topics, consent) and something that actually handles the subscription. The design asks for double opt-in, which EXT:form doesn't do by itself. You'd also need `design/js/forms.js` for the validation in the browser | EXT:form is installed; CSS |

All of these are content elements stacked in the existing "main" column, so the Home backend layout and template don't need to change.

**Two decisions I need from you:**

1. **How to store events:**
   - Build them on top of news, with an "is event" flag and date fields. This reuses the news templates and categories.
   - Or give the sitepackage its own event record type and plugin. That's cleaner if events will grow (registration, places).
2. **Who handles newsletter subscriptions:**
   - EXT:form plus a small custom step that stores the subscriber and sends the confirmation email. That's enough for a demo.
   - A dedicated newsletter extension.
   - An external mailing service.

   This decides how much of the double opt-in we build ourselves.

**Problems in the events document:**
- One news date reads "5 September **2062**".
- One event date is written "22/11/2026" while the others are spelled out.
- There are several typos ("Contcat", "Pedestrains", "helth", "volunter", "the the old market … there stall").
- The last news entry ("Youth bursaries") is cut off mid-sentence.

These should be fixed when the content goes in, not copied as they are.

If you answer the two questions, I'll build it in this order: slider and tiles first (no open decisions), then events, then the newsletter.

## 3. Prompt

> hat sounds like a lot. What should I start with, and why?

Start with the **service tiles**:

- **It's the smallest piece.** It's a list of links, each with an icon and a label. There's no JavaScript, no new data model and nothing to decide first.
- **It sets up the pattern for the slider.** Both are custom content elements that hold a list of items. Once the tiles work, the slider is the same shape with an image, text and a button per item, plus `slider.js`. Building the tiles first means the slider is mostly a copy of a pattern that already works.
- **The links mostly have somewhere to go.** "Land rates" already has a design page (`land-rates.html`). The other tiles can point to placeholder pages until those pages exist.
- **You'll see it right away.** It's a full section on the home page that matches the design, and it's done in one step.

After that:

1. **Slider.** It's the same kind of element as the tiles plus one script. It's the most eye-catching part of the design.
2. **Events.** This needs your decision on how events are stored (on top of news, or as their own record type) and the cleaned-up content from the document.
3. **Newsletter.** It's the most work and the riskiest, because the double opt-in means storing subscribers, sending confirmation emails and handling privacy. It's also the one most likely to depend on choices outside the website, like a mailing service.

That order goes from no decisions to the most decisions, so nothing waits on an answer you haven't given yet. Should I start with the tiles?

## 4. Prompt

> yes, start with the tiles

The home page now shows the "Popular services" tiles above the news. I've opened the home page and the backend Page module in your browser. Please check the Page module: I haven't seen the editor side myself.

**What I checked:** the home page loads normally, and the tiles markup matches `design/index.html`: six tiles in the design's order, each linking to its page. The schema is correct in the database, and the PHP, Fluid and label files show no errors.

**What I haven't seen:** the backend form and the Page module preview. There's no browser tool here and I don't have the admin password. In the Page module you should see a "Popular services" element listing each tile with its icon, label and link. There are also no automated tests, because the project has no test setup yet.

**What editors get:** a new "Service tiles" element in a "Mto Safi" group of the new-content wizard (the slider and events can join that group later). It has a required heading and a list of tiles. Each tile has an emoji icon, a label and a link, and editors add, reorder, hide and translate tiles inside the element. Setting the header type to "Hidden" keeps the heading for screen readers only.

**Three tiles point to stand-in pages,** because the page tree has no page for them yet:
- "Water connection" goes to "Environment & water"
- "Market stalls" goes to "Market fees"
- "Report a problem" goes to "Contact"

Editors can change these in the backend once the real pages exist.

**Changes outside the element itself:**
- **New seed script** `Build/Initialisation/seed-home.php`. I ran it on your installation and re-exported `Initialisation/data.xml`, so a fresh install gets the tiles too. The export diff is clean: the new element and tiles, plus one page's "last changed" timestamp.
- **README:** I added the seed step and the new table to the export command (without that table, the tiles would be left out of the export silently), plus a short section about the element.
- **Editors group:** new installations get the element and the tile table automatically. Your existing "Editors" group was created before this, so I updated it directly in the database.
- **Database backup** from before the seed: `/tmp/claude-1000/-home-lina-wolf-projects-typo3-t3ca-2026-demo/b78ed53a-d58e-410d-bdae-14d0b5e1c087/scratchpad/before-tiles.sql.gz`

Nothing is committed yet. Should I commit this as "Stage 06" like the earlier stages, or will you look at the backend first?
