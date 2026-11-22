<?php

declare(strict_types=1);

namespace Mtosafi\Sitepackage\Search;

use Tpwd\KeSearch\Event\ModifyFieldValuesBeforeStoringEvent;
use Tpwd\KeSearch\Lib\SearchHelper;
use TYPO3\CMS\Core\Attribute\AsEventListener;

/**
 * Tags every entry of the ke_search index with the type of result the design shows:
 * "service" for a page, "news" for a news record. The options of the filter "Show only"
 * carry these tags.
 *
 * An indexer configuration could set the tag itself, but it names the filter option by
 * uid, and the import of the distribution does not keep uids. The tag here is a word.
 */
#[AsEventListener(identifier: 'mtosafi-sitepackage/search-result-type')]
final class TagIndexEntryWithResultType
{
    private const TAGS = [
        'page' => 'service',
        'news' => 'news',
    ];

    public function __invoke(ModifyFieldValuesBeforeStoringEvent $event): void
    {
        $fieldValues = $event->getFieldValues();
        $tag = self::TAGS[$fieldValues['type'] ?? ''] ?? null;
        if ($tag === null) {
            return;
        }
        // "#", or what ke_search_premium configures.
        $tagChar = SearchHelper::getExtConf()['prePostTagChar'];
        $tags = array_filter(explode(',', (string)($fieldValues['tags'] ?? '')));
        $tags[] = $tagChar . $tag . $tagChar;
        $fieldValues['tags'] = implode(',', array_unique($tags));
        $event->setFieldValues($fieldValues);
    }
}
