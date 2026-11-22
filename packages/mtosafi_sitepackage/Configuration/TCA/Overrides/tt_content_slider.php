<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

/**
 * Content element "Slider" (CType mtosafi_slider): the carousel of design/index.html.
 * The slides are records of tx_mtosafisitepackage_slide, owned by the element. The
 * group "mtosafi" is added in tt_content_tiles.php.
 */

ExtensionManagementUtility::addTcaColumns('tt_content', [
    'tx_mtosafisitepackage_slides' => [
        'label' => 'mtosafi_sitepackage.content_elements:slider.field.slides',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_mtosafisitepackage_slide',
            'foreign_field' => 'parent',
            'minitems' => 1,
            'appearance' => [
                'useSortable' => true,
                'expandSingle' => true,
                'levelLinksPosition' => 'bottom',
                'newRecordLinkTitle' => 'mtosafi_sitepackage.content_elements:slider.field.slides.new',
                'showPossibleLocalizationRecords' => true,
            ],
        ],
    ],
]);

ExtensionManagementUtility::addRecordType(
    [
        'label' => 'mtosafi_sitepackage.content_elements:slider.title',
        'description' => 'mtosafi_sitepackage.content_elements:slider.description',
        'value' => 'mtosafi_slider',
        'icon' => 'content-carousel',
        'group' => 'mtosafi',
    ],
    '
        header,
        tx_mtosafisitepackage_slides,
        --div--;core.form.tabs:appearance,
            --palette--;;frames,
            --palette--;;appearanceLinks,
        --div--;core.form.tabs:categories,
            categories,
        --div--;core.form.tabs:extended,
    ',
    [
        // The header is the carousel's name for screen readers, never shown on the
        // page, so it is required and has no header type, date or link.
        'columnsOverrides' => [
            'header' => [
                'label' => 'mtosafi_sitepackage.content_elements:slider.field.header',
                'description' => 'mtosafi_sitepackage.content_elements:slider.field.header.description',
                'config' => [
                    'required' => true,
                ],
            ],
        ],
    ],
);
