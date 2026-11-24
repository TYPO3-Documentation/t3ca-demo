<?php

declare(strict_types=1);

/**
 * One land use of the content element "Land rates calculator" (CType
 * mtosafi_landrates): what a plot is used for and the share of its rateable value it
 * pays a year.
 *
 * A land use belongs to exactly one content element: it is created, sorted, translated,
 * hidden and deleted inside that element's form, and lives on the element's page. A
 * translation translates the label only; the rate is the one of the default language.
 */
return [
    'ctrl' => [
        'title' => 'mtosafi_sitepackage.content_elements:landuse',
        'label' => 'label',
        'label_alt' => 'percent',
        'label_alt_force' => true,
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
        // The land uses sit on the content element's page, which is a standard page.
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'typeicon_classes' => [
            'default' => 'mtosafi-content-landrates',
        ],
    ],
    'columns' => [
        'parent' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'label' => [
            'label' => 'mtosafi_sitepackage.content_elements:landuse.label',
            'description' => 'mtosafi_sitepackage.content_elements:landuse.label.description',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 100,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'percent' => [
            'label' => 'mtosafi_sitepackage.content_elements:landuse.percent',
            'description' => 'mtosafi_sitepackage.content_elements:landuse.percent.description',
            // A translation calculates with the rate of the default language.
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'number',
                'format' => 'decimal',
                'size' => 10,
                'range' => [
                    'lower' => 0,
                    'upper' => 100,
                ],
                'required' => true,
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '
                --palette--;;landuse,
                --div--;core.form.tabs:language,
                    --palette--;;language,
                --div--;core.form.tabs:access,
                    hidden,
            ',
        ],
    ],
    'palettes' => [
        'landuse' => [
            'showitem' => 'label, percent',
        ],
        'language' => [
            'showitem' => 'sys_language_uid, l10n_parent',
        ],
    ],
];
