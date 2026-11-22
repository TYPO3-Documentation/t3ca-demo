<?php

declare(strict_types=1);

/**
 * Puts the contact form on the "Contact" page, as design/contact.html has it: the
 * form in the main area, the address, the opening hours and "Report a problem" in
 * the sidebar beside it. The page gets the page layout "Sidebar right", the elements
 * seed-pages.php wrote move into its sidebar, and the "Visit us" image moves above
 * its text, because the sidebar is too narrow for both side by side. The form is
 * packages/mtosafi_sitepackage/Resources/Private/Forms/contact.form.yaml, rendered
 * by a "Form" element above them in the main area.
 *
 * Run it after seed-pages.php; the result is exported to
 * packages/mtosafi_sitepackage/Initialisation/ (see the README there).
 *
 *     ddev exec php Build/Initialisation/seed-contact-form.php
 *
 * It refuses to run where the "Contact" page has a form already.
 */

use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

const FORM = 'EXT:mtosafi_sitepackage/Resources/Private/Forms/contact.form.yaml';
const COLPOS_MAIN = 0;
const COLPOS_SIDEBAR = 1;
const IMAGE_ABOVE_CENTER = 0;

$classLoader = require __DIR__ . '/../../vendor/autoload.php';
SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init($classLoader);
Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
Bootstrap::initializeBackendAuthentication();
$GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)
    ->createFromUserPreferences($GLOBALS['BE_USER']);

$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

$contact = $connectionPool->getConnectionForTable('pages')
    ->select(['uid'], 'pages', ['title' => 'Contact', 'doktype' => 1, 'deleted' => 0, 'sys_language_uid' => 0])
    ->fetchOne();
if ($contact === false) {
    fwrite(STDERR, "No page \"Contact\"; run seed-page-tree.php first.\n");
    exit(1);
}
$contact = (int)$contact;

$elements = $connectionPool->getConnectionForTable('tt_content')
    ->select(['uid', 'CType', 'header'], 'tt_content', ['pid' => $contact, 'deleted' => 0, 'sys_language_uid' => 0], [], ['sorting' => 'ASC'])
    ->fetchAllAssociative();
if (in_array('form_formframework', array_column($elements, 'CType'), true)) {
    fwrite(STDERR, "The page \"Contact\" has a form already.\n");
    exit(1);
}
if ($elements === []) {
    fwrite(STDERR, "The page \"Contact\" has no content; run seed-pages.php first.\n");
    exit(1);
}

$data = [
    'pages' => [
        $contact => ['backend_layout' => 'pagets__SidebarRight'],
    ],
    'tt_content' => [
        'NEWcontactForm' => [
            'pid' => $contact,
            'colPos' => COLPOS_MAIN,
            'CType' => 'form_formframework',
            'header' => 'Write to us',
            'subheader' => 'The right department answers within five working days.',
            'pi_flexform' => [
                'data' => [
                    'sDEF' => [
                        'lDEF' => [
                            'settings.persistenceIdentifier' => ['vDEF' => FORM],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
foreach ($elements as $element) {
    $data['tt_content'][$element['uid']] = ['colPos' => COLPOS_SIDEBAR];
    if ($element['CType'] === 'textpic') {
        $data['tt_content'][$element['uid']]['imageorient'] = IMAGE_ABOVE_CENTER;
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
