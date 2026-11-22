<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Resource\FileType;

/**
 * One slide of the content element "Slider" (CType mtosafi_slider).
 *
 * A slide belongs to exactly one content element: it is created, sorted, translated,
 * hidden and deleted inside that element's form, and lives on the element's page.
 * The columns of the ctrl fields (language, hidden, sorting, workspace) are created
 * by the core.
 */
return [
    'ctrl' => [
        'title' => 'mtosafi_sitepackage.content_elements:slide',
        'label' => 'title',
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
        // The slides sit on the content element's page, which is a standard page.
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'typeicon_classes' => [
            'default' => 'content-carousel-item-calltoaction',
        ],
    ],
    'columns' => [
        'parent' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'image' => [
            'label' => 'mtosafi_sitepackage.content_elements:slide.image',
            'description' => 'mtosafi_sitepackage.content_elements:slide.image.description',
            'config' => [
                'type' => 'file',
                // One image: the record API hands it to the template as a file
                // reference rather than as a collection.
                'relationship' => 'manyToOne',
                'allowed' => 'common-image-types',
                'minitems' => 1,
                'maxitems' => 1,
                // The image is decoration behind the caption: no alternative text, title,
                // link or crop variants of its own.
                'overrideChildTca' => [
                    'types' => [
                        FileType::IMAGE->value => [
                            'showitem' => '--palette--;;filePalette',
                        ],
                    ],
                ],
            ],
        ],
        'title' => [
            'label' => 'mtosafi_sitepackage.content_elements:slide.title',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 100,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'text' => [
            'label' => 'mtosafi_sitepackage.content_elements:slide.text',
            'config' => [
                'type' => 'text',
                'cols' => 50,
                'rows' => 3,
                'max' => 250,
                'eval' => 'trim',
            ],
        ],
        'link' => [
            'label' => 'mtosafi_sitepackage.content_elements:slide.link',
            'config' => [
                'type' => 'link',
                'allowedTypes' => ['page', 'url', 'record'],
                // The button is the link: no target, class or title of its own.
                'appearance' => [
                    'allowedOptions' => [],
                ],
            ],
        ],
        'link_label' => [
            'label' => 'mtosafi_sitepackage.content_elements:slide.link_label',
            'description' => 'mtosafi_sitepackage.content_elements:slide.link_label.description',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 50,
                'eval' => 'trim',
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '
                image,
                --palette--;;caption,
                --palette--;;button,
                --div--;core.form.tabs:language,
                    --palette--;;language,
                --div--;core.form.tabs:access,
                    hidden,
            ',
        ],
    ],
    'palettes' => [
        'caption' => [
            'showitem' => 'title, --linebreak--, text',
        ],
        'button' => [
            'label' => 'mtosafi_sitepackage.content_elements:slide.palette.button',
            'showitem' => 'link_label, link',
        ],
        'language' => [
            'showitem' => 'sys_language_uid, l10n_parent',
        ],
    ],
];
