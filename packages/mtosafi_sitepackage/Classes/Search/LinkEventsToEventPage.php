<?php

declare(strict_types=1);

namespace Mtosafi\Sitepackage\Search;

use Tpwd\KeSearch\Event\ModifyFieldValuesBeforeStoringEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * Links an event in the ke_search index to the event detail page instead of the news
 * detail page, so a visitor who follows the breadcrumb from it lands on the events.
 *
 * The news indexer of ke_search knows one detail page for every news record, events
 * (EXT:eventnews) included. The event detail page is the site setting
 * mtosafi.events.detailPage; where it is 0, events stay linked like news.
 */
#[AsEventListener(identifier: 'mtosafi-sitepackage/search-event-detail-page')]
final readonly class LinkEventsToEventPage
{
    public function __construct(
        private ConnectionPool $connectionPool,
        private SiteFinder $siteFinder,
    ) {}

    public function __invoke(ModifyFieldValuesBeforeStoringEvent $event): void
    {
        $fieldValues = $event->getFieldValues();
        if (($fieldValues['type'] ?? '') !== 'news' || !$this->isEvent((int)($fieldValues['orig_uid'] ?? 0))) {
            return;
        }
        try {
            $site = $this->siteFinder->getSiteByPageId((int)$fieldValues['targetpid']);
        } catch (SiteNotFoundException) {
            return;
        }
        $eventPage = (int)$site->getSettings()->get('mtosafi.events.detailPage', 0);
        if ($eventPage > 0) {
            $fieldValues['targetpid'] = $eventPage;
            $event->setFieldValues($fieldValues);
        }
    }

    private function isEvent(int $newsUid): bool
    {
        return $newsUid > 0 && (bool)$this->connectionPool->getConnectionForTable('tx_news_domain_model_news')
            ->select(['is_event'], 'tx_news_domain_model_news', ['uid' => $newsUid])
            ->fetchOne();
    }
}
