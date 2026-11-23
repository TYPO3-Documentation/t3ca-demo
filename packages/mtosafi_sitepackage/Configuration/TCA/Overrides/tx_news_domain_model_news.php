<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

// Registration of an event (EXT:eventnews): a link and the text of the button that
// shows it on the detail page. Both only show where the news is an event, beside the
// place on the tab "Eventnews".
ExtensionManagementUtility::addTCAcolumns('tx_news_domain_model_news', [
    'tx_mtosafisitepackage_registration_link' => [
        'exclude' => true,
        'displayCond' => 'FIELD:is_event:>:0',
        'label' => 'mtosafi_sitepackage.news:registration_link',
        'description' => 'mtosafi_sitepackage.news:registration_link.description',
        'config' => [
            'type' => 'link',
            'allowedTypes' => ['page', 'url', 'email'],
            // The button is the link: no target, class or title of its own.
            'appearance' => [
                'allowedOptions' => [],
            ],
        ],
    ],
    'tx_mtosafisitepackage_registration_label' => [
        'exclude' => true,
        'displayCond' => 'FIELD:is_event:>:0',
        'label' => 'mtosafi_sitepackage.news:registration_label',
        'description' => 'mtosafi_sitepackage.news:registration_label.description',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'max' => 60,
            'eval' => 'trim',
            'placeholder' => 'mtosafi_sitepackage.news:registration_label.default',
        ],
    ],
]);

ExtensionManagementUtility::addFieldsToPalette(
    'tx_news_domain_model_news',
    'palette_eventfields',
    '--linebreak--,tx_mtosafisitepackage_registration_link,tx_mtosafisitepackage_registration_label'
);
