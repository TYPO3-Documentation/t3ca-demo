<?php

declare(strict_types=1);

namespace Mtosafi\Sitepackage\Initialization;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Package\Event\PackageInitializationEvent;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Impexp\Initialization\ImportSiteConfigurationsOnPackageInitialization;

/**
 * Creates the backend user group "Editors" when the package is set up, after the
 * page tree and the site configuration were imported.
 *
 * A backend user group and a file mount are records on the root level, which the
 * page tree export in Initialisation/data.xml does not carry. The group names the
 * site's root page and the file mount by uid, so it is built here against the
 * installation that runs the setup. An installation that has a group of that
 * title already keeps it untouched.
 */
#[AsEventListener(after: ImportSiteConfigurationsOnPackageInitialization::class)]
final readonly class CreateEditorGroupOnPackageInitialization
{
    private const EXTENSION_KEY = 'mtosafi_sitepackage';
    private const SITE_IDENTIFIER = 'main';
    private const GROUP_TITLE = 'Editors';
    private const UPLOAD_FOLDER = '/user_upload/news/';

    /**
     * The folders editors work in, by the title of their file mount. Uploads go to
     * UPLOAD_FOLDER unless the editor picks another.
     */
    private const FILE_MOUNTS = [
        'News images' => self::UPLOAD_FOLDER,
        'Slider images' => '/user_upload/slides/',
    ];

    /**
     * The tables editors read and write. Every field of these tables that needs
     * access to be granted is granted.
     */
    private const TABLES = [
        'pages',
        'tt_content',
        'sys_category',
        'sys_file',
        'sys_file_metadata',
        'sys_file_reference',
        'tx_news_domain_model_news',
        'tx_news_domain_model_link',
        'tx_news_domain_model_tag',
        'tx_mtosafisitepackage_tile',
        'tx_mtosafisitepackage_slide',
    ];

    /**
     * Content types editors may not choose: raw HTML bypasses the design and its
     * accessibility, a shortcut repeats content elsewhere.
     */
    private const DENIED_CONTENT_TYPES = ['html', 'shortcut'];

    private const MODULES = ['web_layout', 'records', 'media_management'];

    private const FILE_PERMISSIONS = [
        'readFolder', 'addFolder', 'renameFolder',
        'readFile', 'writeFile', 'addFile', 'renameFile', 'replaceFile', 'moveFile', 'copyFile', 'deleteFile',
    ];

    private const PAGE_PERMISSIONS_ALL = 31;

    public function __construct(
        private ConnectionPool $connectionPool,
        private SiteFinder $siteFinder,
        private StorageRepository $storageRepository,
        private TcaSchemaFactory $tcaSchemaFactory,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(PackageInitializationEvent $event): void
    {
        if ($event->getExtensionKey() !== self::EXTENSION_KEY) {
            return;
        }
        $groups = $this->connectionPool->getConnectionForTable('be_groups');
        if ($groups->count('uid', 'be_groups', ['title' => self::GROUP_TITLE, 'deleted' => 0]) > 0) {
            return;
        }
        $rootPageId = $this->findRootPageId();
        if ($rootPageId === null) {
            $this->logger->warning('No site "' . self::SITE_IDENTIFIER . '": the backend user group "' . self::GROUP_TITLE . '" was not created.');
            return;
        }

        $existingGroupIds = array_map('intval', $groups->select(['uid'], 'be_groups', ['deleted' => 0])->fetchFirstColumn());
        $now = time();
        $fileMountIds = $this->createFileMounts($now);
        $groups->insert('be_groups', [
            'pid' => 0,
            'tstamp' => $now,
            'crdate' => $now,
            'title' => self::GROUP_TITLE,
            'description' => 'Edit pages, content and news of the Mto Safi site. Images upload to fileadmin' . self::UPLOAD_FOLDER,
            'db_mountpoints' => (string)$rootPageId,
            'file_mountpoints' => implode(',', $fileMountIds),
            'file_permissions' => implode(',', self::FILE_PERMISSIONS),
            'groupMods' => implode(',', self::MODULES),
            'tables_select' => implode(',', self::TABLES),
            'tables_modify' => implode(',', self::TABLES),
            'pagetypes_select' => '1,4,254',
            'non_exclude_fields' => implode(',', $this->excludeFields()),
            'explicit_allowdeny' => implode(',', $this->allowedContentTypes()),
            'allowed_languages' => '0',
            'TSconfig' => 'options.defaultUploadFolder = 1:' . self::UPLOAD_FOLDER,
        ]);
        $groupId = (int)$groups->lastInsertId();
        $this->grantPages($rootPageId, $groupId, $existingGroupIds);
        $this->grantFuturePages($rootPageId, $groupId);
    }

    private function findRootPageId(): ?int
    {
        foreach ($this->siteFinder->getAllSites(false) as $site) {
            if ($site->getIdentifier() === self::SITE_IDENTIFIER) {
                return $site->getRootPageId();
            }
        }
        return null;
    }

    /**
     * @return list<int> the uids of the file mounts of FILE_MOUNTS
     */
    private function createFileMounts(int $now): array
    {
        $storage = $this->storageRepository->getDefaultStorage();
        if ($storage === null) {
            $this->logger->warning('No default file storage: the group "' . self::GROUP_TITLE . '" gets no file mount.');
            return [];
        }
        $connection = $this->connectionPool->getConnectionForTable('sys_filemounts');
        $uids = [];
        foreach (self::FILE_MOUNTS as $title => $folder) {
            if (!$storage->hasFolder($folder)) {
                $storage->createFolder($folder);
            }
            $connection->insert('sys_filemounts', [
                'pid' => 0,
                'tstamp' => $now,
                'title' => $title,
                'identifier' => $storage->getUid() . ':' . $folder,
            ]);
            $uids[] = (int)$connection->lastInsertId();
        }
        return $uids;
    }

    /**
     * @return list<string> table:field for every field of the editors' tables that is excluded by default
     */
    private function excludeFields(): array
    {
        $fields = [];
        foreach (self::TABLES as $table) {
            if (!$this->tcaSchemaFactory->has($table)) {
                continue;
            }
            foreach ($this->tcaSchemaFactory->get($table)->getFields() as $field) {
                if ($field->supportsAccessControl()) {
                    $fields[] = $table . ':' . $field->getName();
                }
            }
        }
        return $fields;
    }

    /**
     * @return list<string> tt_content:CType:<type> for every content type but the denied ones
     */
    private function allowedContentTypes(): array
    {
        $items = $this->tcaSchemaFactory->get('tt_content')->getField('CType')->getConfiguration()['items'] ?? [];
        $allowed = [];
        foreach ($items as $item) {
            $value = (string)($item['value'] ?? '');
            if ($value === '' || $value === '--div--' || in_array($value, self::DENIED_CONTENT_TYPES, true)) {
                continue;
            }
            $allowed[] = 'tt_content:CType:' . $value;
        }
        return $allowed;
    }

    /**
     * Hands the site's pages to the editors' group, with every permission, where they
     * belong to no group of this installation: no group at all, or the group uid the
     * export carried from the installation it was made on.
     *
     * @param list<int> $existingGroupIds
     */
    private function grantPages(int $rootPageId, int $groupId, array $existingGroupIds): void
    {
        $connection = $this->connectionPool->getConnectionForTable('pages');
        $pageIds = [];
        $level = [$rootPageId];
        while ($level !== []) {
            $pageIds = array_merge($pageIds, $level);
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
            $queryBuilder->getRestrictions()->removeAll();
            $level = array_map('intval', $queryBuilder
                ->select('uid')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->in('pid', $queryBuilder->createNamedParameter($level, Connection::PARAM_INT_ARRAY)),
                    $queryBuilder->expr()->eq('deleted', 0),
                    $queryBuilder->expr()->eq('sys_language_uid', 0),
                )
                ->executeQuery()
                ->fetchFirstColumn());
        }
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->update('pages')
            ->set('perms_groupid', $groupId)
            ->set('perms_group', self::PAGE_PERMISSIONS_ALL)
            ->where($queryBuilder->expr()->in('uid', $queryBuilder->createNamedParameter($pageIds, Connection::PARAM_INT_ARRAY)));
        if ($existingGroupIds !== []) {
            $queryBuilder->andWhere($queryBuilder->expr()->notIn(
                'perms_groupid',
                $queryBuilder->createNamedParameter($existingGroupIds, Connection::PARAM_INT_ARRAY)
            ));
        }
        $queryBuilder->executeStatement();
    }

    /**
     * Pages created later in the site, by an administrator as well, belong to the
     * editors' group: page TSconfig of the root page, inherited by the whole tree.
     * A line the root page carried before, such as the exported group uid of another
     * installation, is replaced.
     */
    private function grantFuturePages(int $rootPageId, int $groupId): void
    {
        $connection = $this->connectionPool->getConnectionForTable('pages');
        $tsConfig = (string)$connection->select(['TSconfig'], 'pages', ['uid' => $rootPageId])->fetchOne();
        $tsConfig = trim((string)preg_replace('/^\s*TCEMAIN\.permissions\.(groupid|group)\s*=.*$\n?/m', '', $tsConfig));
        $tsConfig .= ($tsConfig === '' ? '' : "\n")
            . 'TCEMAIN.permissions.groupid = ' . $groupId . "\n"
            . 'TCEMAIN.permissions.group = show,editcontent,edit,new,delete';
        $connection->update('pages', ['TSconfig' => $tsConfig], ['uid' => $rootPageId]);
    }
}
