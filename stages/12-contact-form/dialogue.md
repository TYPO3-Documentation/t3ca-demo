# Stage 12-contact-form: prompts and answers

## 1. Prompt

> we need a contact form, what ways can it be created and what can you recommend?

The best fit is the core form extension, EXT:form. It's already installed (`typo3/cms-form ^14.3` is in `composer.json`) but not switched on for your site yet. Your mail setup sends to Mailpit in DDEV, so you can test the emails locally straight away.

#### The options

| Option | What it is | Good | Bad |
|---|---|---|---|
| **1. EXT:form (core)** | Editors build forms in the "Forms" backend module or you write them in YAML. They go on a page with the "Form" content element. | Comes with TYPO3 and is maintained with it. It handles server-side validation, CSRF protection and a honeypot spam trap. After submission it can email the site owner, email the sender, save to the database, redirect or show a thank-you message. You can ship the form inside your sitepackage. | Changing the markup means overriding Fluid templates and partials. Submissions are not stored or listed in the backend unless you add something. |
| **2. powermail (third-party)** | A popular form extension that stores submissions as records | Backend list and export of submissions, extra spam checks, more features for editors | Another dependency to keep up to date, and you need to check it supports TYPO3 14.3 before choosing it. Too much for a simple contact form. |
| **3. Your own plugin** | Your own controller, form object, validation and mail code | Full control | You have to write validation, spam protection, mail and error handling yourself. Only worth it for unusual logic, such as writing into your own tables or calling an API. |
| **4. External service** | An embedded form from a hosted provider | No code to write | Visitor data goes to a third party (a GDPR problem), it looks different from your site, and it doesn't fit a self-contained demo. |

#### My recommendation: EXT:form, with the form shipped in the sitepackage

1. **Turn it on:** add `typo3/form` to the dependencies in `Configuration/Sets/Sitepackage/config.yaml`, next to `fluid-styled-content`, `news` and `ke_search`.
2. **Define the form in the sitepackage:** put it in a file like `Resources/Private/Forms/contact.form.yaml` with name, email, subject, message and a privacy checkbox. Register that folder as a form storage location in a small form setup YAML. The form is then under version control and works on a fresh install, like your earlier stages.
   - One catch: by default, forms stored in an extension are read-only in the backend form editor. That's fine for a demo distribution. If editors should be able to change the form, you can allow saving to the extension path or keep the form in fileadmin.
3. **What happens after sending:** one email to the site owner, an optional copy to the sender, then a thank-you message or a redirect to a thank-you page. Put the recipient address in a site setting so it isn't hard-coded.
4. **Styling:** if the default markup doesn't match your theme, override the form templates and partials from the sitepackage.
5. **Content:** create a "Contact" page with the Form content element and add it to the `Initialisation` data, so the fresh-install stage includes it.

I'd suggest powermail only if editors need to browse or export the submissions in the backend.

If EXT:form works for you, I can build it as the next stage: set dependency, form definition, emails, contact page and a test send through Mailpit. I won't commit anything until you say go.

## 2. Prompt

> create a contact form with form then

The contact form is on the Contact page, built with EXT:form, and it works in your DDEV installation. Nothing is committed. One check is still open: importing the new `data.xml` into a fresh installation.

**What I tested on `https://demo.ddev.site/contact`:**
- **Wrong input:** each bad field shows its own message under it and the field is marked red with the design's styles.
- **Spam:** a submission that fills the hidden honeypot field is rejected and no mail is sent.
- **Valid message:** the visitor sees the green "Asante! Your message has been sent." box. In Mailpit, one mail went to `info@mtosafi.example` with the visitor as reply-to, and a copy went to the visitor. Both use a Mto Safi mail layout instead of the TYPO3-branded system mail.

**How it's built:**
- **Form definition:** `Resources/Private/Forms/contact.form.yaml`, with the fields from `design/contact.html`: name, email, phone, topic, message (at least 20 characters) and a consent checkbox. It's a file under version control, so the backend Forms module shows it but can't edit it.
- **Styling:** `Configuration/Form/Sitepackage/config.yaml` replaces EXT:form's Bootstrap classes with the design's classes. The only template override is `Form.fluid.html`, which adds `class="form"` to the form tag because a form definition can't set it.
- **Mails:** a site layout and templates under `Resources/Private/Extensions/Form/`. The consent checkbox is left out of the mails.
- **Wiring:** `typo3/form` is now a dependency of the site set, and `typo3/cms-form` is in the sitepackage's `composer.json`. The `composer.lock` update also adds `tpwd/ke_search`, which the sitepackage already required but the lock had missed.
- **Contact page:** a new script, `Build/Initialisation/seed-contact-form.php`, runs after `seed-pages.php`. It switches the page to the "Sidebar right" layout, puts the form in the main area and moves the address, opening hours and "Report a problem" into the sidebar. I re-exported `data.xml`, and the diff contains only those changes. The README explains all of this.

**Still open:**
- **Fresh-install check:** this needs an empty database, and the safety check blocked me from dropping it. Your installation is unchanged by that attempt. To run it yourself, either follow "Starting over" and "Fresh install" in the root `README.md`, or tell me to go ahead and I'll do it between two database snapshots and restore yours afterwards. A snapshot from before my changes exists as `before-contact-form`.
- **Visual check:** I only checked the HTML, not the page in a browser. Please look at the two-column layout.
- **Mail sender:** the mails come from `noreply@mtosafi.example`. On a live server, that domain has to allow the server to send mail for it.
- **Heading style:** the "Write to us" subheader shows as an `<h3>`, while the design has a lead paragraph there.

If you want to commit, say so and I'll show you the commit message first.

## 3. Prompt

> where3 can I see the mail?

The mails are in Mailpit, which DDEV runs alongside the site: **https://demo.ddev.site:8026**

You can also open it from the terminal with `ddev mailpit`.

DDEV catches every mail the site sends, so nothing goes to a real address. You'll find my test messages there:
- **"Contact form: Health"**: the message to the municipality.
- **"Your message to Mto Safi Municipality"**: the copy for the visitor.

Mailpit has an HTML and a plain-text tab for each mail, so you can check both versions.

One correction to my earlier note: the sitepackage README says `ddev launch -m`, but this DDEV version names the command `ddev mailpit`. I can fix that line if you like.

## 4. Prompt

> what about data privacy and security?

The form is technically sound, but my design has one real security flaw, and a few privacy decisions belong to the site owner. My recommendation is at the end.

#### Security

**The flaw: the copy to the visitor can be abused to send spam.** The copy goes to whatever address someone types into the form. It includes the name and message they entered, and it comes from `noreply@mtosafi.example`. A spammer can enter a stranger's address and their own text, and the municipality sends it for them, from its own domain. That hurts the municipality's mail reputation, so its real mails start landing in spam folders. The honeypot only stops simple bots, and nothing limits how often the form can be sent.

**What's already fine:**
- **Tampered submissions:** EXT:form signs the form's hidden state and the list of allowed fields, so a modified submission is rejected.
- **Injected markup:** the mail templates escape everything the visitor enters. All addresses pass an email check before they reach the mailer.
- **Length limits:** name is capped at 200 characters and the message at 5,000. There's no file upload, so nothing lands on the server.
- **Spam bots:** the honeypot catches simple ones.
- **Cookies:** loading the page sets none, so the form needs no cookie banner. In TYPO3 14.3 the honeypot no longer uses a session.

#### Data privacy

What's good: the form collects only what's needed, and the phone number is optional. Nothing is stored in TYPO3's database. The hint text asks people not to send ID or bank numbers, and the form logs no personal data.

What the municipality has to decide or write; I haven't decided any of it for them:
1. **The copy to the visitor** also sends personal data to whatever address was typed. One typo and a stranger gets the message.
2. **Legal basis:** answering a citizen's message is probably part of the municipality's public task, not something that needs consent. If so, the "I agree…" checkbox may be the wrong tool and could become a short notice instead. Which law applies depends on the deployment, for example Kenya's Data Protection Act 2019 or the GDPR, and the municipality's data protection officer should confirm it.
3. **The privacy policy** is still placeholder text, and the checkbox links to it. Before going live it has to say what the form collects, why, who reads it and how long it's kept.
4. **Retention:** the only copy of each message lives in the `info@` mailbox, so how long it's kept is set by the municipality's mail policy.
5. **Mail transport on production:** use an SMTP server with TLS, and sender records (SPF/DKIM) for `mtosafi.example`. DDEV only uses Mailpit.

#### My recommendation

Remove the copy to the visitor. The thank-you message on the page already confirms the message was sent, and removing the copy fixes both the spam problem and the typo risk. If they want to keep a copy, it should hold only fixed text and none of what the visitor entered. That limits the abuse but doesn't stop it.

Should I remove it? It's a small change: the form YAML, the README, and the success text, which currently says "We have sent a copy to your email address". I'd show you the diff first.

## 5. Prompt

> yes, remove the copy to the sender

I've removed the copy to the visitor. A test submission now sends exactly one mail, "Contact form: Trade & markets", to `info@mtosafi.example`, and the page still shows the confirmation.

What changed:

**`Resources/Private/Forms/contact.form.yaml`**
- The `EmailToSender` step is gone. A comment in its place says why, so nobody adds it back unknowingly.
- The confirmation text no longer promises a copy:
  ```diff
  - <p>We have sent a copy to your email address and will answer within five working days.</p>
  + <p>The right department answers to your email address within five working days.</p>
  ```

**`packages/mtosafi_sitepackage/README.md`**
- The contact form section now describes a single mail and explains why visitors get no copy.
- I also fixed the Mailpit command there: it now says `ddev mailpit` instead of `ddev launch -m`.

I left the mail layout settings for `EmailToSender` in `Configuration/Form/Sitepackage/config.yaml`. They do nothing now, but if an editor ever adds a copy to another form, it gets the site's mail layout instead of the TYPO3 one. `data.xml` didn't change, because the copy was defined only in the form file.

The fresh-install check of `data.xml` is still open, and nothing is committed yet.

## 6. Prompt

> what about spam protection?

Right now the form has only the core honeypot. Removing the copy to the visitor already took away the worst risk, because spam can now only reach the municipality's own `info@` inbox, not strangers. What's left is inbox noise, which is worth reducing.

#### What it has today

- **Honeypot:** a hidden field that people don't see but simple bots fill in. I tested it and the bot submission was rejected with no mail sent. It catches simple bots, but not targeted spam or bots that drive a real browser.
- **Built-in limits:** required fields, an email check, and 20 to 5,000 characters for the message. That stops junk submissions, not spam.
- **Missing:** nothing limits how often one sender can submit.

#### Options

| Option | What a visitor notices | Privacy | Effort |
|---|---|---|---|
| **ALTCHA** (`bbysaeth/typo3-altcha`, says it supports TYPO3 13 and 14, v2.0.2 from August 2026) | Little or nothing: the browser solves a small computing puzzle (proof of work) | Runs on your own server, no outside service, no tracking | Install it and add one element to the form |
| **Link filter** in the form file | Messages containing `http` or `www` are rejected | None | A few lines of YAML; also blocks real people who paste a link |
| **Rate limit** per IP address | Too many submissions in a short time are refused | Handles IP addresses, which needs a mention in the privacy policy | Custom code |
| **Hosted CAPTCHA** (Friendly Captcha, captcha.eu, mosparo, TrustCaptcha) | Mostly invisible | Data goes to an outside service, which needs a processing agreement and policy text; mosparo can be hosted yourself | Extension plus account or own server |
| **Google reCAPTCHA** (`haffner/jh_captcha`) | Sometimes a puzzle | Sends visitor data to Google, so I advise against it for a public authority | Extension plus Google account |

#### My recommendation

Add ALTCHA on top of the honeypot. It stops automated spam without an outside service or cookies, and most visitors won't notice it. For a municipality, that keeps the privacy side simple.

Before relying on it, I'd install it in this project and test it against 14.3. The extension only states that it supports 14, and I haven't tested it yet. It also adds a JavaScript widget and a third-party package the site will need to keep updated.

I'd skip the link filter for now, because citizens sometimes paste links on purpose, and it's quick to add later if spam with links shows up. I'd also hold off on the rate limit unless spam keeps getting through, since it means custom code and handling IP addresses.

Should I try ALTCHA? I'd install it, add it to the contact form, test it, and show you the diff before anything is committed.

## 7. Prompt

> yes, try ALTCHA

ALTCHA is working on the contact form. Visitors tick "I'm not a robot" before sending, and the honeypot stays as a second layer. Nothing is committed.

**What I tested:** I solved the puzzles with a script that does the same computation as the widget.

| Case | Result |
|---|---|
| Correctly solved puzzle | Sent, exactly 1 mail |
| No solution | Rejected: "Please tick "I'm not a robot" and wait until it is checked." |
| Same solution sent again | Rejected, since each puzzle counts once |
| Fake solution | Rejected |

The form still works on cached pages, because each visitor gets a fresh puzzle from an uncached endpoint. I also took a screenshot with headless Chrome: the two-column layout and the widget render properly.

**What changed:**
- **Composer:** `bbysaeth/typo3-altcha ^2.0` is in the project's and the sitepackage's `composer.json`, and the lock file is updated.
- **Site set:** `bbysaeth/typo3-altcha` is now a dependency.
- **Form:** `contact.form.yaml` has an ALTCHA element above the button, with friendlier error texts.
- **Form setup:** `Configuration/Form/Sitepackage/config.yaml` gives the element the design's `form-field` class.
- **`config/system/settings.php`:** the extension added an empty `hmac` setting. Empty means it signs puzzles with TYPO3's encryption key, which is fine, and the change belongs in the commit.
- **README:** a section on ALTCHA. `data.xml` didn't change.

**Things you should know:**
- **It's a hurdle, not a wall.** It stops bots that don't run JavaScript or don't solve ALTCHA puzzles. A determined spammer can still solve it cheaply: my script needed 0.1 seconds. You can raise the difficulty in the site settings (`altcha.maximumComplexity`), but that slows down cheap phones too.
- **Cleanup needs a cron job.** Every page view stores one puzzle in the database, and nothing deletes them automatically. The scheduler extension isn't installed, so production needs a cron job running `typo3 altcha:remove-obsolete-challenges --include-solved-challenges`. It's in the README.
- **Ready for TYPO3 14.3 but not 15.** The extension registers its form setup the old TypoScript way, which TYPO3 14.2 deprecated and 15 removes. It works on 14.3, but the extension must be updated before any move to TYPO3 15.
- **Visitors have to click the checkbox.** It verifies in under a second. Setting `altcha.auto: onfocus` would solve the puzzle as soon as someone starts filling in the form, so it's usually done before they reach the checkbox.
- **Content Security Policy:** the extension loosens the site's CSP for the widget (it allows `blob:` workers and inline styles). That only has an effect if the frontend CSP is switched on.

Still open from before: the consent text renders bold while the design shows normal weight (a small Sass fix plus an asset build), and the fresh-install check of `data.xml`. Tell me if you want either done, or say "commit" and I'll show you the commit message first.

## 8. Prompt

> why are the mails I receive TYPO3 themed now and with a TYPO3 imprint?

ALTCHA caused it, and I missed it because I didn't check the mail layout after installing it. It's fixed now: a new test mail uses the Mto Safi layout again, with no TYPO3 imprint and no ALTCHA value.

**Why it happened:** ALTCHA ships its own copy of TYPO3's mail template, which uses the TYPO3 "SystemEmail" layout with the logo and copyright notice. It registers that copy for both mail finishers under key `2011`. When several template paths are registered, the one with the highest key wins, and my site template had key `20`. So after ALTCHA was installed, its template replaced the site's. ALTCHA ships the copy so the solved puzzle doesn't appear as a form value in the mail.

**The fix, in two files:**
- **Form setup:** in `Configuration/Form/Sitepackage/config.yaml`, the site's mail template and layout paths now use key `2100` for both finishers, so they win over ALTCHA's `2011`. A comment explains the number.
- **Mail templates:** the site's `Default.fluid.html` and `Default.fluid.txt` leave out the ALTCHA field, as ALTCHA's own template does. Otherwise the long encoded puzzle solution would appear in the mail.

This will happen again whenever an extension registers mail templates with a higher key. After installing or updating a form extension, check one mail in Mailpit.

Nothing is committed yet. Still open: the consent text renders bold instead of normal weight, and the fresh-install check of `data.xml`.

## 9. Prompt

> If you can solve this puzzle so quickly cant an attacker do the same?

Yes. ALTCHA makes each submission cost a little computing time; it doesn't stop someone who sets out to beat it. I measured it on your laptop:

- One CPU core solves a puzzle in about **0.2 seconds** on average (10 tries, between 0.0 and 0.6 s).
- Your laptop has 16 cores, so a script like mine could solve about **80 puzzles a second**, which is several hundred thousand an hour. Writing the script took me a few minutes of reading the library's source.

#### What it does stop

Most spam on a small site comes from generic bots that crawl the web and fill in any form they find. They don't run JavaScript and don't solve ALTCHA puzzles, so they can't get past it. A puzzle can be used only once and expires after 6 minutes, so solutions can't be collected and reused. Someone has to target this site specifically.

#### What a targeted attacker could do, and how much it matters

Since we removed the copy to the sender, the form only sends mail to `info@mtosafi.example`. An attacker can't use it to send spam to other people. The worst they can do is flood the municipality's inbox, which is annoying but gains them nothing. That's why few people would bother.

#### Making it harder

| Measure | Effect | Downside |
|---|---|---|
| **Raise ALTCHA difficulty** (`altcha.maximumComplexity`) | Every solve gets slower | A visitor's cheap phone waits too. A factor of 10 means several seconds on a phone, but only 2 s per solve for the attacker, so it still doesn't stop them. |
| **Limit submissions per IP address**, e.g. 5 an hour | Caps how fast one machine can send, whatever it solves | Custom code. It processes IP addresses, which the privacy policy must mention. An attacker with many IP addresses can spread out. |
| **Content filter**, e.g. reject messages with links | Takes away the payoff of link spam | Also blocks citizens who paste a link |
| **Mail rules at the municipality** | The mail server or a filter sorts the messages | Not in TYPO3; the municipality's IT does it |

#### My recommendation

Keep the honeypot and ALTCHA as they are. Together they stop untargeted bots, which is the spam a municipal contact form actually gets, without bothering visitors. Raising the difficulty mostly hurts visitors on slow phones.

If the site gets targeted floods later, the next step is a per-IP limit, not a harder puzzle. TYPO3 already has a built-in rate limiter, the one the backend login uses, and a small validator in the sitepackage could use it.

Do you want me to build the per-IP limit now, or wait until spam actually shows up?

## 10. Prompt

> where is the personal data of the senders saved?

TYPO3 doesn't store the sender's data anywhere. It exists only in the mail. I checked this by searching the installation for the test sender, "Amina Otieno" / `amina@example.org`:

| Place | Result |
|---|---|
| Whole database, including page caches | 0 hits |
| TYPO3 logs (`var/log/`) | 0 hits |
| Web server and PHP logs | 0 hits. nginx logs each submission's IP address, time and URL, but not the contents. |
| ALTCHA table | Only the puzzle nonce, a timestamp and "solved", with no IP address and nothing from the form |
| Cookies | None, not even after sending |

#### Where the data does end up

1. **The mail to `info@mtosafi.example`.** It holds everything the visitor entered except the consent box and the ALTCHA answer. On DDEV that's Mailpit. On the live site it's:
   - the outgoing mail server, which may queue the message briefly and logs sender and recipient addresses;
   - the municipality's mail provider and the `info@` mailbox, where it stays until someone deletes it;
   - everywhere the staff forward, print or file it.
2. **The visitor's browser, briefly.** If the form comes back with an error, the entered values are in the page and in a signed hidden field. Nothing is stored on the server.
3. **The web server log.** It keeps the IP address and time of every submission, as for every page view, and on the live server how long it's kept depends on the hosting setup.

#### What that means for privacy

- All retention and deletion happens in the mailbox, under the municipality's mail policy. That makes the `info@` mailbox the place to decide who can read these messages and how long they're kept.
- Because nothing goes into TYPO3, there's nothing in TYPO3 to export or delete when a citizen asks for their data. The municipality answers such requests from the mailbox.
- The privacy policy, still placeholder text, should say this: what the form collects, that it arrives by email at the municipality, who reads it, how long it's kept, and that the web server logs IP addresses.
- If the per-IP spam limit we discussed is ever added, it would keep IP addresses for a short time and would need its own line in the policy.

I didn't check the live server's mail and server logs, because this project has no live server yet. Those depend on the hosting.
