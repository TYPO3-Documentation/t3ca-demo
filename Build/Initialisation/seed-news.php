<?php

declare(strict_types=1);

/**
 * Seeds the news of the Mto Safi site on top of the page tree seed-page-tree.php wrote:
 * the news list page and its detail page, the folder the news records live in, the
 * categories and news from design/docs/news-and-events.docx, and the three news plugins
 * (home page, list page, detail page).
 *
 * Run it after seed-page-tree.php; the result is exported to
 * packages/mtosafi_sitepackage/Initialisation/ (see the README there).
 *
 *     ddev exec php Build/Initialisation/seed-news.php
 *
 * It refuses to run where the news folder exists already.
 *
 * The news texts are the document's, with its spelling mistakes corrected and the date
 * "5 September 2062" read as 2026, where the document's order puts it. The last entry,
 * "Youth bursaries: applications open", breaks off in the middle of a sentence and is
 * left out until the Communications Office supplies the rest.
 */

use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

const ROOT_PAGE = 1;
const DOKTYPE_PAGE = 1;
const DOKTYPE_FOLDER = 254;
const NEWS_FOLDER_TITLE = 'News records';

$classLoader = require __DIR__ . '/../../vendor/autoload.php';
SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init($classLoader);
Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
Bootstrap::initializeBackendAuthentication();
$GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)
    ->createFromUserPreferences($GLOBALS['BE_USER']);

$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

/**
 * The uid of the page below the root page with this title, which seed-page-tree.php wrote.
 */
function pageBelowRoot(ConnectionPool $connectionPool, string $title): int
{
    $uid = $connectionPool->getConnectionForTable('pages')
        ->select(['uid'], 'pages', ['pid' => ROOT_PAGE, 'title' => $title, 'deleted' => 0, 'sys_language_uid' => 0])
        ->fetchOne();
    if ($uid === false) {
        fwrite(STDERR, "No page \"$title\" below page " . ROOT_PAGE . "; run seed-page-tree.php first.\n");
        exit(1);
    }
    return (int)$uid;
}

function process(array $data, array $commands = []): DataHandler
{
    $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
    $dataHandler->start($data, $commands);
    $dataHandler->process_datamap();
    $dataHandler->process_cmdmap();
    if ($dataHandler->errorLog !== []) {
        fwrite(STDERR, implode("\n", $dataHandler->errorLog) . "\n");
        exit(1);
    }
    return $dataHandler;
}

/**
 * A FlexForm value as DataHandler takes it: sheet => field => value.
 */
function flexForm(array $sheets): array
{
    $data = [];
    foreach ($sheets as $sheet => $fields) {
        foreach ($fields as $field => $value) {
            $data['data'][$sheet]['lDEF'][$field]['vDEF'] = (string)$value;
        }
    }
    return $data;
}

function newsDate(string $date): int
{
    return (new DateTimeImmutable($date . ' 08:00', new DateTimeZone('Africa/Nairobi')))->getTimestamp();
}

/**
 * Body text as the text editor writes it: one paragraph per entry, the contact last.
 */
function bodytext(array $paragraphs, string $contact): string
{
    $html = '';
    foreach ($paragraphs as $paragraph) {
        $html .= '<p>' . htmlspecialchars($paragraph) . "</p>\n";
    }
    return $html . '<p><strong>Contact:</strong> ' . htmlspecialchars($contact) . '</p>';
}

if ($connectionPool->getConnectionForTable('pages')->count('uid', 'pages', ['title' => NEWS_FOLDER_TITLE, 'deleted' => 0]) > 0) {
    fwrite(STDERR, "The folder \"" . NEWS_FOLDER_TITLE . "\" exists already; restore the snapshot before seeding again.\n");
    exit(1);
}

$tenders = pageBelowRoot($connectionPool, 'Tenders');
$footerMenu = pageBelowRoot($connectionPool, 'Footer navigation');

// Pages first: the plugins below name them in FlexForm fields, which take no NEW ids.
$pages = process(['pages' => [
    'NEWnewsPage' => [
        'pid' => '-' . $tenders, 'title' => 'News', 'doktype' => DOKTYPE_PAGE, 'hidden' => 0, 'nav_hide' => 1,
    ],
    'NEWarticlePage' => [
        'pid' => 'NEWnewsPage', 'title' => 'Article', 'doktype' => DOKTYPE_PAGE, 'hidden' => 0, 'nav_hide' => 1,
        'backend_layout' => 'pagets__Article',
    ],
    'NEWnewsFolder' => [
        'pid' => '-' . $footerMenu, 'title' => NEWS_FOLDER_TITLE, 'doktype' => DOKTYPE_FOLDER, 'hidden' => 0,
    ],
]]);
$newsPage = (int)$pages->substNEWwithIDs['NEWnewsPage'];
$articlePage = (int)$pages->substNEWwithIDs['NEWarticlePage'];
$newsFolder = (int)$pages->substNEWwithIDs['NEWnewsFolder'];

$categoryTitles = [
    'NEWcatTrade' => 'Trade & markets',
    'NEWcatRoads' => 'Roads & transport',
    'NEWcatHealth' => 'Health',
    'NEWcatRevenue' => 'Revenue & payments',
    'NEWcatParticipation' => 'Public participation',
    'NEWcatEnvironment' => 'Environment',
    'NEWcatWater' => 'Water',
    'NEWcatEducation' => 'Education',
];
$categories = [];
$previous = null;
foreach ($categoryTitles as $id => $title) {
    $categories[$id] = ['pid' => $previous === null ? $newsFolder : '-' . $previous, 'title' => $title, 'hidden' => 0];
    $previous = $id;
}

$news = [
    'NEWnewsFishMarket' => [
        'title' => 'New fish market opens at the jetty',
        'datetime' => newsDate('2026-11-02'),
        'categories' => 'NEWcatTrade',
        'teaser' => 'Sixty stalls, cold storage and clean water for traders and buyers.',
        'bodytext' => bodytext([
            'The new fish market at the jetty opened on Monday. It has sixty stalls, a cold room for the night’s catch and running water at every stall.',
            'Traders who sold at the old market keep their stall number. New traders apply for a stall at the trade office in the municipal office. The market opens daily from 5:00 to 18:00.',
        ], 'Trade & Markets Office, +254 700 000 010'),
    ],
    'NEWnewsBeachRoad' => [
        'title' => 'Beach Road resurfacing starts',
        'datetime' => newsDate('2026-10-27'),
        'categories' => 'NEWcatRoads',
        'teaser' => 'Works run for six weeks. Matatus take the diversion via Market Street.',
        'bodytext' => bodytext([
            'Beach Road between the roundabout and the public beach gets a new surface and drainage. The works start on 3 November and run for about six weeks.',
            'During the works, matatus and lorries take the diversion via Market Street. Pedestrians and bicycles can pass at all times. Shops along Beach Road stay open.',
        ], 'Roads Department, +254 700 000 020'),
    ],
    'NEWnewsHealthCamp' => [
        'title' => 'Free health camp in Ward 3',
        'datetime' => newsDate('2026-10-20'),
        'categories' => 'NEWcatHealth',
        'teaser' => 'Eye tests, vaccinations and screening, open to every resident.',
        'bodytext' => bodytext([
            'The municipality and the Ward 3 health centre hold a free health camp on 25 and 26 October at Ward 3 Primary School, from 8:00 to 16:00.',
            'On offer: eye tests, blood pressure and diabetes screening, and vaccinations for children. Bring your health card if you have one. No appointment is needed.',
        ], 'Health Department, +254 700 000 030'),
    ],
    'NEWnewsMobileMoney' => [
        'title' => 'Pay land rates by mobile money from January',
        'datetime' => newsDate('2026-10-12'),
        'categories' => 'NEWcatRevenue',
        'teaser' => 'Residents can now pay land rates and business permits with their phone.',
        'bodytext' => bodytext([
            'From 1 January 2027, residents pay land rates, the single business permit and market fees by mobile money. The receipt arrives by SMS and counts as proof of payment.',
            'Payment at the cash office in the municipal office stays possible. The land rates calculator on our website shows what you owe before you pay.',
        ], 'Revenue Department, +254 700 000 040'),
    ],
    'NEWnewsBudget' => [
        'title' => 'Have your say on the budget 2027/28',
        'datetime' => newsDate('2026-10-01'),
        'categories' => 'NEWcatParticipation',
        'teaser' => 'Public participation forums in every ward in November.',
        'bodytext' => bodytext([
            'The municipality prepares its budget for 2027/28 and asks residents what matters most to them: roads, water, markets, health or schools.',
            'A forum takes place in every ward in November. The main forum is on 22 November in the municipal hall. Residents who cannot attend send their views in writing to the municipal office by 30 November.',
        ], 'Planning Office, +254 700 000 050'),
    ],
    'NEWnewsCleanestTown' => [
        'title' => 'Mto Safi named cleanest town on the coast',
        'datetime' => newsDate('2026-09-18'),
        'categories' => 'NEWcatEnvironment',
        'teaser' => 'The monthly beach clean-ups and the new waste collection paid off.',
        'bodytext' => bodytext([
            'Mto Safi has won this year’s Clean Coast Town Award. The jury praised the monthly beach clean-ups by residents and schools, and the new waste collection in all wards.',
            'The mayor thanked every volunteer and announced that the clean-ups continue every third Saturday of the month.',
        ], 'Environment Department, +254 700 000 060'),
    ],
    'NEWnewsBoreholes' => [
        'title' => 'New boreholes bring clean water to Ward 5',
        'datetime' => newsDate('2026-09-05'),
        'categories' => 'NEWcatWater',
        'teaser' => 'Three boreholes and twelve water kiosks serve 4,000 residents.',
        'bodytext' => bodytext([
            'Three new boreholes in Ward 5 now supply twelve water kiosks. About 4,000 residents no longer have to walk to the river for water.',
            'A water kiosk is open from 6:00 to 19:00. A 20-litre container costs KES 5.',
        ], 'Water & Sanitation Department, +254 700 000 070'),
    ],
];
// Newest first in the document; created in that order, each after the one before it.
$previous = null;
foreach ($news as $id => $record) {
    $news[$id] = ['pid' => $previous === null ? $newsFolder : '-' . $previous, 'hidden' => 0] + $record;
    $previous = $id;
}

$listSettings = [
    'settings.orderBy' => 'datetime',
    'settings.orderDirection' => 'desc',
    'settings.startingpoint' => $newsFolder,
    'settings.recursive' => 0,
];

// sys_category before the news: the news name the categories by NEW id.
process([
    'sys_category' => $categories,
    'tx_news_domain_model_news' => $news,
    'tt_content' => [
        'NEWhomeNews' => [
            'pid' => ROOT_PAGE,
            'CType' => 'news_newsliststicky',
            'colPos' => 0,
            'header' => 'News',
            // The template prints the header as the section heading, beside the link to all news.
            'header_layout' => 100,
            'hidden' => 0,
            'pi_flexform' => flexForm([
                'sDEF' => $listSettings,
                'additional' => [
                    'settings.detailPid' => $articlePage,
                    'settings.listPid' => $newsPage,
                    'settings.limit' => 3,
                    'settings.hidePagination' => 1,
                ],
                'template' => ['settings.templateLayout' => 'home'],
            ]),
        ],
        'NEWnewsList' => [
            'pid' => $newsPage,
            'CType' => 'news_newsliststicky',
            'colPos' => 0,
            'header' => 'All news',
            'header_layout' => 100,
            'hidden' => 0,
            'pi_flexform' => flexForm([
                'sDEF' => $listSettings,
                'additional' => ['settings.detailPid' => $articlePage],
            ]),
        ],
        'NEWnewsDetail' => [
            'pid' => $articlePage,
            'CType' => 'news_newsdetail',
            'colPos' => 0,
            'header' => 'News article',
            'header_layout' => 100,
            'hidden' => 0,
            'pi_flexform' => flexForm([
                'sDEF' => ['settings.startingpoint' => $newsFolder, 'settings.recursive' => 0],
                'additional' => ['settings.listPid' => $newsPage, 'settings.backPid' => $newsPage],
            ]),
        ],
    ],
]);

echo str_pad((string)$newsPage, 5, ' ', STR_PAD_LEFT) . "  News (list page)\n";
echo str_pad((string)$articlePage, 5, ' ', STR_PAD_LEFT) . "  Article (detail page)\n";
echo str_pad((string)$newsFolder, 5, ' ', STR_PAD_LEFT) . '  ' . NEWS_FOLDER_TITLE . "\n";
