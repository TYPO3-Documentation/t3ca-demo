# Stage 14-kiswahili: prompts and answers

## 1. Prompt

> we still need the language menu

The language menu now works. The header's top bar links to the Kiswahili version of the current page, and on a Kiswahili page it links back to English.

**What I checked:** I loaded the English and Kiswahili versions of the home page, "Land rates", News, Events, Contact and Search. Every page answers 200, has `<html lang="sw-KE">` on the Kiswahili side, has one `<body>`, and lists both languages in its hreflang links. The menu link always points to the same page in the other language (`/revenue-payments/land-rates` ↔ `/sw/mapato-malipo/kodi-ya-ardhi`). I have not run a fresh install from the new `data.xml`.

**The menu itself didn't need changing.** The menu code was already in `setup.typoscript` and `Header.fluid.html`, but the site had only English, so it showed nothing. What I added:

- **Site language:** Kiswahili (language 1, `/sw/`, locale `sw_KE`) in `config/sites/main/config.yaml` and in `Initialisation/Site/main/config.yaml`. Where a Kiswahili page has no translated content, it shows the English content.
- **Register `sw` with TYPO3:** TYPO3 14 doesn't know Kiswahili as a label language, so without this all labels stay English. `ext_localconf.php` now registers `sw`.
- **Labels:** `sw.frontend.xlf` and `sw.news.xlf`.
- **Seed script:** `Build/Initialisation/seed-kiswahili.php` translates the 29 pages and shortcuts (Kiswahili titles and URLs) and the home page: headers, slides and service tiles. TYPO3 creates these translations hidden, so the script makes them visible.
- **Export:** `data.xml` is exported again, and the README has the new seed step and a "Languages" section.

**Still English on the Kiswahili pages:**
- The content of pages other than the home page, plus the news and events.
- The top-bar notice, the opening hours and the footer note. These are site settings, which hold one value for all languages.
- The search filter "Show only".
- **The Kiswahili search finds nothing.** The search extension (ke_search) only indexes a page translation that has content of its own, so the index has no Kiswahili pages. It will start working once those pages get Kiswahili content.

A native speaker should check the Kiswahili wording; I wrote it myself.

Your local database now has the translations. A snapshot from before them exists: `ddev snapshot restore before-kiswahili`. Nothing is committed. `seed-kiswahili.php` and the two `sw.*.xlf` files are new and untracked. Tell me if you want a "Stage 14-languages" commit.

## 2. Prompt

> searching for ardhi there is no result

Searching for "ardhi" now works. `/sw/tafuta?q=ardhi` returns "Kodi ya ardhi", and the result links to `/sw/mapato-malipo/kodi-ya-ardhi`.

**Why it found nothing:** the search extension (ke_search) only indexes a page translation that has content elements in its own language. The Kiswahili pages have none of their own; they show the English content instead. So even their Kiswahili titles never reached the index.

**The fix:** a new hook class, `Classes/Search/IndexFallbackContentOfPageTranslations.php`, is registered in `ext_localconf.php`. When a site language falls back to English and a page translation has no content of its own, the hook gives that translation the English content. ke_search then stores it under the Kiswahili title. `Services.yaml` marks the class public, which ke_search needs to create the hook with its dependency.

**What I checked:** after re-indexing, the index holds 16 Kiswahili pages next to the 16 English ones. In Kiswahili, "ardhi" gives 1 result and "land" gives 4, because the English content is indexed with them. The English search still gives the same 6 results for "land".

**One gap remains:** the news are indexed in English only, so they don't show up in the Kiswahili search. The README now says this.

Nothing is committed yet. The new files are the hook class, `seed-kiswahili.php` and the two `sw.*.xlf` files; the README section "Languages" covers the search behaviour. Tell me if you want me to prepare a "Stage 14-languages" commit.

## 3. Prompt

> How to translate the contact form?

A form isn't translated as a content element. The form framework translates the form definition through XLF files, and it picks the language from the site language of the page. So the form on `/sw/wasiliana-nasi` shows Kiswahili once there is a `sw.` file holding its keys. I checked the key format against `TranslationService.php` in TYPO3 14.

**1. Register a translation file** in `Configuration/Form/Sitepackage/config.yaml`, so it applies to every form of the site:

```yaml
prototypes:
  standard:
    formElementsDefinition:
      Form:
        renderingOptions:
          translation:
            translationFiles:
              20: 'EXT:mtosafi_sitepackage/Resources/Private/Language/form.xlf'
```

**2. Write `form.xlf` and `sw.form.xlf`**, with keys built from the form's identifier `contact` and each element's identifier:

| What | Key |
|---|---|
| Field label | `contact.element.name.properties.label` |
| Hint, placeholder | `contact.element.message.properties.elementDescription`, `contact.element.phone.properties.placeholder` |
| Select options | `contact.element.topic.properties.options.revenue`, `contact.element.topic.properties.prependOptionLabel` |
| Static text | `contact.element.required-hint.properties.text` |
| Submit button | `contact.element.contact.renderingOptions.submitButtonLabel` |
| Confirmation, mail subject | `contact.finisher.Confirmation.message`, `contact.finisher.EmailToReceiver.subject` |
| Error message | `contact.validation.error.phone.1221565130`, or `validation.error.1221560910` for one shared by all fields |

The English text in the form's YAML stays as the source. Any key missing from the `sw` file falls back to that English text.

**Two things the YAML can't keep as they are:**
- **Custom error messages:** the messages written directly into the YAML (the phone number and the two "I'm not a robot" messages) are returned as they are and never translated. They have to move into the XLF under the keys above.
- **Standard error messages:** the default messages ("This field is mandatory", "invalid email address") come from EXT:form's own labels. TYPO3 has no Kiswahili for them (it doesn't even know the language, which is why the sitepackage registers `sw`). So `sw.form.xlf` needs them too, as `validation.error.<code>` for the validators the form uses: NotEmpty, StringLength, EmailAddress, RegularExpression.

**Outside the form:**
- The header "Write to us" and the sidebar texts are ordinary content elements. They stay English until the Contact page gets Kiswahili content, which the seed script could do like it does for the home page.
- The mail to the municipality would show its field labels in Kiswahili when someone writes from `/sw/`, because the mail template translates them too. If the office should always get English mails, the template can be pinned to English.

Should I build it: the translation file for the whole form, the error messages moved out of the YAML, and the Contact page content added to the seed script?

## 4. Prompt

> go

The contact form is translated. On `/sw/wasiliana-nasi` the labels, the hints, the topic options, the consent text with its link to `/sw/sera-ya-faragha`, and the submit button "Tuma ujumbe" are all in Kiswahili. The English page is unchanged.

**What I checked:** I sent the form with empty fields, an invalid email, the phone number "abc" and a too-short message. All 7 error messages come back in Kiswahili on `/sw/` and in English on `/contact`. The Contact page's own content also shows in Kiswahili: the heading "Tuandikie", the address, the opening hours, "Report a problem", and the image's alt text and caption.

**Not tested: a message that actually gets sent.** That needs the "I'm not a robot" puzzle solved in a browser. So the Kiswahili confirmation message and the English mail to the office are written but unproven. To check, send the form once from `/sw/wasiliana-nasi` and look at the mail in `ddev mailpit`.

**What changed:**
- **Translation file:** `Configuration/Form/Sitepackage/config.yaml` points the form framework to the new `form.xlf`. `form.xlf` holds the English texts and `sw.form.xlf` the Kiswahili ones.
- **Error messages:** the phone and "I'm not a robot" messages moved from `contact.form.yaml` into the XLF, because text written in the YAML is shown untranslated. `sw.form.xlf` also carries Kiswahili for the standard validator messages, since TYPO3 has none.
- **Office mail:** it stays English, whatever language the visitor uses (`translation.language: default` on EmailToReceiver).
- **Altcha:** the widget switches to Kiswahili by itself, because it reads the language from `<html lang>` and ships Kiswahili. No change needed.
- **Contact page content:** `seed-kiswahili.php` now translates it too. I restored the snapshot, ran the script again, rebuilt the search index and exported `data.xml` again.
- **README:** a new section, "The form in Kiswahili", explains the key format. Any new field needs its key added in both XLF files.

Nothing is committed. Should I prepare the "Stage 14-languages" commit and show you the message?
