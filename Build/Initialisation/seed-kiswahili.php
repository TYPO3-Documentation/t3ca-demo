<?php

declare(strict_types=1);

/**
 * Translates the site into Kiswahili, the site language 1 (/sw/): every page and
 * shortcut with its title and URL, the content of the home page, with the slides of
 * the slider and the tiles of "Popular services", and the content of the "Contact"
 * page. The content of the other pages, the news and the events are not translated;
 * the site language falls back to English for them (fallbackType "fallback" in the
 * site configuration). The contact form itself is translated in
 * packages/mtosafi_sitepackage/Resources/Private/Language/sw.form.xlf.
 *
 * The translations are made the way the "Translate" button of the page module makes
 * them, by the DataHandler command "localize", and the Kiswahili text is written into
 * them afterwards.
 *
 * Run it after seed-events.php; the result is exported to
 * packages/mtosafi_sitepackage/Initialisation/ (see the README there).
 *
 *     ddev exec php Build/Initialisation/seed-kiswahili.php
 *
 * It refuses to run where the home page has a Kiswahili translation already.
 */

use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

const LANGUAGE = 1;

/** The field of each table that points from a translation to its record in the default language. */
const PARENT_FIELDS = [
    'pages' => 'l10n_parent',
    'tt_content' => 'l18n_parent',
    'tx_mtosafisitepackage_tile' => 'l10n_parent',
    'tx_mtosafisitepackage_slide' => 'l10n_parent',
    'sys_file_reference' => 'l10n_parent',
];

/**
 * Kiswahili title and URL of each page, by the English title of the page and the
 * title of its parent page. The shortcuts in the folders "Service navigation" and
 * "Footer navigation" carry the titles of the pages they lead to.
 */
const PAGES = [
    ['', 'Home', 'Mwanzo', '/'],
    ['Home', 'Revenue & payments', 'Mapato na malipo', '/mapato-malipo'],
    ['Home', 'Health', 'Afya', '/afya'],
    ['Home', 'Environment & water', 'Mazingira na maji', '/mazingira-maji'],
    ['Home', 'Trade & tourism', 'Biashara na utalii', '/biashara-utalii'],
    ['Home', 'Public participation', 'Ushiriki wa umma', '/ushiriki-wa-umma'],
    ['Home', 'Contact', 'Wasiliana nasi', '/wasiliana-nasi'],
    ['Home', 'Search', 'Tafuta', '/tafuta'],
    ['Home', 'E-services portal', 'Huduma za mtandaoni', '/huduma-za-mtandaoni'],
    ['Home', 'Accessibility statement', 'Taarifa ya ufikivu', '/taarifa-ya-ufikivu'],
    ['Home', 'Privacy policy', 'Sera ya faragha', '/sera-ya-faragha'],
    ['Home', 'Access to information', 'Upatikanaji wa taarifa', '/upatikanaji-wa-taarifa'],
    ['Home', 'Tenders', 'Zabuni', '/zabuni'],
    ['Home', 'Page not found', 'Ukurasa haupatikani', '/ukurasa-haupatikani'],
    ['Home', 'News', 'Habari', '/habari'],
    ['Home', 'Events', 'Matukio', '/matukio'],
    ['Revenue & payments', 'Land rates', 'Kodi ya ardhi', '/mapato-malipo/kodi-ya-ardhi'],
    ['Revenue & payments', 'Single business permit', 'Kibali cha biashara', '/mapato-malipo/kibali-cha-biashara'],
    ['Revenue & payments', 'Parking fees', 'Ada za maegesho', '/mapato-malipo/ada-za-maegesho'],
    ['Revenue & payments', 'Market fees', 'Ada za soko', '/mapato-malipo/ada-za-soko'],
    ['Revenue & payments', 'Waiver of interest', 'Msamaha wa riba', '/mapato-malipo/msamaha-wa-riba'],
    ['News', 'Article', 'Makala', '/habari/makala'],
    ['Events', 'Event', 'Tukio', '/matukio/tukio'],
    ['Service navigation', 'E-services portal', 'Huduma za mtandaoni', '/huduma-za-mtandaoni-1'],
    ['Service navigation', 'Contact', 'Wasiliana nasi', '/wasiliana-nasi-1'],
    ['Footer navigation', 'Accessibility statement', 'Taarifa ya ufikivu', '/taarifa-ya-ufikivu-1'],
    ['Footer navigation', 'Privacy policy', 'Sera ya faragha', '/sera-ya-faragha-1'],
    ['Footer navigation', 'Access to information', 'Upatikanaji wa taarifa', '/upatikanaji-wa-taarifa-1'],
    ['Footer navigation', 'Tenders', 'Zabuni', '/zabuni-1'],
];

/**
 * The Kiswahili fields of each content element, by the page (title of its parent and
 * its own title, as in PAGES) and the English header of the element. Every element
 * of these pages needs an entry.
 */
const CONTENT = [
    '/Home' => [
        'Highlights' => ['header' => 'Mambo muhimu'],
        'Popular services' => ['header' => 'Huduma maarufu'],
        'News' => ['header' => 'Habari'],
        'Events' => ['header' => 'Matukio'],
    ],
    'Home/Contact' => [
        'Write to us' => [
            'header' => 'Tuandikie',
            'subheader' => 'Idara husika itakujibu ndani ya siku tano za kazi.',
        ],
        'Visit us' => [
            'header' => 'Tutembelee',
            'bodytext' => '<p>Manispaa ya Mto Safi<br />Ofisi ya Manispaa<br />Beach Road 1<br />S.L.P. 123, Mto Safi</p>' . "\n"
                . '<p>Simu: <a href="tel:+254700000000">+254 700 000 000</a><br />Barua pepe: <a href="mailto:info@mtosafi.example">info@mtosafi.example</a></p>',
        ],
        'Opening hours' => [
            'header' => 'Saa za kazi',
            'bodytext' => "Siku|Saa\nJumatatu–Alhamisi|8:00–17:00\nIjumaa|8:00–13:00, 14:00–17:00\nJumamosi, Jumapili|Imefungwa",
        ],
        'Report a problem' => [
            'header' => 'Ripoti tatizo',
            'bodytext' => '<p>Shimo barabarani, taa ya barabarani iliyoharibika, mfereji ulioziba au taka ambazo hazijakusanywa: piga simu +254 700 000 002 au andika kwa <a href="mailto:report@mtosafi.example">report@mtosafi.example</a>, na useme ni wapi.</p>',
        ],
    ],
];

/** Alternative text and caption of each image of these elements, by its English alternative text. */
const IMAGES = [
    'The municipal office with a flag on the roof' => [
        'alternative' => 'Ofisi ya manispaa yenye bendera juu ya paa',
        'description' => 'Ofisi ya manispaa kwenye Beach Road.',
    ],
];

/** Label of each tile of "Popular services", by its English label. */
const TILES = [
    'Single business permit' => 'Kibali cha biashara',
    'Land rates' => 'Kodi ya ardhi',
    'Parking fees' => 'Ada za maegesho',
    'Water connection' => 'Kuunganishiwa maji',
    'Market stalls' => 'Vibanda vya soko',
    'Report a problem' => 'Ripoti tatizo',
];

/** Title, text and link text of each slide, by its English title. */
const SLIDES = [
    'Karibu Mto Safi' => [
        'title' => 'Karibu Mto Safi',
        'text' => 'Fukwe nyeupe, mto safi na manispaa inayokuhudumia mtandaoni, mchana na usiku.',
        'link_label' => 'Gundua Mto Safi',
    ],
    'Pay your land rates by mobile money' => [
        'title' => 'Lipa kodi ya ardhi kwa pesa za simu',
        'text' => 'Kokotoa deni lako na ulipe kabla ya tarehe 31 Machi ili kuepuka riba.',
        'link_label' => 'Kokotoa kodi ya ardhi',
    ],
    'Have your say on the budget' => [
        'title' => 'Toa maoni yako kuhusu bajeti',
        'text' => 'Shiriki katika mabaraza ya umma katika kila wadi mwezi huu.',
        'link_label' => 'Tazama tarehe za mabaraza',
    ],
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

// A new page translation is hidden, and the query builder leaves out hidden records
// unless told otherwise.
$translationOf = static function (string $table, int $uid) use ($connectionPool): int {
    $queryBuilder = $connectionPool->getQueryBuilderForTable($table);
    $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
    $translation = $queryBuilder->select('uid')->from($table)->where(
        $queryBuilder->expr()->eq(PARENT_FIELDS[$table], $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)),
        $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(LANGUAGE, Connection::PARAM_INT)),
    )->executeQuery()->fetchOne();
    if ($translation === false) {
        fwrite(STDERR, "No Kiswahili translation of $table:$uid.\n");
        exit(1);
    }
    return (int)$translation;
};

// The pages in the default language, by their title and the title of their parent.
$pages = [];
$rows = $connectionPool->getConnectionForTable('pages')
    ->select(['uid', 'pid', 'title'], 'pages', ['deleted' => 0, 'sys_language_uid' => 0])
    ->fetchAllAssociative();
$titles = array_column($rows, 'title', 'uid');
foreach ($rows as $row) {
    $pages[($titles[$row['pid']] ?? '') . '/' . $row['title']] = (int)$row['uid'];
}

$home = $pages['/Home'] ?? null;
if ($home === null) {
    fwrite(STDERR, "No page \"Home\"; run seed-page-tree.php first.\n");
    exit(1);
}
$homeTranslated = $connectionPool->getConnectionForTable('pages')
    ->count('uid', 'pages', ['l10n_parent' => $home, 'sys_language_uid' => LANGUAGE, 'deleted' => 0]);
if ($homeTranslated > 0) {
    fwrite(STDERR, "The home page has a Kiswahili translation already.\n");
    exit(1);
}

// 1. The pages: a translation each, then their title and URL. A page translation
// starts hidden, so it is made visible.
$commands = [];
foreach (PAGES as [$parent, $title]) {
    $uid = $pages[$parent . '/' . $title] ?? null;
    if ($uid === null) {
        fwrite(STDERR, "No page \"$title\" below \"$parent\".\n");
        exit(1);
    }
    $commands['pages'][$uid]['localize'] = LANGUAGE;
}
$run([], $commands);

$data = [];
foreach (PAGES as [$parent, $title, $swahiliTitle, $slug]) {
    $data['pages'][$translationOf('pages', $pages[$parent . '/' . $title])] = [
        'title' => $swahiliTitle,
        'slug' => $slug,
        'hidden' => 0,
    ];
}
$run($data, []);

// 2. The content of the home page, with the slides and tiles inside it, and of the
// "Contact" page. A translated content element starts hidden too.
$elements = [];
foreach (CONTENT as $page => $texts) {
    if (!isset($pages[$page])) {
        fwrite(STDERR, "No page \"$page\".\n");
        exit(1);
    }
    $rows = $connectionPool->getConnectionForTable('tt_content')
        ->select(['uid', 'header'], 'tt_content', ['pid' => $pages[$page], 'deleted' => 0, 'sys_language_uid' => 0], [], ['sorting' => 'ASC'])
        ->fetchAllAssociative();
    foreach ($rows as $row) {
        if (!isset($texts[$row['header']])) {
            fwrite(STDERR, "No Kiswahili text for the element \"{$row['header']}\" of the page \"$page\".\n");
            exit(1);
        }
        $elements[(int)$row['uid']] = $texts[$row['header']];
    }
}
$commands = [];
foreach (array_keys($elements) as $uid) {
    $commands['tt_content'][$uid]['localize'] = LANGUAGE;
}
$run([], $commands);

$data = [];
foreach ($elements as $uid => $fields) {
    $data['tt_content'][$translationOf('tt_content', $uid)] = $fields + ['hidden' => 0];
}
$references = $connectionPool->getConnectionForTable('sys_file_reference')->select(
    ['uid', 'uid_foreign', 'alternative'],
    'sys_file_reference',
    ['tablenames' => 'tt_content', 'deleted' => 0, 'sys_language_uid' => 0]
)->fetchAllAssociative();
foreach ($references as $reference) {
    if (!isset($elements[(int)$reference['uid_foreign']])) {
        continue;
    }
    if (!isset(IMAGES[$reference['alternative']])) {
        fwrite(STDERR, "No Kiswahili text for the image \"{$reference['alternative']}\".\n");
        exit(1);
    }
    $data['sys_file_reference'][$translationOf('sys_file_reference', (int)$reference['uid'])] = IMAGES[$reference['alternative']];
}
foreach (['tx_mtosafisitepackage_tile' => ['label', TILES], 'tx_mtosafisitepackage_slide' => ['title', SLIDES]] as $table => [$labelField, $texts]) {
    $children = $connectionPool->getConnectionForTable($table)
        ->select(['uid', $labelField], $table, ['pid' => $home, 'deleted' => 0, 'sys_language_uid' => 0])
        ->fetchAllAssociative();
    foreach ($children as $child) {
        $text = $texts[$child[$labelField]] ?? null;
        if ($text === null) {
            fwrite(STDERR, "No Kiswahili text for $table \"{$child[$labelField]}\".\n");
            exit(1);
        }
        $data[$table][$translationOf($table, (int)$child['uid'])] = is_array($text) ? $text : [$labelField => $text];
    }
}
$run($data, []);

foreach (['pages', 'tt_content', 'tx_mtosafisitepackage_tile', 'tx_mtosafisitepackage_slide', 'sys_file_reference'] as $table) {
    $count = $connectionPool->getConnectionForTable($table)
        ->count('uid', $table, ['sys_language_uid' => LANGUAGE, 'deleted' => 0]);
    echo str_pad((string)$count, 4, ' ', STR_PAD_LEFT) . "  $table\n";
}
