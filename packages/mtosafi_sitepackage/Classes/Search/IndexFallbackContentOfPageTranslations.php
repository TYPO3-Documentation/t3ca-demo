<?php

declare(strict_types=1);

namespace Mtosafi\Sitepackage\Search;

use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * Puts a page translation into the ke_search index where it has no content elements of
 * its own and the site language falls back to the default language.
 *
 * The page indexer of ke_search writes an entry for a language only where the page has
 * content elements in that language. A Kiswahili page that shows the English content
 * by fallback would never be found, not even by its Kiswahili title. This hook gives
 * such a translation the content of the default language, which is what the page shows,
 * and ke_search stores it with the title of the translation.
 *
 * Hook "modifyPagesIndexEntry" of EXT:ke_search, registered in ext_localconf.php.
 */
final readonly class IndexFallbackContentOfPageTranslations
{
    public function __construct(
        private SiteFinder $siteFinder,
    ) {}

    /**
     * @param array<int, string> $pageContent content by language uid
     * @param array<int, array<int, array<string, mixed>>> $cachedPageRecords page records by language uid and page uid
     */
    public function modifyPagesIndexEntry(int|string $uid, array &$pageContent, string &$tags, array $cachedPageRecords): void
    {
        $uid = (int)$uid;
        if (!isset($pageContent[0])) {
            return;
        }
        try {
            $site = $this->siteFinder->getSiteByPageId($uid);
        } catch (SiteNotFoundException) {
            return;
        }
        foreach ($site->getLanguages() as $language) {
            $languageId = $language->getLanguageId();
            if ($languageId === 0
                || isset($pageContent[$languageId])
                || !isset($cachedPageRecords[$languageId][$uid])
                || $language->getFallbackType() !== 'fallback'
                || !in_array(0, $language->getFallbackLanguageIds(), true)
            ) {
                continue;
            }
            $pageContent[$languageId] = $pageContent[0];
        }
    }
}
