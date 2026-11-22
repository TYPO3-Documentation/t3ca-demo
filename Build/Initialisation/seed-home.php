<?php

declare(strict_types=1);

/**
 * Seeds the slider and the "Popular services" tiles of the home page
 * (design/index.html) on top of the page tree seed-page-tree.php wrote. Both go above
 * the news seed-news.php put there, the slider first.
 *
 * Run it after seed-page-tree.php; the result is exported to
 * packages/mtosafi_sitepackage/Initialisation/ (see the README there).
 *
 *     ddev exec php Build/Initialisation/seed-home.php
 *
 * An element the home page has already is left alone, so the script seeds what is
 * missing; it refuses to run where nothing is.
 *
 * The slider images are copied from design/images/ to fileadmin/user_upload/slides/.
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
use TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

const ROOT_PAGE = 1;
const SLIDE_FOLDER = '/user_upload/slides/';

$classLoader = require __DIR__ . '/../../vendor/autoload.php';
SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init($classLoader);
Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
Bootstrap::initializeBackendAuthentication();
$GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)
    ->createFromUserPreferences($GLOBALS['BE_USER']);

$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

function homeHas(ConnectionPool $connectionPool, string $cType): bool
{
    return $connectionPool->getConnectionForTable('tt_content')
        ->count('uid', 'tt_content', ['pid' => ROOT_PAGE, 'CType' => $cType, 'deleted' => 0]) > 0;
}

$seedTiles = !homeHas($connectionPool, 'mtosafi_tiles');
$seedSlider = !homeHas($connectionPool, 'mtosafi_slider');
if (!$seedTiles && !$seedSlider) {
    fwrite(STDERR, "The home page has its slider and its service tiles already; restore the snapshot before seeding again.\n");
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

$data = [];

$tileRecords = [];
foreach ($seedTiles ? $tiles : [] as $index => [$icon, $label, $page]) {
    $tileRecords['NEWtile' . $index] = [
        'pid' => ROOT_PAGE,
        'icon' => $icon,
        'label' => $label,
        'link' => 't3://page?uid=' . pageByTitle($connectionPool, $page),
        'hidden' => 0,
    ];
}

if ($seedTiles) {
    // A positive pid puts the element first on the page.
    $data['tt_content']['NEWserviceTiles'] = [
        'pid' => ROOT_PAGE,
        'CType' => 'mtosafi_tiles',
        'colPos' => 0,
        'header' => 'Popular services',
        'hidden' => 0,
        'tx_mtosafisitepackage_tiles' => implode(',', array_keys($tileRecords)),
    ];
    $data['tx_mtosafisitepackage_tile'] = $tileRecords;
}

// Image, heading, text, button text and the page the button links to, in the order
// of the design.
$slides = [
    ['slide-coast.svg', 'Karibu Mto Safi', 'White beaches, a clean river and a municipality that serves you online, day and night.', 'Discover Mto Safi', 'Trade & tourism'],
    ['slide-rates.svg', 'Pay your land rates by mobile money', 'Calculate what you owe and pay before 31 March to avoid interest.', 'Calculate land rates', 'Land rates'],
    ['slide-community.svg', 'Have your say on the budget', 'Join the public participation forums in every ward this month.', 'See the forum dates', 'Public participation'],
];

if ($seedSlider) {
    $storage = GeneralUtility::makeInstance(StorageRepository::class)->getDefaultStorage();
    $folder = $storage->hasFolder(SLIDE_FOLDER) ? $storage->getFolder(SLIDE_FOLDER) : $storage->createFolder(SLIDE_FOLDER);
    $slideRecords = [];
    foreach ($slides as $index => [$image, $title, $text, $linkLabel, $page]) {
        if ($folder->hasFile($image)) {
            $file = $storage->getFileInFolder($image, $folder);
        } else {
            // addFile() sanitizes an SVG in place, so it gets a copy rather than the
            // design's own file.
            $copy = GeneralUtility::tempnam('slide', '.svg');
            copy(__DIR__ . '/../../design/images/' . $image, $copy);
            $file = $storage->addFile($copy, $folder, $image, DuplicationBehavior::CANCEL);
        }
        $data['sys_file_reference']['NEWslideImage' . $index] = [
            'pid' => ROOT_PAGE,
            'uid_local' => $file->getUid(),
            'hidden' => 0,
        ];
        $slideRecords['NEWslide' . $index] = [
            'pid' => ROOT_PAGE,
            'image' => 'NEWslideImage' . $index,
            'title' => $title,
            'text' => $text,
            'link' => 't3://page?uid=' . pageByTitle($connectionPool, $page),
            'link_label' => $linkLabel,
            'hidden' => 0,
        ];
    }
    // Created after the tiles, with a positive pid as well, so it ends up above them.
    $data['tt_content']['NEWslider'] = [
        'pid' => ROOT_PAGE,
        'CType' => 'mtosafi_slider',
        'colPos' => 0,
        'header' => 'Highlights',
        'hidden' => 0,
        'tx_mtosafisitepackage_slides' => implode(',', array_keys($slideRecords)),
    ];
    $data['tx_mtosafisitepackage_slide'] = $slideRecords;
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
