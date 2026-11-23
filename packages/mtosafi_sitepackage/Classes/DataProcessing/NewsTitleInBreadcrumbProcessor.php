<?php

declare(strict_types=1);

namespace Mtosafi\Sitepackage\DataProcessing;

use GeorgRinger\News\DataProcessing\AddNewsToMenuProcessor;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * Puts the title of the news or event a detail page shows in place of that page in the
 * breadcrumb: Home › Events › Festive market at the jetty rather than Home › Events ›
 * Event. The detail pages ("Article", "Event") have nothing of their own to link to.
 *
 * EXT:news adds the record behind the last item instead; this replaces the last item,
 * which is the detail page itself. Without a news in the request the menu stays as it is.
 *
 *     41 = Mtosafi\Sitepackage\DataProcessing\NewsTitleInBreadcrumbProcessor
 *     41.menus = breadcrumb
 */
#[Autoconfigure(public: true)]
final class NewsTitleInBreadcrumbProcessor extends AddNewsToMenuProcessor
{
    protected function addNewsRecordToMenu(array $newsRecord, array &$menu): void
    {
        array_pop($menu);
        parent::addNewsRecordToMenu($newsRecord, $menu);
    }
}
