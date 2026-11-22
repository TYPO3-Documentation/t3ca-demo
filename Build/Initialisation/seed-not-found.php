<?php

declare(strict_types=1);

/**
 * Seeds the "Page not found" page of the Mto Safi site on top of the page tree
 * seed-page-tree.php wrote, and points the 404 error handling of the site at it.
 *
 * Run it after seed-home.php; the result is exported to
 * packages/mtosafi_sitepackage/Initialisation/ (see the README there).
 *
 *     ddev exec php Build/Initialisation/seed-not-found.php
 *
 * It refuses to run where the page exists already.
 */

use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
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
const PAGE_TITLE = 'Page not found';

$classLoader = require __DIR__ . '/../../vendor/autoload.php';
SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init($classLoader);
Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
Bootstrap::initializeBackendAuthentication();
$GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)
    ->createFromUserPreferences($GLOBALS['BE_USER']);

$pages = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');

/**
 * The uid of the page below the root page with this title, which seed-page-tree.php wrote.
 */
function pageBelowRoot(\TYPO3\CMS\Core\Database\Connection $pages, string $title): int|false
{
    $uid = $pages->select(['uid'], 'pages', ['pid' => ROOT_PAGE, 'title' => $title, 'deleted' => 0, 'sys_language_uid' => 0])
        ->fetchOne();
    return $uid === false ? false : (int)$uid;
}

if (pageBelowRoot($pages, PAGE_TITLE) !== false) {
    fwrite(STDERR, "Page \"" . PAGE_TITLE . "\" exists already; restore the snapshot before seeding again.\n");
    exit(1);
}
$tenders = pageBelowRoot($pages, 'Tenders');
$search = pageBelowRoot($pages, 'Search');
if ($tenders === false || $search === false) {
    fwrite(STDERR, "No pages \"Tenders\" and \"Search\" below page " . ROOT_PAGE . "; run seed-page-tree.php first.\n");
    exit(1);
}

$dataHandler = GeneralUtility::makeInstance(DataHandler::class);
$dataHandler->start(
    [
        'pages' => [
            // After the footer pages, before the menu folders; reached by the error handling only.
            'NEWnotFound' => [
                'pid' => -$tenders,
                'title' => PAGE_TITLE,
                'doktype' => DOKTYPE_PAGE,
                // TCA defaults pages.hidden to 1.
                'hidden' => 0,
                'nav_hide' => 1,
                'backend_layout' => 'pagets__Narrow',
                // Neither in search engines nor in the XML sitemap.
                'no_index' => 1,
                'no_follow' => 1,
            ],
        ],
        'tt_content' => [
            'NEWnotFoundText' => [
                'pid' => 'NEWnotFound',
                'CType' => 'text',
                'colPos' => 0,
                'header' => 'This page does not exist',
                // The page template prints the page title as the heading.
                'header_layout' => 100,
                'bodytext' => '<p>The page you are looking for may have moved, or the address may be mistyped.</p>'
                    . '<ul>'
                    . '<li><a href="t3://page?uid=' . ROOT_PAGE . '">Go to the home page</a></li>'
                    . '<li><a href="t3://page?uid=' . $search . '">Search the site</a></li>'
                    . '</ul>',
            ],
        ],
    ],
    []
);
$dataHandler->process_datamap();

if ($dataHandler->errorLog !== []) {
    fwrite(STDERR, implode("\n", $dataHandler->errorLog) . "\n");
    exit(1);
}

$notFound = (int)$dataHandler->substNEWwithIDs['NEWnotFound'];

// A page uid: it holds on this installation and on a fresh one only where the import
// hands out the same uids. See packages/mtosafi_sitepackage/README.md.
$configuration = GeneralUtility::makeInstance(SiteConfiguration::class)->load(SITE_IDENTIFIER);
$configuration['errorHandling'] = [
    [
        'errorCode' => 404,
        'errorHandler' => 'Page',
        'errorContentSource' => 't3://page?uid=' . $notFound,
    ],
];
GeneralUtility::makeInstance(SiteWriter::class)->write(SITE_IDENTIFIER, $configuration);

foreach ($dataHandler->substNEWwithIDs as $newId => $id) {
    echo str_pad((string)$id, 5, ' ', STR_PAD_LEFT) . '  ' . $newId . "\n";
}
