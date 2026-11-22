<?php

declare(strict_types=1);

/**
 * Seeds the "Popular services" tiles of the home page (design/index.html) on top of
 * the page tree seed-page-tree.php wrote. The element goes first on the home page,
 * above the news seed-news.php put there.
 *
 * Run it after seed-page-tree.php; the result is exported to
 * packages/mtosafi_sitepackage/Initialisation/ (see the README there).
 *
 *     ddev exec php Build/Initialisation/seed-home.php
 *
 * It refuses to run where the home page has a "Service tiles" element already.
 *
 * Three tiles of the design name services the page tree has no page of their own for
 * yet. They link to the page that covers them for now: "Water connection" to
 * "Environment & water", "Market stalls" to "Market fees" and "Report a problem" to
 * "Contact".
 */

use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

const ROOT_PAGE = 1;

$classLoader = require __DIR__ . '/../../vendor/autoload.php';
SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init($classLoader);
Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
Bootstrap::initializeBackendAuthentication();
$GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)
    ->createFromUserPreferences($GLOBALS['BE_USER']);

$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

$existing = $connectionPool->getConnectionForTable('tt_content')
    ->count('uid', 'tt_content', ['pid' => ROOT_PAGE, 'CType' => 'mtosafi_tiles', 'deleted' => 0]);
if ($existing > 0) {
    fwrite(STDERR, "The home page has service tiles already; restore the snapshot before seeding again.\n");
    exit(1);
}

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

// Icon, label and the page the tile links to, in the order of the design.
$tiles = [
    ['🏪', 'Single business permit', 'Single business permit'],
    ['🏡', 'Land rates', 'Land rates'],
    ['🅿️', 'Parking fees', 'Parking fees'],
    ['💧', 'Water connection', 'Environment & water'],
    ['🧺', 'Market stalls', 'Market fees'],
    ['📣', 'Report a problem', 'Contact'],
];

$tileRecords = [];
foreach ($tiles as $index => [$icon, $label, $page]) {
    $tileRecords['NEWtile' . $index] = [
        'pid' => ROOT_PAGE,
        'icon' => $icon,
        'label' => $label,
        'link' => 't3://page?uid=' . pageByTitle($connectionPool, $page),
        'hidden' => 0,
    ];
}

$dataHandler = GeneralUtility::makeInstance(DataHandler::class);
$dataHandler->start([
    'tt_content' => [
        // A positive pid puts the element first on the page.
        'NEWserviceTiles' => [
            'pid' => ROOT_PAGE,
            'CType' => 'mtosafi_tiles',
            'colPos' => 0,
            'header' => 'Popular services',
            'hidden' => 0,
            'tx_mtosafisitepackage_tiles' => implode(',', array_keys($tileRecords)),
        ],
    ],
    'tx_mtosafisitepackage_tile' => $tileRecords,
], []);
$dataHandler->process_datamap();

if ($dataHandler->errorLog !== []) {
    fwrite(STDERR, implode("\n", $dataHandler->errorLog) . "\n");
    exit(1);
}

foreach ($dataHandler->substNEWwithIDs as $newId => $id) {
    echo str_pad((string)$id, 5, ' ', STR_PAD_LEFT) . '  ' . $newId . "\n";
}
