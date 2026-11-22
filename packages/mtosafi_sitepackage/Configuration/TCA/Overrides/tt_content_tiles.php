<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

/**
 * Content element "Service tiles" (CType mtosafi_tiles): a heading and the tiles of
 * design/index.html, section "Popular services". The tiles are records of
 * tx_mtosafisitepackage_tile, owned by the element.
 */

// The group the sitepackage's own elements share in the content type selector.
ExtensionManagementUtility::addTcaSelectItemGroup(
    'tt_content',
    'CType',
    'mtosafi',
    'mtosafi_sitepackage.content_elements:group',
    'before:default',
);

ExtensionManagementUtility::addTcaColumns('tt_content', [
    'tx_mtosafisitepackage_tiles' => [
        'label' => 'mtosafi_sitepackage.content_elements:tiles.field.tiles',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_mtosafisitepackage_tile',
            'foreign_field' => 'parent',
            'minitems' => 1,
            'appearance' => [
                'useSortable' => true,
                'expandSingle' => true,
                'levelLinksPosition' => 'bottom',
                'newRecordLinkTitle' => 'mtosafi_sitepackage.content_elements:tiles.field.tiles.new',
                'showPossibleLocalizationRecords' => true,
            ],
        ],
    ],
]);

ExtensionManagementUtility::addRecordType(
    [
        'label' => 'mtosafi_sitepackage.content_elements:tiles.title',
        'description' => 'mtosafi_sitepackage.content_elements:tiles.description',
        'value' => 'mtosafi_tiles',
        'icon' => 'content-menu-card',
        'group' => 'mtosafi',
    ],
    '
        --palette--;;headers,
        tx_mtosafisitepackage_tiles,
        --div--;core.form.tabs:appearance,
            --palette--;;frames,
            --palette--;;appearanceLinks,
        --div--;core.form.tabs:categories,
            categories,
        --div--;core.form.tabs:extended,
    ',
    [
        // The heading names the section for screen readers too, so it is required.
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
