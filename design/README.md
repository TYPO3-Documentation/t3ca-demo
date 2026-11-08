# Mto Safi Municipality — static design

Design delivery for the website of Mto Safi, a **fictional** municipality on
the Kenyan coast. The name is Swahili for "clean river". The pages are static
HTML; the styles are SCSS, compiled to `css/main.css`; the scripts are plain
JavaScript without a framework.

Open `index.html` in a browser to look at it. Every page works from disk.

## Pages and their layouts

| Page              | Layout        | What it shows                                         |
|-------------------|---------------|-------------------------------------------------------|
| `index.html`      | Home          | Slider, quick links, news, events, newsletter sign-up |
| `land-rates.html` | Sidebar left  | Sub-navigation, content, land rates calculator        |
| `contact.html`    | Full width    | Contact form, address and opening hours               |
| `search.html`     | Sidebar right | Search results with filters                           |
| `login.html`      | Narrow        | Login to the e-services portal                        |

Every page shares the header with the site search and the footer.

## Components

- **Site search** in the header. It submits to `search.html?q=…`.
- **Slider** on the home page: previous/next, dots, arrow keys, autoplay with a
  pause button. Autoplay stays off for visitors who prefer reduced motion.
- **Newsletter sign-up** on the home page: email, topics, consent. Double
  opt-in: the confirmation tells the visitor to check their inbox.
- **Contact form**: validated in the browser, with an error summary.
- **Land rates calculator**: the annual rate from the land use and the
  rateable value, with interest for each month paid late, in Kenya shillings.
  The rates are data attributes on the calculator, not part of the script.
  `docs/land-rates-calculation.md` explains the rule it applies.
- **Login** to the e-services portal, with a show/hide toggle for the
  password.

The forms do not send anything: the design shows the success message in the
browser. The website has to supply the real processing.

## Look

Green, red and gold, after the Kenyan flag and the coast's sun. The flag itself
and the coat of arms are not used: the footer carries the three stripes, and
the logo is a river under the sun. The language switch offers Kiswahili.

## Accessibility

The design targets WCAG 2.1 AA: a skip link, landmarks, visible focus,
labelled form fields, error messages tied to their fields, and colours with
sufficient contrast.

## Files

    scss/main.scss        entry point; imports the partials below
    scss/_variables.scss  colours, fonts, spacing, breakpoints
    scss/_base.scss       reset, typography, helpers
    scss/_layout.scss     container, header, footer, the five page layouts
    scss/_components.scss buttons, cards, slider, forms, calculator, search results
    js/navigation.js      mobile menu
    js/slider.js          slider
    js/forms.js           validation of the contact, newsletter and login forms
    js/calculator.js      land rates calculator
    js/search.js          search term and result filters on search.html
    images/               logo and slider images (SVG)
    docs/                 content the municipality kept in documents so far:
                          news-and-events.docx, land-rates-calculation.md

## Build

    npm install
    npm run build    # scss/ → css/main.css
    npm run watch
