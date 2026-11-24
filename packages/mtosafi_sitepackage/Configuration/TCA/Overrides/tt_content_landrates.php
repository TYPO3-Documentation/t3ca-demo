<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

/**
 * Content element "Land rates calculator" (CType mtosafi_landrates): the calculator of
 * design/land-rates.html. It applies the rule of design/docs/land-rates-calculation.md
 * with the rates the editor enters here: a share of the rateable value for each land
 * use, records of tx_mtosafisitepackage_landuse owned by the element, at least the
 * minimum rate, plus interest for each month paid late.
 *
 * The rates are numbers of the default language: a translation translates the
 * heading and the names of the land uses, and calculates with the same rates.
 */
ExtensionManagementUtility::addTcaColumns('tt_content', [
    'tx_mtosafisitepackage_landuses' => [
        'label' => 'mtosafi_sitepackage.content_elements:landrates.field.landuses',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_mtosafisitepackage_landuse',
            'foreign_field' => 'parent',
            'minitems' => 1,
            'appearance' => [
                'useSortable' => true,
                'expandSingle' => true,
                'levelLinksPosition' => 'bottom',
                'newRecordLinkTitle' => 'mtosafi_sitepackage.content_elements:landrates.field.landuses.new',
                'showPossibleLocalizationRecords' => true,
            ],
        ],
    ],
    'tx_mtosafisitepackage_minimum_rate' => [
        'label' => 'mtosafi_sitepackage.content_elements:landrates.field.minimum',
        'description' => 'mtosafi_sitepackage.content_elements:landrates.field.minimum.description',
        'l10n_mode' => 'exclude',
        'config' => [
            'type' => 'number',
            'size' => 10,
            'range' => [
                'lower' => 0,
            ],
        ],
    ],
    'tx_mtosafisitepackage_interest' => [
        'label' => 'mtosafi_sitepackage.content_elements:landrates.field.interest',
        'description' => 'mtosafi_sitepackage.content_elements:landrates.field.interest.description',
        'l10n_mode' => 'exclude',
        'config' => [
            'type' => 'number',
            'format' => 'decimal',
            'size' => 10,
            'range' => [
                'lower' => 0,
                'upper' => 100,
            ],
        ],
    ],
]);

ExtensionManagementUtility::addRecordType(
    [
        'label' => 'mtosafi_sitepackage.content_elements:landrates.title',
        'description' => 'mtosafi_sitepackage.content_elements:landrates.description',
        'value' => 'mtosafi_landrates',
        'icon' => 'mtosafi-content-landrates',
        'group' => 'mtosafi',
    ],
    '
        --palette--;;headers,
        tx_mtosafisitepackage_landuses,
        --palette--;mtosafi_sitepackage.content_elements:landrates.palette.rates;mtosafiLandRates,
        --div--;core.form.tabs:appearance,
            --palette--;;frames,
            --palette--;;appearanceLinks,
        --div--;core.form.tabs:categories,
            categories,
        --div--;core.form.tabs:extended,
    ',
    [
        // The heading names the calculator for screen readers too, so it is required.
        // Header type "Hidden" keeps it for them and hides it on the page.
        'columnsOverrides' => [
            'header' => [
                'config' => [
                    'required' => true,
                ],
            ],
        ],
    ],
);

$GLOBALS['TCA']['tt_content']['palettes']['mtosafiLandRates'] = [
    'showitem' => 'tx_mtosafisitepackage_minimum_rate, tx_mtosafisitepackage_interest',
];
