<?php

declare(strict_types=1);

/**
 * Puts the land rates calculator on the "Land rates" page, after the table "Rates for
 * 2026", as design/land-rates.html has it: a "Land rates calculator" element with the
 * rates of design/docs/land-rates-calculation.md, residential 0.5 %, commercial 1.2 %
 * and agricultural 0.2 % of the rateable value, a minimum of KES 1,500 and interest of
 * 1 % a month. It translates the element into Kiswahili, the heading and the names of
 * the land uses; the rates stay those of the English element.
 *
 * Run it after seed-kiswahili.php; the result is exported to
 * packages/mtosafi_sitepackage/Initialisation/ (see the README there).
 *
 *     ddev exec php Build/Initialisation/seed-land-rates-calculator.php
 *
 * It refuses to run where the "Land rates" page has a calculator already.
 */

use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

const CTYPE = 'mtosafi_landrates';
const AFTER = 'Rates for 2026';
const LANGUAGE = 1;

/** The land uses: English label, Kiswahili label, percent of the rateable value. */
const LAND_USES = [
    ['Residential', 'Makazi', 0.5],
    ['Commercial', 'Biashara', 1.2],
    ['Agricultural', 'Kilimo', 0.2],
];

$classLoader = require __DIR__ . '/../../vendor/autoload.php';
SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init($classLoader);
Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
Bootstrap::initializeBackendAuthentication();
$GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)
    ->createFromUserPreferences($GLOBALS['BE_USER']);

$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

$run = static function (array $data, array $commands): DataHandler {
    $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
    $dataHandler->start($data, $commands);
    $dataHandler->process_cmdmap();
    $dataHandler->process_datamap();
    if ($dataHandler->errorLog !== []) {
        fwrite(STDERR, implode("\n", $dataHandler->errorLog) . "\n");
        exit(1);
    }
    return $dataHandler;
};

$page = $connectionPool->getConnectionForTable('pages')
    ->select(['uid'], 'pages', ['title' => 'Land rates', 'doktype' => 1, 'deleted' => 0, 'sys_language_uid' => 0])
    ->fetchOne();
if ($page === false) {
    fwrite(STDERR, "No page \"Land rates\"; run seed-page-tree.php first.\n");
    exit(1);
}
$page = (int)$page;

$content = $connectionPool->getConnectionForTable('tt_content');
if ($content->count('uid', 'tt_content', ['pid' => $page, 'CType' => CTYPE, 'deleted' => 0]) > 0) {
    fwrite(STDERR, "The page \"Land rates\" has a calculator already.\n");
    exit(1);
}
$after = $content->select(['uid'], 'tt_content', ['pid' => $page, 'header' => AFTER, 'deleted' => 0, 'sys_language_uid' => 0])->fetchOne();
if ($after === false) {
    fwrite(STDERR, "The page \"Land rates\" has no element \"" . AFTER . "\"; run seed-pages.php first.\n");
    exit(1);
}

// 1. The element, after the table of the rates. A negative pid puts it after that element.
$data = [
    'tt_content' => [
        'NEWcalculator' => [
            'pid' => '-' . $after,
            'colPos' => 0,
            'CType' => CTYPE,
            'header' => 'Land rates calculator',
            'tx_mtosafisitepackage_minimum_rate' => 1500,
            'tx_mtosafisitepackage_interest' => 1,
            'tx_mtosafisitepackage_landuses' => '',
        ],
    ],
    'tx_mtosafisitepackage_landuse' => [],
];
$landUseIds = [];
foreach (LAND_USES as $number => [$label, , $percent]) {
    $landUseIds[] = $id = 'NEWlandUse' . $number;
    $data['tx_mtosafisitepackage_landuse'][$id] = ['pid' => $page, 'label' => $label, 'percent' => $percent];
}
$data['tt_content']['NEWcalculator']['tx_mtosafisitepackage_landuses'] = implode(',', $landUseIds);
$dataHandler = $run($data, []);
$element = (int)$dataHandler->substNEWwithIDs['NEWcalculator'];

// 2. Its Kiswahili translation, the way the "Translate" button of the page module makes
// it, with the land uses inside it. A translated element starts hidden.
$run([], ['tt_content' => [$element => ['localize' => LANGUAGE]]]);

$translationOf = static function (string $table, string $parentField, int $uid) use ($connectionPool): int {
    $queryBuilder = $connectionPool->getQueryBuilderForTable($table);
    $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
    $translation = $queryBuilder->select('uid')->from($table)->where(
        $queryBuilder->expr()->eq($parentField, $queryBuilder->createNamedParameter($uid)),
        $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(LANGUAGE)),
    )->executeQuery()->fetchOne();
    if ($translation === false) {
        fwrite(STDERR, "No Kiswahili translation of $table:$uid.\n");
        exit(1);
    }
    return (int)$translation;
};

$data = [
    'tt_content' => [
        $translationOf('tt_content', 'l18n_parent', $element) => [
            'header' => 'Kikokotoo cha kodi ya ardhi',
            'hidden' => 0,
        ],
    ],
];
foreach (LAND_USES as $number => [, $swahiliLabel]) {
    $uid = (int)$dataHandler->substNEWwithIDs['NEWlandUse' . $number];
    $data['tx_mtosafisitepackage_landuse'][$translationOf('tx_mtosafisitepackage_landuse', 'l10n_parent', $uid)] = [
        'label' => $swahiliLabel,
    ];
}
$run($data, []);

echo str_pad((string)$element, 5, ' ', STR_PAD_LEFT) . "  tt_content, Land rates calculator\n";
