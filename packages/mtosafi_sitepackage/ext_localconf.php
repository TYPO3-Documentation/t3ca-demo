<?php

declare(strict_types=1);

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['mtosafi'] = 'EXT:mtosafi_sitepackage/Configuration/RTE/Mtosafi.yaml';

// The search form in the page header submits "q" without a cHash; with
// FE.cacheHash.enforceValidation the request would otherwise end in a 404.
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'q';

// The result list of ke_search says how many results there are for the search word.
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyResultList'][] =
    \Mtosafi\Sitepackage\Search\AddSearchWordToResultList::class;
