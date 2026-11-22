<?php

declare(strict_types=1);

/**
 * Seeds example content on the pages of the Mto Safi site that carry none yet: the
 * pages of the main navigation and the pages reached from the header and the footer.
 * Each page gets a "Text & Images" element, most of them a second element beside it:
 * a table, a list, or the menu of the subpages. "Trade & tourism" also gets a gallery
 * of four images, which the lightbox browses with previous and next. The texts are made up for the
 * fictional municipality, the land rates follow design/docs/land-rates-calculation.md
 * and the contact details design/contact.html.
 *
 * Run it after seed-search.php; the result is exported to
 * packages/mtosafi_sitepackage/Initialisation/ (see the README there).
 *
 *     ddev exec php Build/Initialisation/seed-pages.php
 *
 * It refuses to run where one of these pages has content already.
 *
 * The images are the illustrations in Build/Initialisation/images/, copied to
 * fileadmin/user_upload/pages/, with alternative text and a caption, and open full
 * size in the lightbox on a click. The accessibility statement and the privacy policy get
 * placeholder text that says so: their real text is the municipality's to write.
 */

use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

const IMAGE_FOLDER = '/user_upload/pages/';
const COLPOS_MAIN = 0;

// "Position and alignment" of a Text & Images element.
const IMAGE_BESIDE_RIGHT = 25;
const IMAGE_BESIDE_LEFT = 26;
const IMAGE_ABOVE_CENTER = 0;

$classLoader = require __DIR__ . '/../../vendor/autoload.php';
SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init($classLoader);
Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
Bootstrap::initializeBackendAuthentication();
$GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)
    ->createFromUserPreferences($GLOBALS['BE_USER']);

$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

/**
 * The uid of the page with this title anywhere in the tree seed-page-tree.php wrote.
 */
function pageByTitle(ConnectionPool $connectionPool, string $title): int
{
    $uid = $connectionPool->getConnectionForTable('pages')
        ->select(['uid'], 'pages', ['title' => $title, 'doktype' => 1, 'deleted' => 0, 'sys_language_uid' => 0])
        ->fetchOne();
    if ($uid === false) {
        fwrite(STDERR, "No page \"$title\"; run seed-page-tree.php first.\n");
        exit(1);
    }
    return (int)$uid;
}

$link = static fn(string $title): string => 't3://page?uid=' . pageByTitle($connectionPool, $title);

/**
 * A "Text & Images" element with one image. The alternative text goes on the reference,
 * because it describes the image where this page uses it.
 */
function textpic(string $header, string $bodytext, string $image, string $alternative, int $orient = IMAGE_BESIDE_RIGHT): array
{
    return ['CType' => 'textpic', 'header' => $header, 'bodytext' => $bodytext, 'image' => [[$image, $alternative]], 'imageorient' => $orient, 'imagecols' => 1];
}

/**
 * An "Images" element: a gallery of several images, each [image, alternative text,
 * caption]. In the lightbox they are one group, browsed with previous and next.
 */
function gallery(string $header, array $images, int $columns): array
{
    return ['CType' => 'image', 'header' => $header, 'image' => $images, 'imageorient' => IMAGE_ABOVE_CENTER, 'imagecols' => $columns];
}

/**
 * A table element: one row per line, the cells separated by "|", the first row the header.
 */
function table(string $header, array $rows): array
{
    return [
        'CType' => 'table',
        'header' => $header,
        'bodytext' => implode("\n", array_map(static fn(array $row): string => implode('|', $row), $rows)),
        'table_delimiter' => 124,
        'table_enclosure' => 0,
        'table_header_position' => 1,
    ];
}

function bullets(string $header, array $items): array
{
    return ['CType' => 'bullets', 'header' => $header, 'bodytext' => implode("\n", $items)];
}

function text(string $header, string $bodytext): array
{
    return ['CType' => 'text', 'header' => $header, 'bodytext' => $bodytext];
}

// Page title => its elements, top to bottom.
$content = [
    'Revenue & payments' => [
        textpic(
            'Pay the municipality from wherever you are',
            '<p>Land rates, permits, parking and market fees pay for the roads, the street lights, the markets and the waste collection of Mto Safi.</p>'
            . '<p>Most of them can be paid by mobile money or by card in the <a href="' . $link('E-services portal') . '">e-services portal</a>, at any time of day. The cash office in the municipal office is open on working days.</p>',
            'revenue.svg',
            'A mobile phone confirming a payment, beside a stack of coins'
        ),
        ['CType' => 'menu_subpages', 'header' => 'Payments and fees'],
    ],
    'Land rates' => [
        textpic(
            'How your land rates are calculated',
            '<p>Every owner of land in Mto Safi pays land rates once a year. The rate is a share of the rateable value of your plot. The valuation roll sets that value, and your valuation notice states it. The share depends on what the land is used for.</p>'
            . '<p>Pay before 31 March. From April, interest of 1 % of the annual rate is added for every month the rate stays unpaid.</p>',
            'land-rates.svg',
            'Two houses on surveyed plots on a green hillside'
        ),
        table('Rates for 2026', [
            ['Land use', 'Rate', 'Minimum per plot and year'],
            ['Residential', '0.5 % of the rateable value', 'KES 1,500'],
            ['Commercial', '1.2 % of the rateable value', 'KES 1,500'],
            ['Agricultural', '0.2 % of the rateable value', 'KES 1,500'],
        ]),
        text(
            'How to pay',
            '<p>Pay by mobile money, by card in the <a href="' . $link('E-services portal') . '">e-services portal</a>, or at the cash office in the municipal office. Keep the receipt: it is your proof of payment.</p>'
        ),
    ],
    'Single business permit' => [
        textpic(
            'One permit for your business',
            '<p>Every business in Mto Safi, from a kiosk to a hotel, needs a single business permit. It replaces the separate trade, health and fire licences, and it is valid for the calendar year.</p>'
            . '<p>Apply for a new permit or renew yours in the e-services portal. A renewal is due by 31 January.</p>',
            'business-permit.svg',
            'A shop with a striped awning and a framed business permit'
        ),
        bullets('What you need to apply', [
            'Your national ID or passport',
            'The KRA PIN of the business',
            'The plot number of the premises',
            'A description of the activity and the number of employees',
        ]),
    ],
    'Parking fees' => [
        textpic(
            'Parking in the town centre',
            '<p>Parking in the town centre and on Beach Road is paid from Monday to Saturday, 7:00 to 18:00. Sundays and public holidays are free.</p>'
            . '<p>Pay by mobile money with your number plate, or buy a monthly sticker in the e-services portal.</p>',
            'parking.svg',
            'Two cars parked beside a parking sign',
            IMAGE_BESIDE_LEFT
        ),
        table('Fees', [
            ['Vehicle', 'Per day', 'Per month'],
            ['Car', 'KES 100', 'KES 2,000'],
            ['Pick-up and van', 'KES 150', 'KES 3,000'],
            ['Lorry', 'KES 300', 'KES 6,000'],
            ['Motorcycle (boda boda)', 'KES 30', 'KES 500'],
        ]),
    ],
    'Market fees' => [
        textpic(
            'Selling at the municipal markets',
            '<p>Mto Safi runs three markets: the central market on Market Street, the fish market at the jetty and the Saturday market at the bus park.</p>'
            . '<p>Traders with a stall pay a monthly fee, traders without one a daily fee at the gate. A free stall is allocated by the market office; ask there for the waiting list.</p>',
            'market.svg',
            'Two market stalls with fruit and vegetables'
        ),
        table('Fees', [
            ['', 'Fee'],
            ['Stall, per month', 'KES 1,200'],
            ['Open space, per day', 'KES 50'],
            ['Fish market, per day', 'KES 80'],
        ]),
    ],
    'Waiver of interest' => [
        textpic(
            'Interest waived when you clear your arrears',
            '<p>Owners who pay all their land rate arrears in one sum before 30 June can apply for a waiver of the interest on them.</p>'
            . '<p>Apply at the cash office or in the e-services portal before you pay. The waiver is granted once per plot.</p>',
            'waiver.svg',
            'A calendar with a ticked check mark beside it',
            IMAGE_BESIDE_LEFT
        ),
        bullets('How it works', [
            'Ask for a statement of your arrears',
            'Apply for the waiver with the statement',
            'Pay the arrears without the interest within 30 days of the approval',
        ]),
    ],
    'Health' => [
        textpic(
            'Health services close to home',
            '<p>The municipality runs four health centres and eleven dispensaries. Maternal care, vaccinations and the treatment of common illnesses are free in all of them.</p>'
            . '<p>For an emergency, call 1199 or go to Mto Safi Sub-County Hospital, which is open day and night.</p>',
            'health.svg',
            'A health centre with a red cross sign'
        ),
        table('Health centres', [
            ['Health centre', 'Ward', 'Opening hours'],
            ['Mto Safi Town', 'Central', 'Mon–Sat 7:00–19:00'],
            ['Pwani', 'Beach', 'Mon–Fri 8:00–17:00'],
            ['Kijiji', 'Upper River', 'Mon–Fri 8:00–17:00'],
            ['Mnarani', 'Mnarani', 'Mon–Fri 8:00–17:00'],
        ]),
    ],
    'Environment & water' => [
        textpic(
            'A clean river for everyone',
            '<p>Mto Safi means "clean river", and we work to keep it that way: with a sewage plant that serves the town centre, waste collection twice a week and clean-up days in every ward.</p>',
            'water.svg',
            'A river running through green land, with a water tower and a tap'
        ),
        text(
            'Water connection',
            '<p>To connect a plot to the water supply, apply in the e-services portal with the plot number. A connection to a house on a plot along a main pipe costs KES 6,500 and takes about three weeks.</p>'
            . '<p>To report a leak or a burst pipe, call +254 700 000 001 at any time.</p>'
        ),
        bullets('Waste collection', [
            'Town centre: Monday and Thursday',
            'Beach and Mnarani: Tuesday and Friday',
            'Upper River: Wednesday and Saturday',
        ]),
    ],
    'Trade & tourism' => [
        textpic(
            'Karibu Mto Safi',
            '<p>White beaches, a quiet river mouth and the old dhow harbour bring visitors to Mto Safi all year round. Tourism and the fish trade are what the town lives from.</p>'
            . '<p>The tourist information at the jetty helps with accommodation, boat trips and guides. Businesses that want to start here find the permits they need under <a href="' . $link('Single business permit') . '">Single business permit</a>.</p>',
            'tourism.svg',
            'A dhow sailing at sunset with a palm tree on the beach',
            IMAGE_ABOVE_CENTER
        ),
        bullets('Places to visit', [
            'The dhow harbour and the fish market at sunrise',
            'The mangrove boardwalk at the river mouth',
            'Mto Safi beach and the coral garden',
            'The old town with its carved doors',
        ]),
        gallery('Impressions of Mto Safi', [
            ['tourism.svg', 'A dhow sailing at sunset with a palm tree on the beach', 'Sunset over the old dhow harbour.'],
            ['market.svg', 'Two market stalls with fruit and vegetables', 'Mangoes, tomatoes and sukuma wiki at the central market.'],
            ['water.svg', 'A river running through green land, with a water tower and a tap', 'The river mouth, where the mangrove boardwalk starts.'],
            ['office.svg', 'The municipal office with a flag on the roof', 'The municipal office, built in 1962, on Beach Road.'],
        ], 2),
    ],
    'Public participation' => [
        textpic(
            'Have your say on the budget',
            '<p>Before the county assembly decides on the budget, the municipality presents it in every ward. Come to a forum, ask questions and tell us what your ward needs most.</p>'
            . '<p>You can also send your views in writing to the clerk until 15 May.</p>',
            'participation.svg',
            'Residents gathered in front of a board at a public forum'
        ),
        table('Forum dates', [
            ['Ward', 'Date', 'Place'],
            ['Central', '4 May, 10:00', 'Municipal hall'],
            ['Beach', '6 May, 10:00', 'Pwani primary school'],
            ['Upper River', '8 May, 14:00', 'Kijiji social hall'],
            ['Mnarani', '11 May, 10:00', 'Mnarani chief\'s camp'],
        ]),
    ],
    'Contact' => [
        textpic(
            'Visit us',
            '<p>Mto Safi Municipality<br>Municipal Office<br>Beach Road 1<br>P.O. Box 123, Mto Safi</p>'
            . '<p>Phone: <a href="tel:+254700000000">+254 700 000 000</a><br>Email: <a href="mailto:info@mtosafi.example">info@mtosafi.example</a></p>',
            'office.svg',
            'The municipal office with a flag on the roof'
        ),
        table('Opening hours', [
            ['Days', 'Hours'],
            ['Mon–Thu', '8:00–17:00'],
            ['Friday', '8:00–13:00, 14:00–17:00'],
            ['Sat, Sun', 'Closed'],
        ]),
        text(
            'Report a problem',
            '<p>A pothole, a broken street light, a blocked drain or waste that was not collected: call +254 700 000 002 or write to <a href="mailto:report@mtosafi.example">report@mtosafi.example</a>, and say where it is.</p>'
        ),
    ],
    'E-services portal' => [
        textpic(
            'Your municipal services online',
            '<p>In the e-services portal you pay land rates, permits and fees, apply for a water connection and download your receipts, at any time of day.</p>'
            . '<p>Log in with your eCitizen account. You do not have one yet? Register at eCitizen first; it takes five minutes.</p>',
            'portal.svg',
            'A laptop showing a login form, beside a padlock',
            IMAGE_ABOVE_CENTER
        ),
    ],
    'Accessibility statement' => [
        textpic(
            'Example text',
            '<p>This is placeholder text for the demo site. The municipality writes its own accessibility statement here: the standard the site aims at, what is not accessible yet and how visitors report a barrier.</p>'
            . '<p>The design of this site targets WCAG 2.1 level AA.</p>',
            'accessibility.svg',
            'A web page with clear headings and a focused button, beside the accessibility symbol'
        ),
    ],
    'Privacy policy' => [
        textpic(
            'Example text',
            '<p>This is placeholder text for the demo site. The municipality writes its own privacy policy here: which personal data the site and its forms collect, what for, how long it is kept and whom visitors contact about it.</p>',
            'privacy.svg',
            'A shield with a padlock'
        ),
    ],
    'Access to information' => [
        textpic(
            'Ask for the information we hold',
            '<p>Every citizen has the right to the information the municipality holds. Ask for a document in writing, by email or at the front desk of the municipal office, and say as precisely as you can what you are looking for.</p>'
            . '<p>We answer within 21 days. Copies cost KES 10 per page; electronic copies are free.</p>',
            'documents.svg',
            'A folder of documents and a magnifying glass'
        ),
        bullets('Published without a request', [
            'The budget and the annual accounts',
            'The valuation roll',
            'The minutes of the municipal board',
            'Tenders and the contracts awarded',
        ]),
    ],
    'Tenders' => [
        textpic(
            'Doing business with the municipality',
            '<p>The municipality publishes every tender above KES 500,000 here and on the national procurement portal. Bids are submitted in a sealed envelope at the procurement office before the closing date.</p>',
            'tenders.svg',
            'A building under construction with a crane, and a sealed envelope'
        ),
        table('Open tenders', [
            ['Number', 'Subject', 'Closes'],
            ['MSM/T/01/2026', 'Resurfacing of Market Street', '12 June'],
            ['MSM/T/02/2026', 'Solar street lights, Beach ward', '19 June'],
            ['MSM/T/03/2026', 'Waste collection vehicles (2)', '26 June'],
        ]),
    ],
];

// The caption under each image and in the lightbox, by image. It goes on the reference
// as its description, like the alternative text.
$captions = [
    'revenue.svg' => 'Pay by mobile money: the receipt arrives on your phone.',
    'land-rates.svg' => 'Every plot in the valuation roll has a rateable value.',
    'business-permit.svg' => 'One permit a year covers trade, health and fire safety.',
    'parking.svg' => 'Parking on Beach Road is paid Monday to Saturday, 7:00 to 18:00.',
    'market.svg' => 'Stalls at the central market on Market Street.',
    'waiver.svg' => 'Clear your arrears before 30 June to have the interest waived.',
    'health.svg' => 'Mto Safi Town health centre, open Monday to Saturday.',
    'water.svg' => 'The river gives the town its name: Mto Safi means "clean river".',
    'tourism.svg' => 'A dhow leaves the old harbour at sunset.',
    'participation.svg' => 'Budget forum in the municipal hall, Central ward.',
    'office.svg' => 'The municipal office on Beach Road.',
    'portal.svg' => 'Log in with your eCitizen account.',
    'accessibility.svg' => 'Example image: replace it together with the text.',
    'privacy.svg' => 'Example image: replace it together with the text.',
    'documents.svg' => 'Requests are answered within 21 days.',
    'tenders.svg' => 'Bids go in a sealed envelope to the procurement office.',
];

$pageUids = [];
foreach (array_keys($content) as $title) {
    $pageUids[$title] = pageByTitle($connectionPool, $title);
}
$filled = $connectionPool->getConnectionForTable('tt_content')->select(
    ['pid'],
    'tt_content',
    ['deleted' => 0]
)->fetchFirstColumn();
$alreadyFilled = array_keys(array_intersect($pageUids, array_map('intval', $filled)));
if ($alreadyFilled !== []) {
    fwrite(STDERR, 'These pages have content already: ' . implode(', ', $alreadyFilled) . "; restore the snapshot before seeding again.\n");
    exit(1);
}

$storage = GeneralUtility::makeInstance(StorageRepository::class)->getDefaultStorage();
$folder = $storage->hasFolder(IMAGE_FOLDER) ? $storage->getFolder(IMAGE_FOLDER) : $storage->createFolder(IMAGE_FOLDER);

function fileUid(ResourceStorage $storage, Folder $folder, string $image): int
{
    if ($folder->hasFile($image)) {
        return $storage->getFileInFolder($image, $folder)->getUid();
    }
    // addFile() sanitizes an SVG in place, so it gets a copy rather than the source.
    $copy = GeneralUtility::tempnam('page', '.svg');
    copy(__DIR__ . '/images/' . $image, $copy);
    return $storage->addFile($copy, $folder, $image, DuplicationBehavior::CANCEL)->getUid();
}

$data = ['sys_file_reference' => [], 'tt_content' => []];
$index = 0;
foreach ($content as $title => $elements) {
    $pid = $pageUids[$title];
    $previous = null;
    foreach ($elements as $element) {
        $id = 'NEWcontent' . $index++;
        if (isset($element['image'])) {
            $references = [];
            foreach ($element['image'] as $number => $imageAndTexts) {
                [$image, $alternative] = $imageAndTexts;
                // A NEW id carries no underscore, so the number is appended as it is.
                $references[] = $referenceId = $id . 'Image' . $number;
                $data['sys_file_reference'][$referenceId] = [
                    'pid' => $pid,
                    'uid_local' => fileUid($storage, $folder, $image),
                    'alternative' => $alternative,
                    'description' => $imageAndTexts[2] ?? $captions[$image],
                    'hidden' => 0,
                ];
            }
            $element['image'] = implode(',', $references);
            // "Enlarge on click": the images open full size in the site's lightbox.
            $element['image_zoom'] = 1;
        }
        // A negative pid puts the element after the one before it.
        $data['tt_content'][$id] = array_merge(
            ['pid' => $previous === null ? $pid : '-' . $previous, 'colPos' => COLPOS_MAIN, 'hidden' => 0],
            $element
        );
        $previous = $id;
    }
}

$dataHandler = GeneralUtility::makeInstance(DataHandler::class);
$dataHandler->start($data, []);
$dataHandler->process_datamap();

if ($dataHandler->errorLog !== []) {
    fwrite(STDERR, implode("\n", $dataHandler->errorLog) . "\n");
    exit(1);
}

foreach ($dataHandler->substNEWwithIDs as $newId => $id) {
    echo str_pad((string)$id, 5, ' ', STR_PAD_LEFT) . '  ' . $newId . "\n";
}
