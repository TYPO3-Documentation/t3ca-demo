<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    // Content element "Land rates calculator": the core has no calculator among its icons.
    'mtosafi-content-landrates' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:mtosafi_sitepackage/Resources/Public/Icons/ContentElements/LandRatesCalculator.svg',
    ],
];
