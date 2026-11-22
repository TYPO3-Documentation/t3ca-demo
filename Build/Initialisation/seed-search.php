<?php

declare(strict_types=1);

/**
 * Seeds the search of the Mto Safi site (EXT:ke_search) on top of what the other seed
 * scripts wrote: the folder that holds the index, the "Show only" filter with one option
 * per result type, an indexer for the pages and one for the news, and the search box and
 * result list on the "Search" page. Files are not indexed.
 *
 * Run it after seed-not-found.php; the result is exported to
 * packages/mtosafi_sitepackage/Initialisation/ (see the README there).
 *
 *     ddev exec php Build/Initialisation/seed-search.php
 *
 * It refuses to run where the folder exists already.
 *
 * The type keys "service" and "news" are the tags of the filter options. The listener
 * Mtosafi\Sitepackage\Search\TagIndexEntryWithResultType tags the index entries with them,
 * and the result templates map the index type "page" to "service", so badge, filter and
 * tag agree.
 */

use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

const ROOT_PAGE = 1;
const DOKTYPE_FOLDER = 254;
const SEARCH_FOLDER_TITLE = 'Search index';
const COLPOS_MAIN = 0;
const COLPOS_SIDEBAR = 1;

$classLoader = require __DIR__ . '/../../vendor/autoload.php';
SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init($classLoader);
Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
Bootstrap::initializeBackendAuthentication();
$GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)
    ->createFromUserPreferences($GLOBALS['BE_USER']);

$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

/**
 * The uid of the page with this title below the given page, which the other seed scripts wrote.
 */
function pageBelow(ConnectionPool $connectionPool, int $pid, string $title): int
{
    $uid = $connectionPool->getConnectionForTable('pages')
        ->select(['uid'], 'pages', ['pid' => $pid, 'title' => $title, 'deleted' => 0, 'sys_language_uid' => 0])
        ->fetchOne();
    if ($uid === false) {
        fwrite(STDERR, "No page \"$title\" below page $pid; run the other seed scripts first.\n");
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

$existing = $connectionPool->getConnectionForTable('pages')
    ->select(['uid'], 'pages', ['pid' => ROOT_PAGE, 'title' => SEARCH_FOLDER_TITLE, 'deleted' => 0])
    ->fetchOne();
if ($existing !== false) {
    fwrite(STDERR, "Folder \"" . SEARCH_FOLDER_TITLE . "\" exists already; restore the snapshot before seeding again.\n");
    exit(1);
}

$searchPage = pageBelow($connectionPool, ROOT_PAGE, 'Search');
$notFoundPage = pageBelow($connectionPool, ROOT_PAGE, 'Page not found');
$newsPage = pageBelow($connectionPool, ROOT_PAGE, 'News');
$articlePage = pageBelow($connectionPool, $newsPage, 'Article');
$newsFolder = pageBelow($connectionPool, ROOT_PAGE, 'News records');

// The folder, the filter with its options, and the pages that stay out of the index.
$dataHandler = process([
    'pages' => [
        'NEWsearchFolder' => [
            'pid' => '-' . $newsFolder,
            'title' => SEARCH_FOLDER_TITLE,
            'doktype' => DOKTYPE_FOLDER,
            'hidden' => 0,
        ],
        // The search itself, the 404 page and the empty news detail page are no results.
        $searchPage => ['no_search' => 1],
        $notFoundPage => ['no_search' => 1],
        $articlePage => ['no_search' => 1],
    ],
    'tx_kesearch_filteroptions' => [
        'NEWoptionService' => ['pid' => 'NEWsearchFolder', 'title' => 'Services', 'tag' => 'service'],
        // After "Services", in the design's order.
        'NEWoptionNews' => ['pid' => '-NEWoptionService', 'title' => 'News', 'tag' => 'news'],
    ],
    'tx_kesearch_filters' => [
        'NEWfilterType' => [
            'pid' => 'NEWsearchFolder',
            'title' => 'Show only',
            'rendertype' => 'checkbox',
            // The order of the options, not their titles; TCA defaults this to 1.
            'alphabeticalsorting' => 0,
            'options' => 'NEWoptionService,NEWoptionNews',
        ],
    ],
]);
$searchFolder = (int)$dataHandler->substNEWwithIDs['NEWsearchFolder'];
$optionService = (int)$dataHandler->substNEWwithIDs['NEWoptionService'];
$optionNews = (int)$dataHandler->substNEWwithIDs['NEWoptionNews'];
$filterType = (int)$dataHandler->substNEWwithIDs['NEWfilterType'];

// The indexers set no tag: Mtosafi\Sitepackage\Search\TagIndexEntryWithResultType tags
// by type, because "filteroption" holds a uid the import does not keep. The plugin's
// "filters" holds one as well; see the README.
$dataHandler = process([
    'tx_kesearch_indexerconfig' => [
        'NEWindexerPages' => [
            'pid' => $searchFolder,
            'title' => 'Pages',
            'type' => 'page',
            'storagepid' => $searchFolder,
            'startingpoints_recursive' => ROOT_PAGE,
        ],
        'NEWindexerNews' => [
            'pid' => $searchFolder,
            'title' => 'News',
            'type' => 'news',
            'storagepid' => $searchFolder,
            'targetpid' => $articlePage,
            'startingpoints_recursive' => $newsFolder,
        ],
    ],
    'tt_content' => [
        // The search box carries the configuration; on this site it renders the filter only,
        // the search field is the one in the page header.
        'NEWsearchBox' => [
            'pid' => $searchPage,
            'CType' => 'ke_search_pi1',
            'colPos' => COLPOS_SIDEBAR,
            'header' => 'Filter results',
            'header_layout' => 100,
            'pages' => $searchFolder,
            'pi_flexform' => flexForm([
                'MAIN' => [
                    'resultPage' => $searchPage,
                    'showTextInsteadOfResults' => 1,
                    'textForResults' => '<p>Type what you are looking for into the search field at the top of the page.</p>',
                    'showNoResultsText' => 0,
                ],
                'view' => [
                    'highlightSword' => 1,
                    'resultChars' => 200,
                    'resultsNumeration' => 0,
                    'showResultUrl' => 0,
                    'showDate' => 0,
                ],
                'images' => [
                    'showTypeIcon' => 0,
                    'showPageImages' => 0,
                    'showNewsImages' => 0,
                    'showFilePreview' => 0,
                ],
                'pagebrowser' => [
                    'pagebrowserOnTop' => 0,
                    'pagebrowserAtBottom' => 1,
                    'resultsPerPage' => 10,
                    'maxPagesInPagebrowser' => 10,
                ],
                'filter' => [
                    'filters' => $filterType,
                ],
            ]),
        ],
    ],
]);
$searchBox = (int)$dataHandler->substNEWwithIDs['NEWsearchBox'];

$dataHandler = process([
    'tt_content' => [
        'NEWresultList' => [
            'pid' => $searchPage,
            'CType' => 'ke_search_pi2',
            'colPos' => COLPOS_MAIN,
            'header' => 'Search results',
            'header_layout' => 100,
            'pi_flexform' => flexForm([
                'MAIN' => ['loadFlexformsFromOtherCE' => $searchBox],
            ]),
        ],
    ],
]);

$ids = [
    'search folder' => $searchFolder,
    'filter option "service"' => $optionService,
    'filter option "news"' => $optionNews,
    'filter "Show only"' => $filterType,
    'search box' => $searchBox,
    'result list' => (int)$dataHandler->substNEWwithIDs['NEWresultList'],
];
foreach ($ids as $label => $id) {
    echo str_pad((string)$id, 5, ' ', STR_PAD_LEFT) . '  ' . $label . "\n";
}
