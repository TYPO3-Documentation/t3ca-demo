<?php

declare(strict_types=1);

/**
 * Seeds the page tree of the Mto Safi design (design/README.md) below the existing
 * root page and points the site settings at its menu folders and search page.
 *
 * The result is exported to packages/mtosafi_sitepackage/Initialisation/ afterwards;
 * correct this script rather than the records, it is what the next export comes from.
 *
 *     ddev exec php Build/Initialisation/seed-page-tree.php
 *
 * It refuses to run where the root page already has subpages.
 */

use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

const ROOT_PAGE = 1;
const SITE_IDENTIFIER = 'main';

const DOKTYPE_PAGE = 1;
const DOKTYPE_SHORTCUT = 4;
const DOKTYPE_FOLDER = 254;
const SHORTCUT_MODE_SELECTED_PAGE = 0;

$classLoader = require __DIR__ . '/../../vendor/autoload.php';
SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init($classLoader);
Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
Bootstrap::initializeBackendAuthentication();
$GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)
    ->createFromUserPreferences($GLOBALS['BE_USER']);

$subpages = GeneralUtility::makeInstance(ConnectionPool::class)
    ->getConnectionForTable('pages')
    ->count('uid', 'pages', ['pid' => ROOT_PAGE, 'deleted' => 0]);
if ($subpages > 0) {
    fwrite(STDERR, "Page " . ROOT_PAGE . " already has subpages; restore the snapshot before seeding again.\n");
    exit(1);
}

/**
 * A page record. TCA defaults pages.hidden to 1, so every page states hidden = 0.
 */
function page(string $pid, string $title, array $fields = []): array
{
    return array_merge(['pid' => $pid, 'title' => $title, 'doktype' => DOKTYPE_PAGE, 'hidden' => 0], $fields);
}

// A positive pid is a page, a negative one the record to follow; NEW ids carry no underscore.
$root = (string)ROOT_PAGE;
$pages = [
    // Main navigation, in the order of the design.
    'NEWrevenue' => page($root, 'Revenue & payments', [
        'backend_layout' => 'pagets__SidebarLeft',
        'backend_layout_next_level' => 'pagets__SidebarLeft',
    ]),
    'NEWlandRates' => page('NEWrevenue', 'Land rates'),
    'NEWbusinessPermit' => page('-NEWlandRates', 'Single business permit'),
    'NEWparkingFees' => page('-NEWbusinessPermit', 'Parking fees'),
    'NEWmarketFees' => page('-NEWparkingFees', 'Market fees'),
    'NEWinterestWaiver' => page('-NEWmarketFees', 'Waiver of interest'),
    'NEWhealth' => page('-NEWrevenue', 'Health'),
    'NEWenvironment' => page('-NEWhealth', 'Environment & water'),
    'NEWtrade' => page('-NEWenvironment', 'Trade & tourism'),
    'NEWparticipation' => page('-NEWtrade', 'Public participation'),
    'NEWcontact' => page('-NEWparticipation', 'Contact'),

    // Reached from the header and the footer only.
    'NEWsearch' => page('-NEWcontact', 'Search', ['nav_hide' => 1, 'backend_layout' => 'pagets__SidebarRight']),
    'NEWlogin' => page('-NEWsearch', 'E-services portal', ['nav_hide' => 1, 'backend_layout' => 'pagets__Narrow']),
    'NEWaccessibility' => page('-NEWlogin', 'Accessibility statement', ['nav_hide' => 1]),
    'NEWprivacy' => page('-NEWaccessibility', 'Privacy policy', ['nav_hide' => 1]),
    'NEWaccessToInformation' => page('-NEWprivacy', 'Access to information', ['nav_hide' => 1]),
    'NEWtenders' => page('-NEWaccessToInformation', 'Tenders', ['nav_hide' => 1]),

    // Menu folders: the site settings point at them, their shortcuts are the menu entries.
    'NEWserviceMenu' => page('-NEWtenders', 'Service navigation', ['doktype' => DOKTYPE_FOLDER]),
    'NEWfooterMenu' => page('-NEWserviceMenu', 'Footer navigation', ['doktype' => DOKTYPE_FOLDER]),
];

$shortcuts = [
    'NEWserviceMenu' => ['NEWlogin' => 'E-services portal', 'NEWcontact' => 'Contact'],
    'NEWfooterMenu' => [
        'NEWaccessibility' => 'Accessibility statement',
        'NEWprivacy' => 'Privacy policy',
        'NEWaccessToInformation' => 'Access to information',
        'NEWtenders' => 'Tenders',
    ],
];
foreach ($shortcuts as $folder => $targets) {
    $previous = null;
    foreach ($targets as $target => $title) {
        $id = $folder . 'To' . substr($target, 3);
        $pages[$id] = page($previous === null ? $folder : '-' . $previous, $title, [
            'doktype' => DOKTYPE_SHORTCUT,
            'shortcut_mode' => SHORTCUT_MODE_SELECTED_PAGE,
            'shortcut' => $target,
        ]);
        $previous = $id;
    }
}

$dataHandler = GeneralUtility::makeInstance(DataHandler::class);
$dataHandler->start(
    [
        'pages' => $pages + [
            ROOT_PAGE => ['backend_layout' => 'pagets__Home', 'backend_layout_next_level' => ''],
        ],
    ],
    // The welcome element "typo3 setup" puts on the root page.
    ['tt_content' => array_fill_keys(
        array_column(
            GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content')
                ->select(['uid'], 'tt_content', ['pid' => ROOT_PAGE, 'deleted' => 0])->fetchAllAssociative(),
            'uid'
        ),
        ['delete' => 1]
    )]
);
$dataHandler->process_datamap();
$dataHandler->process_cmdmap();

if ($dataHandler->errorLog !== []) {
    fwrite(STDERR, implode("\n", $dataHandler->errorLog) . "\n");
    exit(1);
}

$uid = static fn(string $newId): int => (int)$dataHandler->substNEWwithIDs[$newId];

// These are page uids: they hold on this installation and on a fresh one only where
// the import hands out the same uids. See packages/mtosafi_sitepackage/README.md.
GeneralUtility::makeInstance(SiteWriter::class)->writeSettings(SITE_IDENTIFIER, [
    'mtosafi' => [
        'navigation' => [
            'serviceMenu' => $uid('NEWserviceMenu'),
            'footerMenu' => $uid('NEWfooterMenu'),
            'searchPage' => $uid('NEWsearch'),
        ],
    ],
]);

foreach ($dataHandler->substNEWwithIDs as $newId => $id) {
    echo str_pad((string)$id, 5, ' ', STR_PAD_LEFT) . '  ' . $newId . "\n";
}
