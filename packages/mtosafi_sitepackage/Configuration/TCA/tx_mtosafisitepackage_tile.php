<?php

declare(strict_types=1);

/**
 * One tile of the content element "Service tiles" (CType mtosafi_tiles).
 *
 * A tile belongs to exactly one content element: it is created, sorted, translated,
 * hidden and deleted inside that element's form, and lives on the element's page.
 * The columns of the ctrl fields (language, hidden, sorting, workspace) are created
 * by the core.
 */
return [
    'ctrl' => [
        'title' => 'mtosafi_sitepackage.content_elements:tile',
        'label' => 'label',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'translationSource' => 'l10n_source',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        // Edited inside its content element only, never on its own in the record list.
        'hideTable' => true,
        // The tiles sit on the content element's page, which is a standard page.
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'typeicon_classes' => [
            'default' => 'content-menu-card',
        ],
    ],
    'columns' => [
        'parent' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'icon' => [
            'label' => 'mtosafi_sitepackage.content_elements:tile.icon',
            'description' => 'mtosafi_sitepackage.content_elements:tile.icon.description',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'max' => 8,
                'eval' => 'trim',
            ],
        ],
        'label' => [
            'label' => 'mtosafi_sitepackage.content_elements:tile.label',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'max' => 100,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'link' => [
            'label' => 'mtosafi_sitepackage.content_elements:tile.link',
            'description' => 'mtosafi_sitepackage.content_elements:tile.link.description',
            'config' => [
                'type' => 'link',
                'required' => true,
                'allowedTypes' => ['page', 'url', 'record'],
                // The tile is the link: no target, class or title of its own.
                'appearance' => [
                    'allowedOptions' => [],
                ],
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '
                --palette--;;tile,
                --div--;core.form.tabs:language,
                    --palette--;;language,
                --div--;core.form.tabs:access,
                    hidden,
            ',
        ],
    ],
    'palettes' => [
        'tile' => [
            'showitem' => 'icon, label, --linebreak--, link',
        ],
        'language' => [
            'showitem' => 'sys_language_uid, l10n_parent',
        ],
    ],
];
