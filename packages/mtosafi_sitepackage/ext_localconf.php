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

// A Kiswahili page that shows the English content by fallback goes into the search index
// with that content and its Kiswahili title.
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyPagesIndexEntry'][] =
    \Mtosafi\Sitepackage\Search\IndexFallbackContentOfPageTranslations::class;

// The registration fields of an event (Configuration/TCA/Overrides/tx_news_domain_model_news.php)
// reach the news model through the class extension of EXT:news, see Classes/Domain/Model/News.php.
$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Model/News'][] = 'mtosafi_sitepackage';

// Kiswahili is not among the languages the core knows. Without this, the site language
// "Kiswahili" (locale sw_KE) would read its labels in English and not from the sw.*.xlf
// files of the package.
$GLOBALS['TYPO3_CONF_VARS']['SYS']['localization']['locales']['user']['sw'] = 'Swahili';
