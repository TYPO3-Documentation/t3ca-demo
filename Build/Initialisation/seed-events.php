<?php

declare(strict_types=1);

/**
 * Seeds the events of the Mto Safi site on top of what seed-news.php and seed-home.php
 * wrote: the events from design/docs/news-and-events.docx as news records that are
 * events (EXT:eventnews), in the "News records" folder; the "Events" page after the
 * "News" page with its "Event" detail page below it, as the news have them; and the
 * events plugins on the home page (below the news), the events page and the detail page.
 *
 * Run it after seed-home.php; the result is exported to
 * packages/mtosafi_sitepackage/Initialisation/ (see the README there).
 *
 *     ddev exec php Build/Initialisation/seed-events.php
 *
 * It refuses to run where an event exists already.
 *
 * An event starts and ends at the times the document gives, in the municipality's time
 * zone, Africa/Nairobi. The site shows only the days, so the times are in the text as
 * well, together with the cost and the registration, which have no field of their own.
 * The place is the event's location. Where the document names an office to register
 * or apply at, the registration button links to the page that helps most: the market
 * fees for the stalls of the festive market, the contact page for the job fair. The
 * document gives the offices no email address. "Past events" of the document is left out: the
 * site lists only the events still to come, and one of them, the health camp, is a news.
 */

use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

const ROOT_PAGE = 1;
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
 * The uid of the one record of $table that matches $where, which an earlier seed wrote.
 */
function existing(ConnectionPool $connectionPool, string $table, array $where, string $script): int
{
    $uid = $connectionPool->getConnectionForTable($table)
        ->select(['uid'], $table, $where + ['deleted' => 0])
        ->fetchOne();
    if ($uid === false) {
        fwrite(STDERR, "No $table record " . json_encode($where) . "; run $script first.\n");
        exit(1);
    }
    return (int)$uid;
}

function process(array $data): DataHandler
{
    $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
    $dataHandler->start($data, []);
    $dataHandler->process_datamap();
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

function eventTime(string $dateAndTime): int
{
    return (new DateTimeImmutable($dateAndTime, new DateTimeZone('Africa/Nairobi')))->getTimestamp();
}

/**
 * Body text as the text editor writes it: the description, then time, cost and registration.
 */
function bodytext(string $description, string $time, string $cost, string $registration): string
{
    return '<p>' . htmlspecialchars($description) . "</p>\n"
        . '<p><strong>Time:</strong> ' . htmlspecialchars($time)
        . '<br><strong>Cost:</strong> ' . htmlspecialchars($cost)
        . '<br><strong>Registration:</strong> ' . htmlspecialchars($registration) . '</p>';
}

if ($connectionPool->getConnectionForTable('tx_news_domain_model_news')->count('uid', 'tx_news_domain_model_news', ['is_event' => 1, 'deleted' => 0]) > 0) {
    fwrite(STDERR, "Events exist already; restore the snapshot before seeding again.\n");
    exit(1);
}

$newsFolder = existing($connectionPool, 'pages', ['title' => NEWS_FOLDER_TITLE, 'sys_language_uid' => 0], 'seed-news.php');
$newsPage = existing($connectionPool, 'pages', ['pid' => ROOT_PAGE, 'title' => 'News', 'sys_language_uid' => 0], 'seed-news.php');
$homeNews = existing($connectionPool, 'tt_content', ['pid' => ROOT_PAGE, 'CType' => 'news_newsliststicky', 'header' => 'News'], 'seed-news.php');
$contactPage = existing($connectionPool, 'pages', ['pid' => ROOT_PAGE, 'title' => 'Contact', 'sys_language_uid' => 0], 'seed-page-tree.php');
$marketFeesPage = existing($connectionPool, 'pages', ['title' => 'Market fees', 'sys_language_uid' => 0], 'seed-page-tree.php');
$category = static fn(string $title): int => existing($connectionPool, 'sys_category', ['title' => $title, 'sys_language_uid' => 0], 'seed-news.php');

$events = [
    'NEWeventBeachCleanUp' => [
        'title' => 'Beach clean-up day',
        'datetime' => eventTime('2026-11-15 07:00'),
        'event_end' => eventTime('2026-11-15 11:00'),
        'location_simple' => 'Public beach, meeting point at the lifeguard tower',
        'categories' => $category('Environment'),
        'teaser' => 'Meet at the lifeguard tower on the public beach, 7:00. Gloves, bags and water provided.',
        'bodytext' => bodytext(
            'Residents, schools and businesses clean the beach together. Gloves, bags and water are provided. The cleanest group wins a tree for its school.',
            '7:00–11:00',
            'Free',
            'Not needed'
        ),
    ],
    'NEWeventBudgetForum' => [
        'title' => 'Public participation forum: budget 2027/28',
        'datetime' => eventTime('2026-11-22 10:00'),
        'event_end' => eventTime('2026-11-22 13:00'),
        'location_simple' => 'Municipal hall, Beach Road 1',
        'categories' => $category('Public participation'),
        'teaser' => 'Municipal hall, 10:00. Every resident may speak.',
        'bodytext' => bodytext(
            'Every resident may speak. The forum is held in Kiswahili and English. Sign language interpretation is available on request until 15 November.',
            '10:00–13:00',
            'Free',
            'Not needed'
        ),
    ],
    'NEWeventJobFair' => [
        'title' => 'Youth job and skills fair',
        'datetime' => eventTime('2026-12-05 09:00'),
        'event_end' => eventTime('2026-12-05 15:00'),
        'location_simple' => 'Mto Safi Polytechnic',
        'categories' => $category('Education'),
        'teaser' => 'Mto Safi Polytechnic, 9:00–15:00. Bring copies of your CV and certificates.',
        'bodytext' => bodytext(
            'Employers from tourism, fishing, construction and IT present jobs and apprenticeships. Bring copies of your CV and certificates.',
            '9:00–15:00',
            'Free',
            'Recommended, at the Education Office'
        ),
        'tx_mtosafisitepackage_registration_link' => 't3://page?uid=' . $contactPage,
    ],
    'NEWeventJamhuriDay' => [
        'title' => 'Jamhuri Day celebrations',
        'datetime' => eventTime('2026-12-12 09:00'),
        'event_end' => eventTime('2026-12-12 14:00'),
        'location_simple' => 'Municipal grounds',
        'teaser' => 'Municipal grounds, 9:00–14:00. Flag raising, speeches, school choirs and traditional dances.',
        'bodytext' => bodytext(
            'The municipality celebrates Jamhuri Day with the flag raising, speeches, school choirs and traditional dances.',
            '9:00–14:00',
            'Free',
            'Not needed'
        ),
    ],
    'NEWeventFestiveMarket' => [
        'title' => 'Festive market at the jetty',
        'datetime' => eventTime('2026-12-19 16:00'),
        'event_end' => eventTime('2026-12-24 22:00'),
        'location_simple' => 'Jetty and fish market',
        'categories' => $category('Trade & markets'),
        'teaser' => 'Every evening from 16:00 to 22:00 at the jetty: food, crafts and music.',
        'bodytext' => bodytext(
            'Food, crafts and music every evening before Christmas.',
            'Every evening, 16:00–22:00',
            'Free entry',
            'Traders apply for a stall at the Trade & Markets Office until 1 December'
        ),
        'tx_mtosafisitepackage_registration_link' => 't3://page?uid=' . $marketFeesPage,
        'tx_mtosafisitepackage_registration_label' => 'Apply for a stall',
    ],
    'NEWeventLandRatesHelpDesk' => [
        'title' => 'Land rates help desk',
        'datetime' => eventTime('2027-01-18 09:00'),
        'event_end' => eventTime('2027-03-31 15:00'),
        'location_simple' => 'Municipal office, cash office',
        'categories' => $category('Revenue & payments'),
        'teaser' => 'Monday to Friday, 9:00–15:00, at the cash office: help with your valuation notice and land rates.',
        'bodytext' => bodytext(
            'Staff of the Revenue Department help you read your valuation notice, calculate your land rates and set up payment by mobile money.',
            'Monday to Friday, 9:00–15:00',
            'Free',
            'Not needed'
        ),
    ],
];
// In the order of the document, each after the one before it.
$previous = null;
foreach ($events as $id => $record) {
    $events[$id] = ['pid' => $previous === null ? $newsFolder : '-' . $previous, 'hidden' => 0, 'is_event' => 1] + $record;
    $previous = $id;
}

// Pages first: the plugins below name them in FlexForm fields, which take no NEW ids.
$pages = process(['pages' => [
    'NEWeventsPage' => [
        'pid' => '-' . $newsPage, 'title' => 'Events', 'doktype' => 1, 'hidden' => 0, 'nav_hide' => 1,
    ],
    'NEWeventPage' => [
        'pid' => 'NEWeventsPage', 'title' => 'Event', 'doktype' => 1, 'hidden' => 0, 'nav_hide' => 1,
        'backend_layout' => 'pagets__Article',
        // Like "Article": without an event it has nothing to find, the event itself is
        // indexed by the news indexer.
        'no_search' => 1,
    ],
]]);
$eventsPage = (int)$pages->substNEWwithIDs['NEWeventsPage'];
$eventPage = (int)$pages->substNEWwithIDs['NEWeventPage'];

// Events only, still to come, the next first. With events only, EXT:eventnews keeps an
// event while it has not ended, so a running event stays in the list until its last day.
$upcomingEvents = [
    'settings.orderBy' => 'datetime',
    'settings.orderDirection' => 'asc',
    'settings.startingpoint' => $newsFolder,
    'settings.recursive' => 0,
    'settings.timeRestriction' => 'today',
];
// 1: events only. Every other news plugin lists news only.
$eventsOnly = ['settings.eventRestriction' => 1];

process([
    'tx_news_domain_model_news' => $events,
    'tt_content' => [
        'NEWhomeEvents' => [
            // Below the news on the home page.
            'pid' => '-' . $homeNews,
            'CType' => 'news_newsliststicky',
            'colPos' => 0,
            'header' => 'Events',
            // The template prints the header as the section heading.
            'header_layout' => 100,
            'hidden' => 0,
            'pi_flexform' => flexForm([
                'sDEF' => $upcomingEvents,
                'additional' => [
                    'settings.detailPid' => $eventPage,
                    'settings.listPid' => $eventsPage,
                    'settings.limit' => 3,
                    'settings.hidePagination' => 1,
                ],
                'template' => ['settings.templateLayout' => 'events'],
                'extraEntryEventNews' => $eventsOnly,
            ]),
        ],
        'NEWeventsList' => [
            'pid' => $eventsPage,
            'CType' => 'news_newsliststicky',
            'colPos' => 0,
            'header' => 'Upcoming events',
            'header_layout' => 100,
            'hidden' => 0,
            'pi_flexform' => flexForm([
                'sDEF' => $upcomingEvents,
                // Every upcoming event on one page: there are few at a time.
                'additional' => ['settings.detailPid' => $eventPage, 'settings.hidePagination' => 1],
                'template' => ['settings.templateLayout' => 'eventsPage'],
                'extraEntryEventNews' => $eventsOnly,
            ]),
        ],
        'NEWeventDetail' => [
            'pid' => $eventPage,
            'CType' => 'news_newsdetail',
            'colPos' => 0,
            'header' => 'Event',
            'header_layout' => 100,
            'hidden' => 0,
            'pi_flexform' => flexForm([
                'sDEF' => ['settings.startingpoint' => $newsFolder, 'settings.recursive' => 0],
                'additional' => ['settings.listPid' => $eventsPage, 'settings.backPid' => $eventsPage],
            ]),
        ],
    ],
]);

echo str_pad((string)$eventsPage, 5, ' ', STR_PAD_LEFT) . "  Events (list page)\n";
echo str_pad((string)$eventPage, 5, ' ', STR_PAD_LEFT) . "  Event (detail page)\n";
echo count($events) . " events in \"" . NEWS_FOLDER_TITLE . "\", events plugins on the home page, the events page and the detail page\n";
