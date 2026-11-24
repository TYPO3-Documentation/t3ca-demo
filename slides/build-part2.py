"""Builds slides/part2.html from the frame of slides/index.html (styles, drawings, script) and the
slides below. Change part 2 here, then run: python3 slides/build-part2.py"""
import re
import os
SL = os.path.dirname(os.path.abspath(__file__)) + '/'
src = open(SL + 'index.html', encoding='utf-8').read()
head = src[:src.index('<div id="deck">')]
tail = src[src.index('<div id="counter"></div>'):]
head = head.replace('<title>AI meets TYPO3</title>', '<title>AI meets TYPO3, part 2</title>')
tail = tail.replace('<script src="casts/03-site-package.js"></script>', '<script src="casts/07-slider.js"></script>')
# A click on a link (the live slides) opens it and does not move to the next slide.
old_click = "document.addEventListener('click', () => { if (!slides[current].classList.contains('replay')) show(current + 1); });"
new_click = "document.addEventListener('click', (event) => { if (event.target.closest('a')) return; if (!slides[current].classList.contains('replay')) show(current + 1); });"
tail = tail.replace(old_click, new_click)
assert new_click in tail, 'click handler not found'
s = src.index('<div class="titlebar">'); e = src.index('</div>\n      <div class="messages">', s)
titlebar = '      ' + src[s:e] + '</div>'
mark = re.search(r'<g transform="translate\(176 246\) scale\(5\)" fill="#fff">(.*?)</g>', src, re.S).group(1)
final = re.search(r'  <!-- ================= Questions: more in the repository ================= -->.*?</section>\n', src, re.S).group(0)
farewell_img_note = 'Thank you. Benny says thank you too, all knitted up.'

def chat(pairs, note, comment):
    msgs = ''
    for who, text in pairs:
        av, name, cls = ('lina', 'Lina', 'me') if who == 'me' else ('claudia', 'Claudia', 'claudia')
        msgs += (f'        <div class="msg {cls}">\n          <div class="avatar"><img src="images/avatars/{av}.jpg" alt=""></div>\n'
                 f'          <div class="bubble"><span class="sender">{name}</span>{text}</div>\n        </div>\n')
    return ('  <section class="chat">\n    <div class="window">\n' + titlebar + '\n      <div class="messages">\n'
            f'        <!-- {comment} -->\n' + msgs + '      </div>\n    </div>\n'
            f'    <aside class="notes">{note}</aside>\n  </section>\n\n')

def picture(img, alt, note, style='', caption=''):
    st = f' style="{style}"' if style else ''
    cap = f'\n    <div class="caption">{caption}</div>' if caption else ''
    return f'  <section class="picture">\n    <img src="images/{img}" alt="{alt}"{st}>{cap}\n    <aside class="notes">{note}</aside>\n  </section>\n\n'

def code(lang, text, note):
    return (f'  <section class="terminal">\n    <div class="term">\n      <div class="term-bar"><i></i><i></i><i></i><span class="lang">{lang}</span></div>\n'
            f'      <pre class="code">{text}</pre>\n    </div>\n    <aside class="notes">{note}</aside>\n  </section>\n\n')

def shot(img, alt, note, caption=''):
    cap = f'\n    <div class="caption url small-url">{caption}</div>' if caption else ''
    return f'  <section class="picture screenshot">\n    <img src="screens/{img}" alt="{alt}">{cap}\n    <aside class="notes">{note}</aside>\n  </section>\n\n'

def live(url, note):
    return (f'  <section class="graphic live">\n    <svg viewBox="0 0 1920 1080" role="img" aria-label="Live in the browser: {url}">\n'
            f'      <a href="https://{url}" target="_blank">\n'
            '        <use href="#browser" x="360" y="230" width="1200" height="746"/>\n'
            '        <rect x="580" y="258" width="800" height="50" rx="25" fill="#fff"/>\n'
            f'        <text x="980" y="295" text-anchor="middle" font-family="Source Sans 3, sans-serif" font-size="34" fill="#073d29">{url}</text>\n'
            '      </a>\n'
            '      <text x="960" y="160" text-anchor="middle" font-family="Source Sans 3, sans-serif" font-weight="700" font-size="96" fill="#f2b632">Live</text>\n'
            '      <text x="960" y="1040" text-anchor="middle" font-family="Source Sans 3, sans-serif" font-size="30" fill="#9fb3a8">click the window to open it</text>\n'
            f'    </svg>\n    <aside class="notes">Click the window: it opens {url} in a new tab. {note}</aside>\n  </section>\n\n')

def take(icon, l1, l2, label, note, size=100):
    gap = int(size * 1.4)
    return (f'  <section class="graphic">\n    <svg viewBox="0 0 1920 1080" role="img" aria-label="{label}">\n      {icon}\n'
            '      <g font-family="Source Sans 3, sans-serif" font-weight="700">\n'
            f'        <text x="960" y="{570 - gap // 2}" font-size="{size}" fill="#f3f7f4">{l1}</text>\n'
            f'        <text x="960" y="{570 + gap // 2}" font-size="{size}" fill="#f2b632">{l2}</text>\n      </g>\n    </svg>\n'
            f'    <aside class="notes">{note}</aside>\n  </section>\n\n')

S = []
S.append('''  <section class="words">
    <h1>AI meets <em>TYPO3</em></h1>
    <p>Part 2: thinking like a<br>TYPO3 developer</p>
    <p class="small">TYPO3 Camp Africa 2026 · Diani</p>
    <aside class="notes">Same room, same Claudia. Part 2.</aside>
  </section>

''')
S.append(picture('benny-tunnel.jpg', 'Benny crawling out of a play tunnel', 'Where were we? Claudia built the contact form. Today: a bug, choosing extensions, content elements for editors, events, a second language. And Benny instead of Halma.'))

# --- 08: a bug report
S.append('  <!-- ================= 08: a bug report ================= -->\n\n')
S.append(picture('typo3-decision-dice.jpg', 'The TYPO3 decision dice: Clear the cache, Works on my machine, Core bug', 'Debugging the classic way: roll the dice. Clear the cache? Works on my machine? Core bug?'))
S.append('''  <section class="picture">
    <img src="screens/error-page-typo3.png" alt="TYPO3's built-in error page: 404, Page Not Found, Request parameters could not be validated (&amp;cHash empty)">
    <aside class="notes">What I saw after searching for "water": TYPO3's built-in error page, the whole page. Read the small print: "Request parameters could not be validated (&amp;cHash empty)". The cause is right there.</aside>
  </section>

''')
S.append(chat([('me', 'when I click on the search button an error page appears'),
               ('claudia', '<mark>The search button works now.</mark> <code>/search?q=test</code> returns 200 instead of the 404 error page.')],
              'Stage 08. I did not ask for the cause first, and Claudia fixed it right away; the explanation came afterwards. Here it was harmless. With a bigger change I would have said: "Find the cause and explain it to me before you change anything."',
              'Stage 08-error-page, as typed; the first sentences of the answer.'))
S.append(code('PHP · ext_localconf.php',
              '<span class="c">// The search form in the page header submits "q" without a cHash; with\n// FE.cacheHash.enforceValidation the request would otherwise end in a 404.</span>\n$GLOBALS[\'TYPO3_CONF_VARS\'][\'FE\'][\'cacheHash\']\n    [\'excludedParameters\'][] = <span class="s">\'q\'</span>;',
              'The fix, one line, with Claudia\'s comment. The cHash: a checksum TYPO3 adds to URLs with parameters, so nobody can fill the page cache with endless variants (?q=1, ?q=2, …). Unknown parameters without a cHash end in a 404. A search term is different on every request, so it is left out of the check.'))
S.append(chat([('me', 'https://demo.ddev.site/apple-tree gives me the same error page'),
               ('claudia', '<mark>That one isn\'t the same bug.</mark> <code>/apple-tree</code> isn\'t a page on your site, so a 404 is the right answer. What you\'re seeing is TYPO3\'s built-in error page.')],
              'A web developer tests a made-up URL. Claudia separates the two problems instead of "fixing" a correct 404.',
              'Stage 08-error-page, as typed; the first sentences of the answer, shortened.'))
S.append(code('YAML · config/sites/main/config.yaml',
              'errorHandling:\n  - errorCode: 404\n    errorHandler: Page\n    errorContentSource: <span class="s">\'t3://page?uid=33\'</span>',
              'The TYPO3 way: 404 handling is part of the site configuration. It points to a normal page, so editors maintain the text. It still answers with status 404.'))
S.append(shot('error-page-site.png', 'The Page not found page in the Mto Safi design, with links to the home page and the search',
              'After: a page that does not exist shows "Page not found" in the site\'s design, still with status 404. And /search?q=water now answers, but there is no search engine yet: that is the next section.',
              caption='apple-tree'))

# --- 09: choosing an extension
S.append('  <!-- ================= 09: choosing an extension ================= -->\n\n')
S.append(picture('dogdancing/menzigen-2024-072A3375.jpg', 'Lina sitting, one hand raised: wait. Benny lying in front of her', 'Choosing an extension: wait, and ask before you install.'))
S.append(chat([('me', 'How would you recommend to implement the search?'),
               ('claudia', 'I\'d use <mark>ke_search</mark> (version 7.5.0 on TER, works with TYPO3 13 and 14).')],
              'Stage 09. A recommendation with a version and a reason. Now the questions a project lead asks.',
              'Stage 09-search, as typed; the first sentence of the answer.'))
S.append(chat([('me', 'who maintains ke_search?'),
               ('claudia', 'The registries I checked don\'t name a maintainer, so <mark>most of this answer is from memory and I haven\'t verified it.</mark>')],
              'Honest: it says what it checked and what it did not. Worth a look yourself: TER, Packagist, the repository.',
              'Stage 09-search, as typed; the first sentence of the answer.'))
S.append(chat([('me', 'what does it cost?'), ('me', 'is there a free option?'),
               ('claudia', 'Yes. The base ke_search I recommended is <mark>free (0 €)</mark>. The 890 € premium version only adds extras such as autocomplete and "Did you mean…?".')],
              'With sources from the vendor\'s website. Free covers the whole design.',
              'Stage 09-search, prompts 3 and 4 as typed; from the answer to 4, shortened.'))
S.append(chat([('me', 'Our other websites are using solr I believe'),
               ('claudia', '<mark>That changes my recommendation.</mark> I called Solr "too much" because you\'d have to run a server, but if your team already runs Solr and knows it, that cost is mostly paid.')],
              'The human brings the context the AI cannot know. Then: which Solr version? Apache Solr 10, which we did not have yet. Decision: ke_search now, Solr later, and build it so the switch is cheap.',
              'Stage 09-search, as typed; the first sentences of the answer.'))
S.append(chat([('me', 'oh we dont have a solr core version 10.0 yet. Can we go with ke_search now and replace it with solr once cores become available?'),
               ('claudia', 'Yes, that works well. ke_search and EXT:solr have the same overall shape. <mark>Most of what we build can stay when you switch.</mark>')],
              'The way out, planned before we start: keep the search page, make the query parameter a setting, own partials for the result markup, the same type keys (service, news, document), only free ke_search features. Thrown away when switching: the indexer and filter records, the dependency, the ke_search tables. And: with Solr, PDFs need EXT:solrfal, which is paid.',
              'Stage 09-search, as typed; the answer shortened.'))
S.append('''  <section class="graphic">
    <svg viewBox="0 0 1920 1080" role="img" aria-label="ke_search now, Solr later, both on the same foundation: search page, templates, type keys">
      <g font-family="Source Sans 3, sans-serif" font-weight="700" text-anchor="middle">
        <rect x="300" y="250" width="520" height="220" rx="30" fill="#f2b632"/>
        <text x="560" y="345" font-size="72" fill="#073d29">ke_search</text>
        <text x="560" y="420" font-size="44" font-weight="400" fill="#073d29">now</text>
        <path d="M860 360h180" stroke="#f3f7f4" stroke-width="16" stroke-linecap="round"/>
        <path d="M1010 320l50 40-50 40" fill="none" stroke="#f3f7f4" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="1100" y="250" width="520" height="220" rx="30" fill="none" stroke="#f3f7f4" stroke-width="8" stroke-dasharray="26 18"/>
        <text x="1360" y="345" font-size="72" fill="#f3f7f4">Solr</text>
        <text x="1360" y="420" font-size="44" font-weight="400" fill="#f3f7f4">later</text>
        <rect x="300" y="560" width="1320" height="260" rx="30" fill="#f3f7f4"/>
        <text x="960" y="660" font-size="56" fill="#073d29">what stays</text>
        <text x="960" y="750" font-size="44" font-weight="400" fill="#073d29">search page · templates · type keys</text>
      </g>
    </svg>
    <aside class="notes">Plan the way out before you go in. Only the plugin and the indexer change; the page, the markup and the filters stay.</aside>
  </section>

''')
S.append(take('''<g transform="translate(260 230)" font-family="Source Sans 3, sans-serif" font-weight="700" font-size="54" fill="#073d29">
        <rect width="560" height="580" rx="28" fill="#f3f7f4"/>
        <g fill="#f2b632"><circle cx="80" cy="110" r="34"/><circle cx="80" cy="230" r="34"/><circle cx="80" cy="350" r="34"/><circle cx="80" cy="470" r="34"/></g>
        <g fill="none" stroke="#073d29" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"><path d="M64 110l12 12 22-24"/><path d="M64 230l12 12 22-24"/><path d="M64 350l12 12 22-24"/><path d="M64 470l12 12 22-24"/></g>
        <text x="140" y="128">maintainer</text><text x="140" y="248">cost</text><text x="140" y="368">versions</text><text x="140" y="488">way out</text>
      </g>''', 'Check the', 'extension.', 'A checklist: maintainer, cost, versions, way out',
              'Not just the feature: who maintains it, what it costs, which TYPO3 versions it supports, and how you get out again. Same questions for eventnews later.'))
S.append(chat([('me', 'Why does "new" not find anything?'),
               ('claudia', '"new" finds nothing because of how MySQL/MariaDB\'s full-text search is set up, <mark>not because of ke_search or our templates.</mark>')],
              'Words under four letters are not in the full-text index, and "new" is also a stopword. The fix would be a database server setting, which shared hosting often does not allow. My decision: "new" does not need to be found.',
              'Stage 09-search, as typed; the first sentence of the answer.'))
S.append(live('t3ca-14.ddev.site/search?q=market', 'The search on the finished site (stage 14), with the example content, news and events: 9 results with type badges. Then the "Show only" filter: Services or News.'))

# --- 06/07: content elements
S.append('  <!-- ================= 06/07: content elements ================= -->\n\n')
S.append(picture('dogdancing/munsingen-2025-2.jpg', 'Halma in mid-air with a ball in her mouth', 'Content elements: let editors do it.'))
S.append(shot('design-index.png', 'The home page of the static design: slider, service tiles, news, events',
              'The home page in the design: a slider, service tiles, news, events, a newsletter sign-up. After the site package, TYPO3 showed only the news.'))
S.append(chat([('me', 'The home page only shows the news. In the design it also has a slider, service tiles, events and a newsletter sign-up. What do I need for that?'),
               ('claudia', "Your sitepackage already has the CSS for all four sections. <mark>What's missing is the content behind them</mark>, plus two scripts.")],
              'Stage 06. The design is done, the CSS too. What is missing: places where editors put the content. In TYPO3 that means content elements with fields.',
              'Stage 06-home-page, as typed; two sentences of the answer.'))
S.append(chat([('me', 'That sounds like a lot. What should I start with, and why?'),
               ('claudia', 'Start with the <mark>service tiles</mark>: it\'s the smallest piece, and it sets up the pattern for the slider.')],
              'Stage 06. Small steps, and the first one makes the second one easy.',
              'Stage 06-home-page, as typed (without the typo); the answer\'s first two points, shortened.'))
S.append('''  <section class="replay" data-cast="07-slider" data-speed="1" data-idle="3">
    <div class="player"></div>
    <aside class="notes">Stage 07 in real time, thinking squeezed out: one prompt, "Lets do the slides next". Space: play/pause. PageDown: next slide.</aside>
  </section>

''')
S.append(code('PHP · TCA, shortened',
              '<span class="c">// The slides belong to the element and are edited inside its form.</span>\n\'tx_mtosafisitepackage_slides\' =&gt; [\n    \'config\' =&gt; [\n        \'type\' =&gt; <span class="s">\'inline\'</span>,\n        \'foreign_table\' =&gt; <span class="s">\'tx_mtosafisitepackage_slide\'</span>,\n    ],\n],\nExtensionManagementUtility::addRecordType([\n    \'value\' =&gt; <span class="s">\'mtosafi_slider\'</span>,\n    \'group\' =&gt; <span class="s">\'mtosafi\'</span>,\n], \'header, tx_mtosafisitepackage_slides, …\');',
              'TCA: TYPO3\'s schema and backend form in one (think schema plus admin form). A new content type "Slider" with child records, the slides. Editors sort, hide and translate them in the backend.'))
S.append(code('Fluid · Slider.fluid.html, shortened',
              '&lt;f:for each=<span class="s">"{record.tx_mtosafisitepackage_slides}"</span> as=<span class="s">"slide"</span>&gt;\n  &lt;f:image image=<span class="s">"{slide.image}"</span> alt=<span class="s">""</span> width=<span class="s">"1600c"</span> height=<span class="s">"560c"</span> /&gt;\n  &lt;h2&gt;{slide.title}&lt;/h2&gt;\n  &lt;f:link.typolink parameter=<span class="s">"{slide.link}"</span>&gt;\n    {slide.link_label}\n  &lt;/f:link.typolink&gt;\n&lt;/f:for&gt;',
              'Fluid, TYPO3\'s template engine (think Twig or Blade): the design\'s markup, filled from the records. Images are cropped to 1600×560 by TYPO3.'))
S.append(live('t3ca-07.ddev.site/typo3', 'The backend: Page module, the home page, edit the Slider element and show the slides. Logged in beforehand.'))

# --- 13: events
S.append('  <!-- ================= 13: events ================= -->\n\n')
S.append(picture('dogdancing/stgallen-2025-tournament-DSC_6184.jpg', 'Halma jumping into Lina\'s arms at a dog dancing tournament, under the banner Gemeinschaft Dogdance Schweiz', 'Events. A tournament is an event too: the Swiss dog dancing community.'))
S.append(chat([('me', 'where are the events displayed at?'),
               ('me', 'what technique should we use to display them?'),
               ('claudia', "I recommend <mark>georgringer/eventnews</mark>. It extends the news extension with events. It runs on your versions: 7.2.0 requires TYPO3 13.4 or 14.3.")],
              "Stage 13. The events from the municipality's Word document. Remember part 1: in stage 05 Claudia said eventnews only supports TYPO3 12. This time she checked Packagist, and it runs on 14.3. Next question: is it maintained?",
              'Stage 13-events, prompts 1 and 2 as typed; the answer to 2, shortened.'))
S.append(chat([('me', 'is it still actively maintained?'),
               ('claudia', 'Yes, it\'s actively maintained, though <mark>by one person</mark>, Georg Ringer, the author of the news extension.')],
              'Stage 13, about eventnews. It checked GitHub: last release six weeks ago. And it named the risk and the way out: the events stay news records.',
              'Stage 13-events, as typed; the first sentence of the answer.'))
S.append(chat([('me', 'What happens if we have events spanning multiple days?'),
               ('claudia', '<mark>I read the query code</mark> of eventnews 7.2.0 to check this. Multi-day events are handled properly.')],
              'It looked it up in the code instead of guessing. Past events disappear by themselves: the list shows upcoming events by date.',
              'Stage 13-events, as typed; the first sentences of the answer.'))
S.append(chat([('me', 'the events are on the news page and when you use de breadcrumb you only see news not events'),
               ('claudia', 'Events now have their own pages, so the breadcrumb on an event <mark>leads back to the events list</mark> instead of the news.')],
              'I found that by clicking around, like a visitor. The AI had tested the pages, not the way through them.',
              'Stage 13-events, as typed; the first sentence of the answer.'))
S.append(live('t3ca-13.ddev.site', 'The events on the home page, a multi-day event, then the breadcrumb on an event.'))

# --- 14: a second language
S.append('  <!-- ================= 14: a second language ================= -->\n\n')
S.append(picture('winter-selfie-halma.jpg', 'Lina and Halma on a snowy morning', 'A second language.'))
S.append(chat([('me', 'we still need the language menu'),
               ('claudia', 'The language menu now works. The header\'s top bar links to the <mark>Kiswahili version of the current page</mark>, and on a Kiswahili page it links back to English.')],
              'Stage 14. The design had a "Kiswahili" link from the start; it led nowhere. Claudia added the language to the site configuration, translated page titles and URLs, and the home page.',
              'Stage 14-kiswahili, as typed; the first sentences of the answer.'))
S.append('''  <section class="picture">
    <img src="screens/kiswahili-home.png" alt="The Mto Safi home page in Kiswahili: Karibu Mto Safi, Huduma maarufu">
    <aside class="notes">The home page in Kiswahili: the menu (Mwanzo, Mapato na malipo, …), "Karibu Mto Safi", "Huduma maarufu". Ask the room: is it any good? Then: the search.</aside>
  </section>

''')
S.append(chat([('me', 'searching for ardhi there is no result'),
               ('claudia', 'Searching for "ardhi" now works. ke_search only indexes a page translation that has <mark>content elements in its own language.</mark>')],
              'Stage 14. "ardhi" is Kiswahili for land. I tested the search in the new language; the AI had not.',
              'Stage 14-kiswahili, as typed; two sentences of the answer.'))
S.append(chat([('me', 'How to translate the contact form?'),
               ('claudia', 'A form isn\'t translated as a content element. The form framework translates the form definition through <mark>XLF files</mark>.')],
              'The TYPO3 way: languages per site, content translated record by record, labels and forms in XLF files.',
              'Stage 14-kiswahili, as typed; the first sentences of the answer.'))
S.append('''  <section class="graphic">
    <svg viewBox="0 0 1920 1080" role="img" aria-label="The site's languages: English, Kiswahili, and maybe Amharic or Arabic">
      <g font-family="Source Sans 3, sans-serif" font-weight="700" font-size="72" text-anchor="middle">
        <rect x="260" y="380" width="380" height="170" rx="30" fill="#f3f7f4"/><text x="450" y="490" fill="#073d29">English</text>
        <rect x="770" y="380" width="380" height="170" rx="30" fill="#f2b632"/><text x="960" y="490" fill="#073d29">Kiswahili</text>
        <rect x="1280" y="380" width="380" height="170" rx="30" fill="none" stroke="#f3f7f4" stroke-width="8" stroke-dasharray="24 16"/><text x="1470" y="490" fill="#f3f7f4">አማርኛ</text>
        <rect x="1280" y="600" width="380" height="170" rx="30" fill="none" stroke="#f3f7f4" stroke-width="8" stroke-dasharray="24 16"/><text x="1470" y="710" fill="#f3f7f4" lang="ar" direction="rtl">العربية</text>
      </g>
    </svg>
    <aside class="notes">And your language? The same steps: a language in the site configuration, translated pages and content, XLF files for labels and the form. TYPO3 does not know Kiswahili or Amharic as label languages yet, so they are registered in the site package, like Kiswahili in stage 14. Arabic TYPO3 knows, and it writes dir="rtl" on the page by itself, because the locale is right to left. The work is in the CSS: the Mto Safi design has 11 rules with left and right (margin-left, text-align: left …) that would become logical properties (margin-inline-start …). The AI writes a draft translation; a native speaker checks it.</aside>
  </section>

''')
S.append(live('t3ca-14.ddev.site/sw/', 'The site in Kiswahili, then search for "ardhi", then the contact form in Kiswahili.'))

# --- takeaways part 2
S.append('  <!-- ================= Takeaways, part 2 ================= -->\n\n')
bug = ('<g transform="translate(260 260)"><circle cx="230" cy="230" r="200" fill="none" stroke="#f2b632" stroke-width="40"/>'
       '<path d="M375 375l150 150" stroke="#f2b632" stroke-width="60" stroke-linecap="round"/>'
       '<text x="230" y="320" text-anchor="middle" font-family="Source Sans 3, sans-serif" font-weight="700" font-size="260" fill="#f3f7f4">?</text></g>')
ce = ('<g transform="translate(240 250)"><rect width="580" height="540" rx="28" fill="#f3f7f4"/>'
      '<rect x="40" y="40" width="500" height="200" rx="16" fill="#f2b632"/><rect x="40" y="280" width="230" height="90" rx="14" fill="#9fb3a8"/><rect x="310" y="280" width="230" height="90" rx="14" fill="#9fb3a8"/>'
      '<rect x="40" y="410" width="230" height="90" rx="14" fill="#9fb3a8"/><rect x="310" y="410" width="230" height="90" rx="14" fill="#9fb3a8"/></g>')
click = ('<g transform="translate(330 220)"><path d="M0 0l0 460 120-110 90 210 80-36-90-206 160-12z" fill="#f3f7f4" stroke="#073d29" stroke-width="10" stroke-linejoin="round"/>'
         '<circle cx="0" cy="0" r="90" fill="none" stroke="#f2b632" stroke-width="16"/><circle cx="0" cy="0" r="150" fill="none" stroke="#f2b632" stroke-width="10" opacity="0.6"/></g>')
checklist = '''<g transform="translate(260 230)" font-family="Source Sans 3, sans-serif" font-weight="700" font-size="54" fill="#073d29">
        <rect width="560" height="580" rx="28" fill="#f3f7f4"/>
        <g fill="#f2b632"><circle cx="80" cy="110" r="34"/><circle cx="80" cy="230" r="34"/><circle cx="80" cy="350" r="34"/><circle cx="80" cy="470" r="34"/></g>
        <g fill="none" stroke="#073d29" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"><path d="M64 110l12 12 22-24"/><path d="M64 230l12 12 22-24"/><path d="M64 350l12 12 22-24"/><path d="M64 470l12 12 22-24"/></g>
        <text x="140" y="128">maintainer</text><text x="140" y="248">cost</text><text x="140" y="368">versions</text><text x="140" y="488">way out</text>
      </g>'''
TAKES = [
    (bug, 'Ask for the cause', 'before the fix.', 'A magnifying glass with a question mark',
     'Before the fix: what is the cause? In stage 08 I did not ask, and it was harmless. "Find the cause and explain it to me before you change anything."'),
    (checklist, 'Check the extension,', 'not just the feature.', 'A checklist: maintainer, cost, versions, way out',
     'Who maintains it, what it costs, which TYPO3 versions it supports, how you get out again. And bring the context the AI cannot know: "Our other websites are using Solr".'),
    (ce, 'Build for editors,', 'not for the template.', 'A content element made of blocks',
     'Content elements with fields instead of markup in the template: the TYPO3 way. Editors sort, hide and translate.'),
    (click, 'Test it like', 'a visitor.', 'A mouse pointer clicking',
     'Click through it yourself: the breadcrumb, the search in the new language, a made-up URL. The AI tests pages; you test the way through them.'),
]
# The announcement: all four, small, so the audience knows the summary starts.
tiles = ''
for k, (icon, l1, l2, *_rest) in enumerate(TAKES):
    # every icon is drawn in about x 180-900, y 220-860; scaled to a 300 px square
    x, y = 90 + (k % 2) * 900, 250 + (k // 2) * 380
    tiles += (f'<g transform="translate({x} {y})"><g transform="scale(0.42) translate(-180 -220)">{icon}</g>'
              f'<text x="360" y="125" font-size="54" fill="#f3f7f4">{l1}</text>'
              f'<text x="360" y="195" font-size="54" fill="#f2b632">{l2}</text></g>')
S.append('  <section class="graphic">\n    <svg viewBox="0 0 1920 1080" role="img" aria-label="Four things from today">\n'
         '      <g font-family="Source Sans 3, sans-serif" font-weight="700">\n'
         '        <text x="960" y="150" text-anchor="middle" font-size="96" fill="#f2b632">Four things from today</text>\n'
         f'        {tiles}\n      </g>\n    </svg>\n'
         '    <aside class="notes">Four things from today. Then one by one.</aside>\n  </section>\n\n')
for icon, l1, l2, label, note in TAKES:
    S.append(take(icon, l1, l2, label, note, size=84))

# --- closing
S.append(picture('festive-knitted.jpg', 'Lina in a knitted hat with Benny on her lap, a Santa figure and a snowman beside them', farewell_img_note,
                 caption='Thank you! <em>Asante sana!</em><br><span class="am" lang="am">አመሰግናለሁ።</span>').replace('<div class="caption">', '<div class="caption farewell">'))
S.append(final)

css = '''  /* Live: the browser window is a link to the live site. */
  .live a { cursor: pointer; }

  /* Code: a terminal card with source code. */
  .term-bar .lang { margin-left: auto; color: #9fb3a8; font: 600 28px 'Source Sans 3', sans-serif; }
  .term pre.code { font-size: 32px; line-height: 1.55; white-space: pre; }
  .term .c { color: #9fb3a8; }
  .term .s { color: var(--accent); }

'''
head = head.replace('  /* Chat: a jambo-ai window. */', css + '  /* Chat: a jambo-ai window. */', 1)
out = head + '<div id="deck">\n\n' + ''.join(S) + '</div>\n' + tail
open(SL + 'part2.html', 'w', encoding='utf-8').write(out)
print('slides:', len(re.findall(r'^  <section class', out, re.M)))
